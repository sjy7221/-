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
     * 进入房间流程.
     * User: shijunyi
     * Date: 3/22
     *
     */
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
            'userInfo'=> $this->userInfo

        ];

         $this->sendToUids($this->uids, $data, false);

       }else{

         // $this->fapai($re['roomInfo']['guize']['renshu'],$re['roomInfo']['guize']['room_id']);

         $this->sendToUids($this->uids,['game_go','游戏开始'],false);

       }
       $this->destroy();

    }


    /**
     * 发牌流程.
     * User: shijunyi
     * Date: 3/22
     *
     */
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
        $pai = [31,32,33,34,41,42,43,44,51,52,53,54,61,62,63,64,71,72,73,74,81,82,83,84,91,92,93,94,101,102,103,104,111,112,113,114,121,122,123,124,131,132,133,134,144,142,143,160];
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
            $gameInfo['niaoid'] = $niaoid;
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
           //判断打出的牌型是否可出
                 if(count($pai)== 1){
          
             $leix =   dan($pai);
            }elseif(count($pai) == 2){
             $leix =  duizi($pai);
            }elseif(count($pai) == 3){
             $leix =  hou3($pai,$shoupai);
            }elseif(count($pai)>=4){
   
               $leix =  liandui($pai,$shoupai);
             }

             //如果返回的类型
             if($leix){

            $gameInfo['dachu']['mid'] = $this->mid;//打出牌人的id
            $gameInfo['dachu']['pai'] = $pai;//打出的牌
            $gameInfo['dachu']['leix'] = $leix;//打出的类型
        // $roomInfo['weizhi'] = [1,2,3];
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
                    var_dump($gameInfo['users'][$this->mid]['shoupai']);
                        echo '<br>';
                     if($roomInfo['guize']['renshu'] == 3){

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
                                 $data = [
                                     'route'=>'dachu',
                                     'now'=> $now,
                                     'mid'=>$this->mid,
                                     'tishi'=>$tishi,
                                     'pai'=>$pai,
                                     'nowshoupai'=>$nextsp,
                                     'type'=>$leix['type']

                                 ];
                                 $gameInfo['now'] = $now;//存该谁打牌
                                 $gameInfo['dachu']['tishi'] = $tishi;

                                 $this->sendToUids($this->uids,$data,false);

                                 break;
                             }else{

                                 if($next+1 == 3) {
                                     $nextid = 0;//下一个人

                                 }else{
                                     $nextid = $next+1;
                                 }
                                 $nextid = $roomInfo['weizhi'][$nextid];
                                     $data = [
                                         'route'=>'guo',
                                         'now'=>$now,
                                         'nowshoupai'=>$nextsp,
                                         'mid'=>$nextid,
                                         'type'=> false,
                                         'mg'=> '要不起'
                                     ];

                                     $this->sendToUids($this->uids,$data,false);
//                                 }

                             }
                         }
                     }elseif($roomInfo['guize']['renshu'] == 2){        //如果是两人房
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
                                   'route'=>'dachu',
                                     'now'=> $now,
                                     'mid'=>$this->mid,
                                     'tishi'=>$tishi,
                                     'pai'=>$pai,
                                     'nowshoupai'=>$nextsp,
                                     'type'=>$leix['type']
                             ];
                             $gameInfo['now'] = $now;//存该谁打牌
                             $gameInfo['tishi'][$now] = $tishi;

                             $this->sendToUids($this->uids,$data,false);
                         }else{
                             $data = [
                                 'route'=>'guo',
                                 'now'=>$now,
                                 'nowshoupai'=>$nextsp,
                                 'mid'=>$this->mid,
                                 'type'=> false,
                                 'mg'=> '要不起'
                             ];

                             $this->sendToUids($this->uids,$data,false);
                         }
                     }






                     /**
                      * 如果打入的牌型通不过
                      *
                      *
                      *
                      */
