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
        } else {
            $this->send('找不到房间信息', false);
            $this->close();
            return;
        }
          //获取房间所有人
        $this->uids = yield $this->redis_pool->getCoroutine()->hkeys('uids_' . $this->room_id); 
        if(!$this->uid){
            $this->bindUid($this->mid);
        }
    }

    public function jinru()
    {
        var_dump($this->roomInfo);
      if ($this->is_destroy) {
            return;
        }
        var_dump($this->uids);
          if (!in_array($this->mid,$this->uids)  && count($this->uids) >= $this->roomInfo['guize']['renshu']) {
            $this->send('人数已满', false);
            $this->close();
            return;
        }
        //模型处理数据
        $re = yield $this->CommModel->jinru($this->mid, $this->room_id, $this->roomInfo);
       
       if($re['game_start']){
         $data = [
            'route'=>'jinru',
            'roomInfo'=>$re['roomInfo']
        ];
         $this->sendToUids($this->uids, $data, false);

       }else{
         
        $this->sendToUids($this->uids,['game_go','游戏开始'],false);
       }

    }

    public function game()
    {

    }
}