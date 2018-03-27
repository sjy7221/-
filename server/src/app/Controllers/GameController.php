<?php

namespace app\Controllers;

use app\Models\AppModel;
use Server\CoreBase\Controller;

/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午3:51
 */
class GameController extends Controller
{
    /**
     * @var AppModel
     */
    public $CommModel;
    public $data; // 传过来数据
    public $mid; //传过来的id
    public $room_id;//传过来的房间号
    public $roomInfo;//房间信息
    public $userInfo;//用户信息
    public $gameInfo;//游戏信息
    public $uids;
    // public $weizhi;  //当前位置是谁打牌
    // public $now; //现在改谁打牌
    protected function initialization($controller_name,$method_name)
    {
        parent::initialization($controller_name, $method_name);
        $this->CommModel = $this->loader->model('CommModel', $this);

        $this->data = $this->client_data->data;
        $this->mid = $this->data->mid;
        $this->room_id = $this->data->room_id;
        $res =  yield $this->CommModel->exit($this->data);//判断传过来的类型;

        if($res){
            $this->send('nonono,数据错误',false);
            $this->close();
            return;
        }
        $room = yield $this->redis_pool->getCoroutine()->hgetall($this->room_id);
        if (isset($room['roomInfo']) && $room['roomInfo']){
            $this->roomInfo = unserialize($room['roomInfo']);
            if(isset($room['userInfo']) && $room['userInfo']){
                $this->userInfo = unserialize($room['userInfo']);
            }
            if(isset($room['gameInfo']) && $room['gameInfo']){
                $this->gameInfo = unserialize($room['gameInfo']);
            }

        } else {
            $this->send('找不到房间信息', false);
            $this->close();
            return false;
        }
        //获取房间所有人
        $this->uids = yield $this->redis_pool->getCoroutine()->hkeys('uids_' . $this->room_id);
        if(!$this->uid){

            $this->bindUid($this->mid);
        }
    }

    /**
     * 打出流程.
     * User: shijunyi
     * Date: 3/22
     *
     */
    public function dachu()
    {

        echo  "【dachu】".json_encode($this->data). "\n";
        if ($this->is_destroy) {
            return;
        }

        $pai = $this->data->pai; //打出的牌

        $room_id = $this->room_id;
        $roomInfo = $this->roomInfo;

        $gameInfo = $this->gameInfo;
        $shoupai = $gameInfo['users'][$this->mid]['shoupai'];
        $leix = panduan($pai,$shoupai);//判断打出牌是否在手牌中

        //如果返回的类型
        if($leix){
            if($roomInfo['guize']['suanfa'][0] && $roomInfo['nowjushu'] == 1  && $pai[0] != 31 && count($shoupai) == 48/$roomInfo['guize']['renshu']){

                    $this->send(reData('error', ['msg'=>'首句先出黑桃3']),false);
                    return ;

            }
            D('leix',$leix);
            $dc = zhuanhuan($pai); //去掉打出花色
            $sjp = zhuanhuan($gameInfo['dachu']['pai']);//去上家打出花色
            //判断打出的牌大小
            if(isset($gameInfo['dachu']) || $gameInfo['dachu']){
                if($gameInfo['dachu']['mid'] != $gameInfo['now'] && $sjp[0] > $dc[0] && $leix['type'] == $gameInfo['dachu']['leix']['type']){
                    $this->send(reData('error', ['msg'=>'牌型不对']),false);
                    return;
                }

            }
            $gameInfo['dachu']['mid'] = $this->mid;//打出牌人的id
            $gameInfo['dachu']['pai'] = $pai;//打出的牌
            $gameInfo['dachu']['leix'] = $leix;//打出的类型
            $gameInfo['users'][$this->mid]['zhadan'] = 0;
            $weizhi =  array_search($this->mid,$roomInfo['weizhi']);//当前位置;
            if($leix['type'] == 10){ //炸弹数
                $gameInfo['users'][$this->mid]['zhadan'] +=1;
            }

            //从手牌中去除打出的牌
            $req =  array_diff($shoupai,$pai);
            if(!empty($req)){//如果没打完
                sort($req);
                $gameInfo['users'][$this->mid]['shoupai'] = $req;//把剩余的手牌存起来
                if($roomInfo['guize']['renshu'] == 3){

                    $gameInfo =  yield $this->sanren($gameInfo,$weizhi,$roomInfo,$pai,$leix,$room_id); //三个人的玩法
                }elseif($roomInfo['guize']['renshu'] == 2){        //如果是两人房
                    $gameInfo =    yield $this->erren($gameInfo,$weizhi,$roomInfo,$pai,$leix,$room_id); //2个人的玩法
                }


                yield $this->redis_pool->hset($room_id, 'gameInfo',serialize($gameInfo)); //存数据
            }else{
                //如果打完
                yield  $this->jieshu($this->mid,$gameInfo);
            }
        }else{
            $this->send(reData('error', ['msg'=>'牌型不对']),false);
        }


        $this->destroy();
    }
    /**
     * 一局结束后 继续开始新游戏
     */
    public  function  jixu(){
        if ($this->is_destroy) {
            return;
        }
        if($this->roomInfo['status'] == 1){
            $this->destroy();
            return;
        }
        yield  $this->redis_pool->getCoroutine()->sAdd("jx_".$this->room_id,$this->mid);
        $num =   yield  $this->redis_pool->getCoroutine()->sSize("jx_".$this->room_id);
        $this->sendToUids($this->uids,reData('jixu',['mid'=>$this->mid]),false);
        if($num == $this->roomInfo['guize']['renshu']){
            yield  $this->redis_pool->getCoroutine()->delete("jx_".$this->room_id);
            yield  $this->fapai();
        }
        $this->destroy();
    }

