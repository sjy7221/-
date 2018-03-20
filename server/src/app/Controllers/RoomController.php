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
            $this->userInfo = unserialize($room['userInfo']);
            $this->gameInfo = unserialize($room['gameInfo']);
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



        $re = yield $this->CommModel->jinru($this->mid, $this->room_id, $this->roomInfo,$this->userInfo,$this->gameInfo);

       if(!$re['game_start']){
         $data = [
            'route'=>'jinru',
            'roomInfo'=>$this->roomInfo,
            'userInfo'=> $this->userInfo,
            'gameInfo'=> $this->gameInfo
        ];
     
         $this->sendToUids($this->uids, $data, false);

       }else{
 
         // $this->fapai($re['roomInfo']['guize']['renshu'],$re['roomInfo']['guize']['room_id']);
     
         $this->sendToUids($this->uids,['game_go','游戏开始'],false);
    
       }
       $this->destroy();

    }

    public function fapai()
    {
             echo  "【fapai】".json_encode($this->data). "\n";
      if ($this->is_destroy) {
            return;
        }
     //开局人数

        $gameInfo =  $this->gameInfo;
        $roomInfo = $this->roomInfo;
        $userInfo = $this->userInfo;
        $renshu = $roomInfo['guize']['renshu'];
        $roomid = $roomInfo['guize']['room_id'];
        $pai = [31,32,33,34,41,42,43,44,51,52,53,54,61,62,63,64,71,72,73,74,81,82,83,84,91,92,93,94,101,102,103,104,111,112,113,114,121,122,123,124,131,132,133,134,141,142,143,160];
        shuffle($pai);

        $numb = count($pai)/$renshu;
        $pais = [];
      

        for($i = 0;$i<$renshu;$i++){
            for($j=0;$j<$numb;$j++){
               $pais[$i][] =  array_pop($pai);
            }
            sort($pais[$i]);
           
        }
       
            $o = -1;
            $h3id = '';
            $niaoid = '';
        foreach ( $gameInfo['users'] as $k=>$v) {

                   $o++;
           $gameInfo['users'][$k]['shoupai'] = $pais[$o];
         
        }
        // var_dump( $gameInfo);
        //找出牌中黑桃三先出的mid 和鸟牌 mid
       foreach($gameInfo['users'] as $kk=>$vv){
        //黑桃三先出的mid 
        if(!(array_search(31,$gameInfo['users'][$kk]['shoupai']) === false)){
            $h3id = $kk;
        }
        // 鸟牌 mid
        if(!(array_search(102,$gameInfo['users'][$kk]['shoupai']) === false)){
            $niaoid = $kk;
        }
       }

       if($roomInfo['guize']['suanfa'][0] && $roomInfo['nowjushu'] == 1){
            $gameInfo['now'] = $h3id;
       }elseif($roomInfo['nowjushu'] == 1){
         $gameInfo['now'] = array_rand( $gameInfo['users'], 1 );
         $gameInfo['now'] = $gameInfo['now']['id'];
       }
        if(isset($roomInfo['guize']['suanfa'][1]) && $roomInfo['guize']['suanfa'][1]){
            $roomInfo['niaoid'] = $niaoid;
        }
        if(isset($roomInfo['guize']['suanfa'][2]) && $roomInfo['guize']['suanfa'][2]){
            $roomInfo['xianshi'] = 1;
        }
  
          yield $this->redis_pool->hset($roomid, 'roomInfo', serialize($roomInfo),'gameInfo',serialize($gameInfo));

          foreach($gameInfo['users'] as $us => $u){

            $data = [
                'route'=>'fapai',
                'roomInfo'=>$roomInfo,
                'userInfo'=>$userInfo,
                'now'=>$gameInfo['now'],
                'pai'=>$u['shoupai']
                
        ];

             $this->sendToUid($us,$data,false);
          }
          $this->destroy();
    }
    public function dachu()
    {
        echo  "【dachu】".json_encode($this->data). "\n";
            if ($this->is_destroy) {
            return;
        }
        var_dump ($this->data->pai);
        $pai = $this->data->pai;
        
        if(count($pai)== 1){
             $leix =   dan($pai);
            }elseif(count($pai) == 2){
             $leix =  duizi($pai);
            }elseif(count($pai) == 3){
             $leix =  hou3($pai);
            }elseif(count($pai)>=4){
   
               $leix =  liandui($pai); 
             }
          var_dump($leix);
    }

    //离开
    public function exit()
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


}