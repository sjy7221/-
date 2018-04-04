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
        $res =  yield $this->CommModel->exiit($this->data);//判断传过来的类型;

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
        //如果打完

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
            D('通过牌型',1);
            if($roomInfo['guize']['suanfa'][0] == 31  && $roomInfo['nowjushu'] == 1  && $gameInfo['one'] == 1){
                        if(!in_array(31,$pai)) {
                            $this->send(reData('error', ['msg' => '首局先出黑桃3']), false);
                            return;
                        }


            }
            $dc = zhuanhuan($pai); //去掉打出花色

            sort($dc);
            D('打出：',$pai);
            //判断打出的牌大小
           $dtype = $leix['type'];
            $dcz =  $leix['zhu'];
            D('现在打出类型比牌数',$dcz);
            if(isset($gameInfo['dachu']['leix']) && isset($gameInfo['dachu']['pai']) && $gameInfo['dachu']['leix'] && $gameInfo['dachu']['pai']){

                $stype = $gameInfo['dachu']['leix']['type'];
                $sjz = $gameInfo['dachu']['leix']['zhu'];
                D('上局打出类型比牌数',$sjz);
                $sjp = $gameInfo['dachu']['pai'];//去上家打出花色
                sort($sjp);
                D('上副打出：',$sjp);
                //类型不同
                if($dtype != $stype && $leix['type'] != 10){
                    D('牌型不对',2);
                    $this->send(reData('error', ['msg'=>'牌型不对']),false);
                    return;
                }
                // 类型同 大小不同
                if($dtype == $stype &&  $dcz <= $sjz){
                    $this->send(reData('error', ['msg'=>'大小不对']),false);
                    return;
                }
            }