//                 }else{
//                     $this->send('牌型有误',false);
//                 }
//                 yield $this->redis_pool->hset($room_id, 'gameInfo',serialize($gameInfo));
                 }else{
                     var_dump(111111);
                     yield  $this->jieshu($this->mid,$gameInfo);
                 }
                     yield $this->redis_pool->hset($room_id, 'gameInfo',serialize($gameInfo));
                      }else{
                    $this->send('牌型有误',false);
                        }


                     $this->destroy();
    }

    /**
     * 每局结束.
     * User: shijunyi
     * Date: 3/22
     *
     */
    private function jieshu($mid,$gameInfo)
    {
        var_dump(3222);
       $roomInfo =  $this->roomInfo;

       if($roomInfo['nowjushu'] == $roomInfo['guize']['jushu']){
                $data = [
                    'route'=>'jieshu',
                  'roomInfo'=> $this->roomInfo,
                   'userInfo'=> $this->userInfo,
                    'gameInfo'=>$gameInfo

                ];
           yield $this->redis_pool->del($this->room_id);
           yield $this->redis_pool->del('uids_'.$this->room_id);

                $this->sendToUids($this->uids,$data,false);
       }else{
           $users = $gameInfo['users'];
           $jusers = $users;
           $zongjifen = '';
         foreach($users as $k=>$v){
                if($k == $gameInfo['niaoid'] && $mid == $k){ // 赢的人
                    unset($jusers[$k]);
                    foreach($jusers as $kk => $vv){
                        if(count($vv['shoupai']) == 1){
                            $vv['sjifen'] = 0;
//                            $gameInfo['users'][$kk]['sjifen'] = $vv['sjifen'];
                            $gameInfo['users'][$kk]['fenshu'] -= $vv['sjifen'];
                            $gameInfo['users'][$k]['fenshu'] += $gameInfo['users'][$kk]['sjifen'];
                        }else{
                            if(count($vv['shoupai']) == 48/$roomInfo['guize']['renshu'] ){
                                $vv['sjifen'] = (count($v['shoupai'])*2 + $v['zhadan']*10)*2;
                                $gameInfo['users'][$kk]['fenshu'] -= $vv['sjifen'];
                                $gameInfo['users'][$k]['fenshu'] += $gameInfo['users'][$kk]['sjifen'];
                            }else{
                                $vv['sjifen'] = count($v['shoupai']) * 2 ;
                                $gameInfo['users'][$kk]['fenshu'] -= $vv['sjifen'];
                                $gameInfo['users'][$k]['fenshu'] += $gameInfo['users'][$kk]['sjifen'];
                            }
                        }

                    }
                }else{
                    if($k == $mid){
                        unset($jusers[$k]);
                        foreach($jusers as $kk => $vv){
                            if(count($vv['shoupai']) == 1){
                                $vv['sjifen'] = 0;
                                $gameInfo['users'][$kk]['fenshu'] -= $vv['sjifen'];
                                $gameInfo['users'][$k]['fenshu'] += $gameInfo['users'][$kk]['sjifen'];
                            }else{
                                if(count($vv['shoupai']) == 48/$roomInfo['guize']['renshu'] && $kk == $gameInfo['niaoid'] ){
                                    $vv['sjifen'] = (count($v['shoupai'])*2 + $v['zhadan']*10)*2;
                                    $gameInfo['users'][$kk]['fenshu'] -= $vv['sjifen'];
                                    $gameInfo['users'][$k]['fenshu'] += $gameInfo['users'][$kk]['sjifen'];
                                }elseif(count($vv['shoupai']) == 48/$roomInfo['guize']['renshu']){
                                    $vv['sjifen'] = count($v['shoupai'])*2 + $v['zhadan']*10;
                                    $gameInfo['users'][$kk]['fenshu'] -= $vv['sjifen'];
                                    $gameInfo['users'][$k]['fenshu'] += $gameInfo['users'][$kk]['sjifen'];
                                }else{
                                    $vv['sjifen'] = count($v['shoupai'])  ;
                                    $gameInfo['users'][$kk]['fenshu'] -= $vv['sjifen'];
                                    $gameInfo['users'][$k]['fenshu'] += $gameInfo['users'][$kk]['sjifen'];
                                }
                            }

                        }
                    }
                }
             $gameInfo['users'][$k]['shoupai'] = [];
             $gameInfo['users'][$k]['dachu'] = [];

         }
           $roomInfo['nowjushu'] +=1;
            $gameInfo['now'] = $mid;
           yield $this->redis_pool->hset($this->room_id, 'gameInfo',serialize($gameInfo),'roomInfo',serialize($roomInfo));
           $data = [
               'route'=>'xjieshu',
               'roomInfo'=> $this->roomInfo,
               'userInfo'=> $this->userInfo,
               'gameInfo'=>$gameInfo

           ];
           $this->sendToUids($this->uids,$data,false);
       }

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


}