    /**
     * 申请解散房间
     */
    public function jiesan(){
        if ($this->is_destroy) {
            return;
        }
        $status = $this->data->status;  //申请解散的给2  其余 0 拒绝 1同意
        $this->sendToUids($this->uids,reData('jiesan',['mid'=>$this->mid,'status'=>$status]),false);

        //如果是2个人玩 那么直接就可以解散
        if($this->roomInfo['guize']['renshu'] == 2){
            yield $this->q_jiesan();
            $this->destroy();
            return;
        }

        //先判断解散存不存在
        if(yield  $this->redis_pool->getCoroutine()->exists("js_".$this->room_id)){
            if($status == 1){
                $this->sendToUids($this->uids,reData('jiesan_result',['status'=>1,'result'=>[]]),false);
                yield  $this->redis_pool->getCoroutine()->delete($this->room_id);
                yield  $this->redis_pool->getCoroutine()->delete('uids_'.$this->room_id);
                yield  $this->redis_pool->getCoroutine()->delete('jx_'.$this->room_id);
                yield  $this->redis_pool->getCoroutine()->delete('js_'.$this->room_id);
                yield  $this->redis_pool->getCoroutine()->delete('logs_'.$this->room_id);
                yield  $this->mysql_pool->dbQueryBuilder
                    ->update('gs_member')
                    ->set('room_id',0)
                    ->where('room_id',$this->room_id)
                    ->coroutineSend();
                if($this->roomInfo['nowjushu'] == 1){
                    yield $this->mysql_pool->dbQueryBuilder
                        ->delete()
                        ->from('gs_rooms')
                        ->where('room_id',$this->room_id)
                        ->coroutineSend();
                }else{
                    yield $this->mysql_pool->dbQueryBuilder
                        ->update('gs_rooms')
                        ->set('status',3)
                        ->where('room_id',$this->room_id)
                        ->where('status',2) // type
                        ->coroutineSend();
                }

            }else{
                yield  $this->redis_pool->getCoroutine()->hset("js_".$this->room_id,$this->mid,$status);
            }
            $re = yield  $this->redis_pool->getCoroutine()->hGetAll("js_".$this->room_id);
            //如果当前投票人数 == 房间人数 那么就返回失败结果
            if(count($re) == $this->roomInfo['guize']['renshu']){
                $this->sendToUids($this->uids,reData('jiesan_result',['status'=>0,'result'=>$re]),false);
                yield  $this->redis_pool->getCoroutine()->del('js_'.$this->room_id);
            }
        }else{
            yield  $this->redis_pool->getCoroutine()->hset("js_".$this->room_id,$this->mid,$status);
        }
        $this->destroy();
    }
    /**
     * 强制解散
     */
    public function q_jiesan(){
        if ($this->is_destroy) {
            return;
        }
        yield  $this->redis_pool->getCoroutine()->delete($this->room_id);  //房间所有数据
        yield  $this->redis_pool->getCoroutine()->delete('uids_'.$this->room_id);  //玩家id
        yield  $this->redis_pool->getCoroutine()->delete('jx_'.$this->room_id);    //继续
        yield  $this->redis_pool->getCoroutine()->delete('js_'.$this->room_id);    //解散
        yield  $this->redis_pool->getCoroutine()->delete('logs_'.$this->room_id);   //游戏记录
        yield  $this->mysql_pool->dbQueryBuilder
            ->update('gs_member')
            ->set('room_id',0)
            ->where('room_id',$this->room_id)
            ->coroutineSend();
        if($this->roomInfo['nowjushu'] == 1){
            yield $this->mysql_pool->dbQueryBuilder
                ->delete()
                ->from('gs_rooms')
                ->where('room_id',$this->room_id)
                ->coroutineSend();
        }else{
            yield $this->mysql_pool->dbQueryBuilder
                ->update('gs_rooms')
                ->set('status',3) // type  如果为三说明房间解散状态
                ->where('room_id',$this->room_id)
                ->where('status',2) // type
                ->coroutineSend();
        }
        $this->sendToUids($this->uids,reData('jiesan_result',['status'=>1,'result'=>[]]),false);
        return;
    }
    /**
     * 断线重连.
     * User: shijunyi
     * Date: 3/22
     *
     */
    public function getGame(){
        if ($this->is_destroy) {
            return;
        }
        //房间内准备状态  并且游戏数据有
        if($this->roomInfo['status'] == 0 || empty($this->gameInfo)){
            $this->send(reData('out', '游戏未开始无法获取'), false);
            $this->close();
            return;
        }
        $roomInfo =  $this->roomInfo;
        $weizhi =  $roomInfo['weizhi'];
        $gameInfo =  $this->gameInfo;
        //如果当前正在发起解散
        if( yield  $this->redis_pool->getCoroutine()->exists("js_".$this->room_id)){
            $re = yield  $this->redis_pool->getCoroutine()->hGetAll("js_".$this->room_id);
            //先发发起结算的人
            foreach ($re as $kk=>$vv){
                if($vv  == 2){
                    $this->sendToUids($this->uids,reData('jiesan',['mid'=>$kk,'status'=>$vv]),false);
                }
            }
            //再发选择状态的人
            foreach ($re as $kk2=>$vv2){
                if($vv != 2){
                    $this->sendToUids($this->uids,reData('jiesan',['mid'=>$kk2,'status'=>$vv2]),false);
                }
            }
            return;
        }
        //如果当前正在发继续
        if( yield  $this->redis_pool->getCoroutine()->exists("jx_".$this->room_id)){
            yield $this->jixu();
            return;
        }
        $upais = [];
        foreach ($weizhi as $v){
            if($v == $this->mid){
                $upais[$v]['s'] = spias($gameInfo['users'][$v]['shoupai']);
            }else{
                $upais[$v]['s'] = [];
            }

        }
        $data = [
            'ju'=>$roomInfo['nowjushu'],                   //当前局数
            'pais'=>$upais,                          //手牌
            'dachu'=>$gameInfo['dachu'],           //打出的所有信息
            'now'=>$gameInfo['now']                    //当前操作玩家
        ];
        $this->send(reData('getGame',$data));
    }