//            $gameInfo['users'][$this->mid]['zhadan'] = 0;
            $weizhi =  array_search($this->mid,$roomInfo['weizhi']);//当前位置;
            if($leix['type'] == 10){ //炸弹数
                $gameInfo['users'][$this->mid]['zhadan'] +=1; //个人炸弹数
                $gameInfo['zhadan'] +=1;  //每局总炸弹数
            }

            //从手牌中去除打出的牌
            $req =  array_diff($shoupai,$pai);
            D('剩余牌',$pai);
            if(!empty($req)){//如果没打完
                sort($req);
                $gameInfo['users'][$this->mid]['shoupai'] = $req;//把剩余的手牌存起来
                if($roomInfo['guize']['renshu'] == 3){
                    $gameInfo['dachu']['mid'] = $this->mid;//打出牌人的id
                    $gameInfo['dachu']['pai'] = $pai;//打出的牌
                    $gameInfo['dachu']['leix'] = $leix;//打出的类型
                    $now = sweizhi($weizhi,$roomInfo);

                    $msp =  $gameInfo['users'][$this->mid]['shoupai'];
                    $nextsp  =  $gameInfo['users'][$now]['shoupai'];//下一个人的手牌;
                    $tishi =  shoupai($nextsp,$pai,$leix) ;

                    $gameInfo['dachu']['tishi'] = $tishi;
                    $gameInfo['now'] = $now;
                        $guo = [];

                    if(!$tishi){
                        $weizhi =  array_search($now,$roomInfo['weizhi']);//当前位置;
                        $nextid = sweizhi($weizhi,$roomInfo);
                        $data = [

                            'now'=>$nextid,
                            'pai'=>$pai,
                            'mid'=>$now,
                            'type'=> false,
                            'mg'=> '要不起'
                        ];
                        $gameInfo['dachu']['tishi'] = [];
                        $gameInfo['now'] = $nextid;
                        $gameInfo['one'] == 0;

                        $guo[] = $data;
                        $nextsp2  =  $gameInfo['users'][$nextid]['shoupai'];//下一个人的手牌;
                        $tishi2 =  shoupai($nextsp2,$pai,$leix) ;
                        if($tishi2){
                            $gameInfo['dachu']['tishi'] = $tishi2;
                            $gameInfo['now'] = $nextid;
                            $gameInfo['one'] == 0;
//                            $this->sendToUids($this->uids,reData('dachu',$data),false);
                        }else{

                            $data = [

                                'now'=>$this->mid,
                                'pai'=>$pai,
                                'mid'=>$nextid,
                                'type'=> false,
                                'mg'=> '要不起'
                            ];
                            $gameInfo['dachu']['tishi'] = [];
                            $gameInfo['now'] = $this->mid;
                            $gameInfo['one'] == 0;
                            $guo[] = $data;
//                            $this->sendToUids($this->uids,reData('guo',$data),false);
                        }
                    }

                if($gameInfo['now'] == $this->mid){
                    $gameInfo['dachu']['leix'] = [];
                    $gameInfo['dachu']['pai'] = [];

                }
                  $users = $this->uids;
                    $tishi = $gameInfo['dachu']['tishi'];
                    foreach($users as $k => $v){
                        $data = [
                            'now' => $gameInfo['now'],
                            'tishi' => $tishi,
                            'mid'=>$this->mid,
                            'pai'=>$pai,

                            'type'=>$leix['type'],
                            'shoupai'=>$gameInfo['users'][$v]['shoupai']

                        ];

                        $this->sendToUid($v,reData('dachu',$data),false);
                    }
                /////////////存游戏记录
                    $data = [
                        'now' => $gameInfo['now'],
                        'tishi' => $tishi,
                        'mid'=>$this->mid,
                        'pai'=>$pai,

                        'type'=>$leix['type'],
                        'shoupai'=>$gameInfo['users'][$v]['shoupai']

                    ];
                    yield $this->saveLogs(reData('dachu',$data)); //存游戏记录
                    foreach ($guo as $k=>$v){
                        yield sleepCoroutine(500);
                        $this->sendToUids($this->uids,reData('guo',$v),false);
                        yield $this->saveLogs(reData('guo',$v)); //存游戏记录
                    }
                    $gameInfo['one'] = 0;
               yield $this->redis_pool->hset($room_id, 'gameInfo',serialize($gameInfo));

                }elseif($roomInfo['guize']['renshu'] == 2){        //如果是两人房
                    $gameInfo = yield $this->erren($gameInfo,$weizhi,$roomInfo,$pai,$leix,$room_id); //2个人的玩法
                }

                $gameInfo['one'] == 0;
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
            yield  $this->fapai($this->gameInfo,$this->roomInfo,$this->userInfo);
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
        $this->destroy();
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
        D('getGame 重连',$this->mid);
        $jiesan = [];
        $jixu = [];
        $jinru = [];
        $dapai = [];
        $gameInfo = $this->gameInfo;
//        //房间内准备状态  并且游戏数据有
//        if($this->roomInfo['status'] == 0 || empty($gameInfo['users'][$this->mid]['shoupai'])){
//
//            $data = [
//              'status'=> 0,
//              'users'=>$this->userInfo,
//              'roomInfo'=>$this->roomInfo
//            ];
//
//            $jinru = $data;
//            $data = [
//                'jiesan'=>[],
//                'jixu'=>[],
//                'jinru'=>$jinru,
//                'dapai'=>[]
//            ];
//            $this->send(reData('getGame',$data),false);
////            return;
//        }
        $roomInfo =  $this->roomInfo;
        $weizhi =  $roomInfo['weizhi'];
        $gameInfo =  $this->gameInfo;

        //如果当前正在发起解散
        if( yield  $this->redis_pool->getCoroutine()->exists("js_".$this->room_id)){
            $re = yield  $this->redis_pool->getCoroutine()->hGetAll("js_".$this->room_id);
            //先发发起结算的人
            foreach ($re as $kk=>$vv){
                if($vv  == 2){
                    $jiesan[$kk]['status'] = $vv;
//                    $this->sendToUids($this->uids,reData('jiesan',['mid'=>$kk,'status'=>$vv]),false);
                }
            }
            //再发选择状态的人
            foreach ($re as $kk2=>$vv2){
                if($vv2 != 2){
                    $jiesan[$kk2]['status'] = $vv2;
//                    $this->sendToUids($this->uids,reData('jiesan',['mid'=>$kk2,'status'=>$vv2]),false);
                }
            }
            $data = [
                'jiesan'=>$jiesan,
                'jixu'=>[],
                'jinru'=>[],
                'dapai'=>[]
            ];
            $this->send(reData('getGame',$data));
            return;
        }
        //如果当前正在发继续
        if( yield  $this->redis_pool->getCoroutine()->exists("jx_".$this->room_id) || $this->roomInfo['status'] == 0 || empty($gameInfo['users'][$this->mid]['shoupai'])){
           $users =  yield  $this->redis_pool->getCoroutine()->smembers("jx_".$this->room_id);
            $data = [
              'users'=>$this->userInfo,
              'room'=>$this->roomInfo,
              'jxusers'=>$users
            ];

            $jixu = $data;
            $data = [
                'jiesan'=>[],
                'jixu'=>$jixu,
                'jinru'=>[],
                'dapai'=>[]
            ];
            $this->send(reData('getGame',$data));
            return;
        }
        if($roomInfo['nowjushu'] >= $roomInfo['guize']['jushu']){
            yield  $this->redis_pool->getCoroutine()->delete($this->room_id);  //房间所有数据
            yield  $this->redis_pool->getCoroutine()->delete('uids_'.$this->room_id);  //玩家id
            yield  $this->redis_pool->getCoroutine()->delete('jx_'.$this->room_id);    //继续
            yield  $this->redis_pool->getCoroutine()->delete('js_'.$this->room_id);    //解散
            yield  $this->redis_pool->getCoroutine()->delete('logs_'.$this->room_id);   //游戏记录
            $this->send('game_over',false);
          

        }
        if($gameInfo['now'] || isset($gameInfo['now'])){
            $users = $this->uids;
            $mid = $this->mid;
            $users = array_diff($users,[$mid]);

            $countp = [];
           foreach ($users as $k => $v){
                $countp[$v] = count($gameInfo['users'][$v]['shoupai']);
           }

            $data = [
                'ju'=>$roomInfo['nowjushu'],                   //当前局数
                'pais'=>$gameInfo['users'][$this->mid]['shoupai'],                          //手牌
                'dachu'=>$gameInfo['dachu'],           //打出的所有信息
                'now'=>$gameInfo['now'],//当前操作玩家
                'users'=>$this->userInfo,  //玩家信息
                'countpai'=>$countp //其他玩家手牌长度
            ];
            $dapai = $data;
        }

            $data = [
                'jiesan'=>[],
                'jixu'=>[],
                'jinru'=>[],
                'dapai'=>$dapai
            ];
        $this->send(reData('getGame',$data));
        $this->destroy();
    }
    /**
     * 总结算.
     * User: shijunyi
     * Date: 3/22
     *
     */
    public function getlog()
    {
       $gameInfo = $this->gameInfo;
       $roomInfo = $this->roomInfo;
       $uids = $this->uids;
       $f = [];
       $roomInfo['getlog'][] = $this->mid;
       foreach($uids as $k =>$v)
       {
         $f[$v] =  $roomInfo['over'][$v]['zf'];
       }
        $key = array_search(max($f),$f);

       $data = [
         'roomInfo'=>$roomInfo['over'],
           'userInfo'=>$this->userInfo,
           'fangzhu'=>$roomInfo['fangzhu'],
           'guize'=>$roomInfo['guize'],
           'dyj'=>$key
       ];
        $this->sendToUid($this->mid,reData('getlog', $data),false);
        $zf = $roomInfo['over'][$this->mid]['zf'];
        yield $this->mysql_pool->dbQueryBuilder ////用户表绑定房间号
        ->update('gs_rooms_user')
            ->set('type', 1)
            ->set('fen', $zf)
            ->where('mid', $this->mid)
            ->where('room_id', $this->room_id)
            ->coroutineSend();
        yield $this->redis_pool->hset($this->room_id,'roomInfo',serialize($roomInfo));
        if(count($roomInfo['getlog']) >= $roomInfo['guize']['renshu']){
            yield  $this->redis_pool->getCoroutine()->delete($this->room_id);  //房间所有数据
            yield  $this->redis_pool->getCoroutine()->delete('uids_'.$this->room_id);  //玩家id
            yield  $this->redis_pool->getCoroutine()->delete('jx_'.$this->room_id);    //继续
            yield  $this->redis_pool->getCoroutine()->delete('js_'.$this->room_id);    //解散
            yield  $this->redis_pool->getCoroutine()->delete('logs_'.$this->room_id);   //游戏记录
        }

        $this->destroy();
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
        $roomInfo['status'] = 1;
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
        $data = [
            'roomInfo'=>$roomInfo,//
            'userInfo'=>$userInfo,//
            'now'=>$gameInfo['now'],
            'gameInfo'=>$gameInfo

        ];
         yield $this->saveLogs(reData('fapai',$data)); //存游戏记录
        $this->destroy();
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
        $gameInfo['dachu']['tishi'] = $tishi;
        $gameInfo['now'] = $now;
        $users = $this->uids;
        $tishi = $gameInfo['dachu']['tishi'];
        foreach($users as $k => $v){
            $data = [
                'now' => $gameInfo['now'],
                'tishi' => $tishi,
                'mid'=>$this->mid,
                'pai'=>$pai,

                'type'=>$leix['type'],
                'shoupai'=>$gameInfo['users'][$v]['shoupai']

            ];

            $this->sendToUid($v,reData('dachu',$data),false);
        }
        /////////////存游戏记录
        $data = [
            'now' => $gameInfo['now'],
            'tishi' => $tishi,
            'mid'=>$this->mid,
            'pai'=>$pai,

            'type'=>$leix['type'],
            'shoupai'=>$gameInfo['users'][$v]['shoupai']

        ];
        yield $this->saveLogs(reData('dachu',$data)); //存游戏记录
        if(!$tishi){

            $data = [

                'now'=>$this->mid,
                'pai'=>$pai,
                'mid'=>$now,
                'type'=> false,
                'mg'=> '要不起'
            ];

            $this->sendToUids($this->uids,reData('guo',$data),false);
            yield $this->saveLogs(reData('dachu',$data));//游戏记录
            $gameInfo['dachu']['tishi'] = [];
            $gameInfo['now'] = $this->mid;
            $gameInfo['dachu']['leix'] = [];
            $gameInfo['dachu']['pai'] = [];
            $gameInfo['one'] == 0;
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
    {
        $roomInfo = $this->roomInfo;
        //判断游戏是否结束
        if($roomInfo['nowjushu'] >= $roomInfo['guize']['jushu']){
            $game_status = 0;
        }else{
            $game_status = 1;
        }

        $users = $roomInfo['users'];
       D('所有用户',$users);

        $shuren = array_diff($users,[$mid]);
        $ying = 0;
        $shu = '';
        $zdjf = $gameInfo['zhadan']*10;
        foreach($shuren as $k => $v){
            $shu = count($gameInfo['users'][$v]['shoupai']);
            if(isset($gameInfo['niaoid']) && $v == $gameInfo['niaoid'] && $gameInfo['niaoid']){
                $shu =  count($gameInfo['users'][$v]['shoupai'])*2 ;
            }
            D('结算手牌',count($gameInfo['users'][$v]['shoupai']));
            if(count($gameInfo['users'][$v]['shoupai']) == 48/$roomInfo['guize']['renshu']){
                $shu =  count($gameInfo['users'][$v]['shoupai'])*2 ;
            }
            // 有鸟牌 且全关
            if($v == $gameInfo['niaoid'] && isset($gameInfo['niaoid']) && count($gameInfo['users'][$v]['shoupai']) == 48/$roomInfo['guize']['renshu']){
              $shu =  count($gameInfo['users'][$v]['shoupai'])*4 ;
//              $shu += $shu*$gameInfo['users'][$v]['zhadan'];
            }


                $shu += $zdjf;
            if(count($gameInfo['users'][$v]['shoupai']) == 1){
                $shu = 0;
            }
//            var_dump($roomInfo['over'][$v]['zhadan']);
//            var_dump($gameInfo['users'][$v]['zhadan']);
            $ying += $shu; //每局赢的积分
            $gameInfo['users'][$v]['fen'] =  '-'.$shu; //输的积分
            $gameInfo['users'][$v]['fenshu'] -=   $shu ; //总分数
            $roomInfo['over'][$v]['zhadan'] += $gameInfo['users'][$v]['zhadan'];
            $roomInfo['over'][$v]['shu'] += 1;
            $roomInfo['over'][$v]['zf'] -= $shu;
        }
        $roomInfo['over'][$mid]['zhadan'] +=  $gameInfo['users'][$mid]['zhadan'];
        $roomInfo['over'][$mid]['ying'] += 1;
        $roomInfo['over'][$mid]['zf'] += $ying;
        if($roomInfo['over'][$mid]['zg'] < $ying){
            $roomInfo['over'][$mid]['zg'] = $ying;
        }
        $gameInfo['users'][$mid]['fen'] = '+'.$ying;//每局赢的积分
        $gameInfo['users'][$mid]['fenshu'] +=  $ying;
        $gameInfo['users'][$mid]['shoupai'] = [];
        $roomInfo['nowjushu'] +=1; //局数加1
        $gameInfo['now'] = $mid;

        $data = [
            'win'=>$mid, //赢的id
            'upais'=>$gameInfo['users'],  //所有人的手牌 和信息
            'users'=>$this->userInfo, //用户信息
            'niaoid'=>$gameInfo['niaoid'], //鸟牌id
            'nowjushu'=>$roomInfo['nowjushu'],
            'status'=>$game_status //是否结束
        ];

        $this->sendToUids($this->uids,reData('over', $data),false);
        yield $this->saveLogs(reData('over',$data)); //存游戏记录
        // 存每局记录
        $userInfo = $this->userInfo;
        $users  = $this->uids;
        $res = [] ;
        foreach($users as $k => $v){
            $name =   $userInfo[$v]['nickname'];
            $res[$v][$name] =  $gameInfo['users'][$v]['fen'];

        }
        $res = json_encode($res,JSON_UNESCAPED_UNICODE);
        yield $this->saveLogs(reData('over',$data),$res);
        foreach($users as $kk => $vv){
            $gameInfo['users'][$vv]['shoupai'] = [];

            $gameInfo['users'][$vv]['zhadan'] = 0;
        }
        $gameInfo['dachu']['mid'] = 0;
        $gameInfo['dachu']['pai'] = [];
        $gameInfo['dachu']['leix'] = [];
        $gameInfo['dachu']['tishi'] = [];
        $gameInfo['zhadan'] = 0;
        $roomInfo['status'] = 0;
        yield $this->redis_pool->hset($this->room_id, 'gameInfo',serialize($gameInfo),'roomInfo',serialize($roomInfo));
        if(!$game_status){       //全部打完 清空数据

            yield  $this->mysql_pool->dbQueryBuilder
                ->update('gs_member')
                ->set('room_id',0)
                ->where('room_id',$this->room_id)
                ->coroutineSend();
        }
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
                ->set('users',$result)
                ->set('room_id',$this->room_id)
                ->coroutineSend();
            //如果当前局数==1 并且打完了 那么开始扣砖石
            if($roomInfo['nowjushu'] == 1){
                $numb = 0;
                if($roomInfo['guize']['jushu'] == 10){
                    $numb = 1;
                }
                if($roomInfo['guize']['jushu'] == 20){
                    $numb = 2;
                }
                yield $this->mysql_pool->dbQueryBuilder
                    ->update('gs_member')
                    ->set('num',"num-{$numb}",false)
                    ->where('id',$roomInfo['fangzhu'])
                    ->coroutineSend();

            //加入room_user
//            foreach ($this->uids as  $v){
//                yield $this->mysql_pool->dbQueryBuilder
//                    ->insert('gs_rooms_user')
//                    ->set('room_id',$this->room_id)
//                    ->set('mid',$v)
//                    ->coroutineSend();
//            }

        }
            if($roomInfo['nowjushu'] == $roomInfo['guize']['jushu']){
                //修改状态
                yield $this->mysql_pool->dbQueryBuilder
                    ->update('gs_rooms')
                    ->set('status',2)
//                    ->set('result',$result)
                    ->where('room_id',$this->room_id)
                    ->coroutineSend();
            }

        //删除redis
        yield  $this->redis_pool->getCoroutine()->del("logs_".$this->room_id);
    }
}
}