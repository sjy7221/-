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
class RoomController extends Controller
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
//            $this->close();
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
     * 进入房间流程.
     * User: shijunyi
     * Date: 3/22
     *
     */
    public function jinru()
    {
        D('jinru',$this->data);

        if ($this->is_destroy) {

            return;
        }
        //判断房间人数
        if (!in_array($this->mid,$this->uids)  && count($this->uids) >= $this->roomInfo['guize']['renshu']) {
            $this->send(reData('out', '房间人数已满'),false);
            $this->close();
            return;

        }
        //模型处理数据
        $re = yield $this->CommModel->jinru($this->mid, $this->room_id, $this->roomInfo,$this->userInfo,$this->gameInfo);
        if($re){
            yield $this->redis_pool->getCoroutine()->hset('uids_'.$this->room_id,$this->mid,1); //设置用户状态
            //所有玩家状态
            $re['roomInfo']['users_status'] = yield $this->redis_pool->getCoroutine()->Hgetall('uids_'.$this->room_id);
            if($this->roomInfo['status'] == 0){
                if(!in_array($this->mid,$this->uids)){
                    $this->uids[] = $this->mid;
                }
                $data = [
                    'roomInfo'=>$re['roomInfo'],
                    'userInfo'=> $re['userInfo'],
                    'mid'=>$this->mid
                ];
                $users =  yield $this->redis_pool->getCoroutine()->hkeys('uids_' . $this->room_id);

                $this->sendToUids($users, reData('jinru', $data), false);
            }else{
                $data = [
                    'roomInfo'=>$re['roomInfo'],
                    'userInfo'=> $re['userInfo'],
                    'mid'=>$this->mid
                ];
                $this->send(reData('jinru', $data),false);
            }

            if($re['is_user'] == 1){
               D('room 重连',$this->mid);
                $uids = array_diff($this->uids, [$this->mid]);
                $this->sendToUid($this->mid, reData('chonglian', $this->mid), false);
            }

            if($re['game_start'] == 1 ){
                E('开始游戏');
//              $this->sendToUids($this->uids, reData('game_go', '开始游戏'), false);


                    $re['gameInfo']['zhadan'] = 0;
                   if($re['roomInfo']['nowjushu'] != 1){
                       return;
                   }
                yield $this->fapai($re['gameInfo'],$re['roomInfo'],$re['userInfo']);
            }
        }

        $this->destroy();

    }


    /**
     * 离开房间.
     * User: shijunyi
     * Date: 3/22
     *
     */
    public function game_exit()
    {
        if ($this->is_destroy) {
            return;
        }
        //模型处理数据
        $re = yield $this->CommModel->likai($this->mid, $this->room_id, $this->roomInfo);
        if($re){
            $re['likai'] = $this->mid;
            $this->sendToUids($this->uids, reData('likai',$re), false);
            //解绑房间信息
            yield $this->redis_pool->getCoroutine()->HDEL('uids_'.$this->room_id,$this->mid);
        }
        $this->destroy();
    }

    //消息
    public function xiaoxi()
    {
        if ($this->is_destroy) {
            return;
        }
        D('消息', ['mid' => $this->data->mid, 'room_id' => $this->room_id]);
        $this->sendToUids($this->uids, reData('xiaoxi', $this->data));
    }

    public function heartbeat()
    {
        if ($this->is_destroy) {
            return;
        }
//        if(yield $this->redis_pool->getCoroutine()->get('del_'.$this->room_id)){
//            $this->send(reData('out', '房主解散房间'), false);
//        }
        if($this->roomid){
            $redis_key = 'heartbeat_' . $this->room_id;
            if (yield $this->redis_pool->getCoroutine()->setnx($redis_key, time())) {
                yield $this->redis_pool->expireAt($redis_key, time() + 10);
                $this->sendToUids($this->uids,reData('heartbeat',$this->room_id));
            } else {
                $this->destroy();
            }
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
        $gameInfo['one'] = 1;
        $roomid = $roomInfo['guize']['room_id'];
//        $gameInfo['one'] = 1;
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