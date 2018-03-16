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
        echo  "【jinru】".json_encode($this->data). "\n";
      if ($this->is_destroy) {
            return;
        }
 
          if (!in_array($this->mid,$this->uids)  && count($this->uids) >= $this->roomInfo['guize']['renshu']) {
            $this->send('人数已满', false);
            $this->close();
            return '空';
        }

        //模型处理数据
        $re = yield $this->CommModel->jinru($this->mid, $this->room_id, $this->roomInfo);

       if(!$re['game_start']){
         $data = [
            'route'=>'jinru',
            'roomInfo'=>$re['roomInfo']
        ];
         $this->sendToUids($this->uids, $data, false);

       }else{
         $this->roomInfo = $re['roomInfo'];
        
        $this->sendToUids($this->uids,['game_go','游戏开始'],false);
        $this->fapai($re['roomInfo']['guize']['renshu'],$re['roomInfo']);
       }
       $this->destroy();

    }

    public function fapai($renshu,$users)
    {
  

        $pai = [31,32,33,34,41,42,43,44,51,52,53,54,61,62,63,64,71,72,73,74,81,82,83,84,91,92,93,94,101,102,103,104,111,112,113,114,121,122,123,124,131,132,133,134,141,142,143,160];
        shuffle($pai);

        $numb = count($pai)/$renshu;
        $weizhi = $users['weizhi'];
        for($i = 0;$i<$renshu;$i++){
            for($j=0;$j<$numb;$j++){
              $users['users'][$weizhi][$i]['pai'] = array_pop($pai);
            }
            sort( $users['users'][$weizhi][$i]['pai']);

        }
 
        var_dump($users['users'][$weizhi][2]['pai']);
       
    }
}