    /**
     * 发牌流程.
     * User: shijunyi
     * Date: 3/22
     *
     */
    private function fapai($gameInfo,$roomInfo,$userInfo)
    {
        D('【fapai】',$this->data);
        echo  "【fapai】".json_encode($this->data). "\n";
        if ($this->is_destroy) {
            return;
        }

        $re = fapai($gameInfo,$roomInfo,$userInfo);
        $roomInfo = $re['roomInfo'];
        $gameInfo = $re['gameInfo'];
        $roomid = $roomInfo['guize']['room_id'];
        yield $this->redis_pool->hset($roomid, 'roomInfo', serialize($roomInfo),'gameInfo',serialize($gameInfo));

        foreach($gameInfo['users'] as $us => $u){

            $data = [
                'roomInfo'=>$roomInfo,//
                'userInfo'=>$userInfo,//
                'now'=>$gameInfo['now'],
                'pai'=>$u['shoupai']

            ];

            $this->sendToUid($us,reData('fapai',$data),false);
        }
//        $data = [
//            'roomInfo'=>$roomInfo,//
//            'userInfo'=>$userInfo,//
//            'now'=>$gameInfo['now'],
//            'gameInfo'=>$gameInfo
//
//        ];
        // yield $this->saveLogs(reData('fapai',$data)); 存游戏记录
        $this->destroy();
    }
    /**
     * 三人玩.
     * User: shijunyi
     * Date: 3/22
     *
     */
    private function sanren($gameInfo,$weizhi,$roomInfo,$pai,$leix,$room_id)
    {
        for($i=1;$i<count($gameInfo['users']);$i++){

            if($weizhi+$i == 3) {
                $next = 0;//下一个人

            }elseif($weizhi+$i == 4){
                $next =1;//下下个人
            }else{
                $next = $weizhi+$i;
            }

            $now = $roomInfo['weizhi'][$next];//取出下一个人的mid

            $nextsp  =  $gameInfo['users'][$now]['shoupai'];//下一个人的手牌
            $tishi =  shoupai($nextsp,$pai,$leix) ;
            if($tishi){
                $msp = $gameInfo['users'][$this->mid]['shoupai'];
                $data = [
                    'now'=> $now,
                    'mid'=>$this->mid,
                    'tishi'=>$tishi,
                    'pai'=>$pai,
                    'shoupai'=>$nextsp,
                    'type'=>$leix['type']

                ];
                $gameInfo['now'] = $now;//存该谁打牌
                $gameInfo['dachu']['tishi'] = $tishi;
                // yield $this->saveLogs(reData('dachu',$data)); //存游戏记录
                $this->sendToUids($this->uids,reData('dachu',$data),false);
                yield $this->redis_pool->hset($room_id, 'gameInfo',serialize($gameInfo));
                break;
            }else{

                if($next+1 == 3) {
                    $nextid = 0;//下一个人

                }else{
                    $nextid = $next+1;
                }
                $nextid = $roomInfo['weizhi'][$nextid];
                $data = [

                    'now'=>$now,
                    'nowshoupai'=>$nextsp,
                    'mid'=>$nextid,
                    'type'=> false,
                    'mg'=> '要不起'
                ];
                if($i == 2){
                    $gameInfo['now'] = $this->mid;//存该谁打牌
                }
                yield $this->redis_pool->hset($room_id, 'gameInfo',serialize($gameInfo));
                // yield $this->saveLogs(reData('dachu',$data));  //存游戏记录
                $this->sendToUids($this->uids,reData('guo',$data),false);

            }
        }
        return $gameInfo ;
    }
    /**
     * 二人玩.
     * User: shijunyi
     * Date: 3/22
     *
     */
    private function erren($gameInfo,$weizhi,$roomInfo,$pai,$leix,$room_id)
    {
        $weizhi += 1;
        if($weizhi > 1){
            $now = 0;
        }else{
            $now =$weizhi;
        }
        $now = $roomInfo['weizhi'][$now];//取出下一个人的mid
        $nextsp  =  $gameInfo['users'][$now]['shoupai'];//下一个人的手牌
        $tishi =  shoupai($nextsp,$pai,$leix) ;
        if($tishi){
            $data = [
                'now'=> $now,
                'mid'=>$this->mid,
                'tishi'=>$tishi,
                'pai'=>$pai,
                'nowshoupai'=>$nextsp,
                'type'=>$leix['type']
            ];
            $gameInfo['now'] = $now;//存该谁打牌
            $gameInfo['tishi'][$now] = $tishi;
            yield $this->redis_pool->hset($room_id, 'gameInfo',serialize($gameInfo));
            // yield $this->saveLogs(reData('dachu',$data));//游戏记录
            $this->sendToUids($this->uids,reData('dachu',$data),false);
        }else{
            $data = [

                'now'=>$now,
                'nowshoupai'=>$nextsp,
                'mid'=>$this->mid,
                'type'=> false,
                'mg'=> '要不起'
            ];
            yield $this->redis_pool->hset($room_id, 'gameInfo',serialize($gameInfo));
            // yield $this->saveLogs(reData('dachu',$data));//游戏记录
            $this->sendToUids($this->uids,reData('guo',$data),false);
        }
        return $gameInfo;
    }
    /**
     * 每局结束.
     * User: shijunyi
     * Date: 3/22
     *
     */
    private function jieshu($mid,$gameInfo)
    {    return false;
//        $roomInfo = $this->roomInfo;
//        //判断游戏是否结束
//        if($roomInfo['nowjushu'] >= $roomInfo['guize']['jushu']){
//            $game_status = 0;
//        }else{
//            $game_status = 1;
//        }
//        $data = [
//            'win'=>$mid,
//            'upais'=>$gameInfo['users'],  //所有人的手牌 和信息
//            'users'=>$this->userInfo,
//            'jifen'=>
//
//        ];
//        $users = $gameInfo['users'];
//        $shu = array_diff($users,$mid);
//        $ying = '';
//        foreach($shu as $k => $v){
//            if($v == $gameInfo['niaoid'] && $gameInfo['niaoid']){
//              $shu =  count($v['shoupai'])*2;
//            }else{
//                $shu = count($v['shoupai']);
//            }
//            if(count($v['shoupai']) == 1){
//                $shu = 0;
//            }
//
//            $ying += $shu;
//            $gameInfo['user'][$v]['fen'] =  '-'.$shu;
//        }
//        $gameInfo['user'][$mid]['fen'] = '+'.$ying;
//        $data = [
//            'win'=>$mid,
//            'upais'=>$gameInfo['users'],  //所有人的手牌 和信息
//            'users'=>$this->userInfo,
//        ];
//        $this->sendToUids($this->uids,reData('over', $data),false);
//        $gameInfo['users'][$k]['shoupai'] = [];
//        $gameInfo['users'][$k]['dachu'] = [];
//        $roomInfo['nowjushu'] +=1;
//        $gameInfo['now'] = $mid;
//        yield $this->redis_pool->hset($this->room_id, 'gameInfo',serialize($gameInfo),'roomInfo',serialize($roomInfo));
    } //!!!!!!!!!!!!!!!!!
    /**
     * 保存记录
     */
    private function saveLogs($log,$result=false){
        //添加游戏记录
        $log = json_encode($log,JSON_UNESCAPED_UNICODE);
        $key = yield  $this->redis_pool->getCoroutine()->Scard("logs_".$this->room_id);
        $re = $key.'--'.$log;
        $roomInfo = $this->roomInfo;
        yield  $this->redis_pool->getCoroutine()->sAdd("logs_".$this->room_id,$re);
        if($result){
            //保存游戏记录
            $re =  yield  $this->redis_pool->getCoroutine()->sMembers("logs_".$this->room_id);
            $data = json_encode($re,JSON_UNESCAPED_UNICODE);
            yield $this->mysql_pool->dbQueryBuilder
                ->insert('gs_logs_info')
                ->set('ju',$roomInfo['nowjushu'])
                ->set('info',$data)
                ->set('room_id',$this->room_id)
                ->coroutineSend();
            //如果当前局数==1 并且打完了 那么开始扣砖石
            if($roomInfo['nowjushu'] == 1){
                $rooms = yield $this->mysql_pool->dbQueryBuilder
                    ->select('status')
                    ->select('num')
                    ->select('f_id')
                    ->where('room_id', $this->room_id)
                    ->from('gs_rooms')
                    ->coroutineSend();
                $rooms = $rooms['result'][0];
                if($rooms['status'] == 2 ){
                    yield $this->mysql_pool->dbQueryBuilder
                        ->update('gs_am')
                        ->set('num',"num-{$rooms['num']}",false)
                        ->where('mid',$rooms['mid'])
                        ->coroutineSend();
                }else{
                    if($rooms['type'] == 0){
                        $title = '创建房间';
                    }else{
                        $title = '代开房间';
                    }
                yield $this->mysql_pool->dbQueryBuilder
                    ->update('gs_member')
                    ->set('roomnums',"roomnums+1",false)
                    ->set('num',"num-{$rooms['num']}",false)
                    ->where('id',$rooms['mid'])
                    ->coroutineSend();
                yield $this->mysql_pool->dbQueryBuilder
                    ->insert('gs_mnum_info')
                    ->set('mid',$rooms['mid'])
                    ->set('num',$rooms['num'] * -1)
                    ->set('gid',51)
                    ->set('room_id',$this->room_id)
                    ->set('title',$title)
                    ->coroutineSend();
            }
            //加入room_user
            foreach ($this->uids as  $v){
                yield $this->mysql_pool->dbQueryBuilder
                    ->insert('gs_rooms_user')
                    ->set('room_id',$this->room_id)
                    ->set('mid',$v)
                    ->coroutineSend();
            }
            //修改状态
            yield $this->mysql_pool->dbQueryBuilder
                ->update('gs_rooms')
                ->set('status',2)
                ->where('room_id',$this->room_id)
                ->coroutineSend();
        }
        //修改战绩总记录
        yield $this->mysql_pool->dbQueryBuilder
            ->update('gs_rooms')
            ->set('result',$result)
            ->where('room_id',$this->room_id)
            ->coroutineSend();

        //删除redis
        yield  $this->redis_pool->getCoroutine()->del("logs_".$this->room_id);
    }
}//!!!!!!!!!!!!!!
}