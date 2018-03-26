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
              //自己不用发
              $uids = array_diff($this->uids, [$this->mid]);
              $this->sendToUids($uids, reData('chonglian', $this->mid), false);
          }

          if($re['game_start'] == 1){
              E('开始游戏');
              $this->sendToUids($this->uids, reData('game_go', '开始游戏'), false);

              yield $this->fapai($re('gameInfo'),$re('roomInfo'),$re('userInfo'));
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
            $leix = panduan($pai,$shoupai);//判断打出牌是否在手牌中

             //如果返回的类型
             if($leix){
                 $dc = zhuanhuan($pai);
                 $sjp = zhuanhuan($gameInfo['dachu']['pai']);
                 if(isset($gameInfo['dachu']) || $gameInfo['dachu']){
                     if($gameInfo['dachu']['mid'] != $gameInfo['now'] && $sjp[0] > $dc[0] && $leix['type'] == $gameInfo['dachu']['leix']['type']){
                         $this->send('牌太小',false);
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
                    $this->send('牌型有误',false);
                        }


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
                $data = [
                    'now'=> $now,
                    'mid'=>$this->mid,
                    'tishi'=>$tishi,
                    'pai'=>$pai,
                    'nowshoupai'=>$nextsp,
                    'type'=>$leix['type']

                ];
                $gameInfo['now'] = $now;//存该谁打牌
                $gameInfo['dachu']['tishi'] = $tishi;

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
            $this->sendToUids($this->uids,reData('guo',$data),false);
        }
        return $gameInfo;
    }
    /**
     * 消息.
     * User: shijunyi
     * Date: 3/22
     *
     */
    public function xiaoxi()
    {
        if ($this->is_destroy) {
            return;
        }
        D('消息', ['mid' => $this->mid, 'room_id' => $this->room_id]);
        $this->sendToUids($this->uids, reData('xiaoxi', $this->data));
    }
    /**
     * 每局结束.
     * User: shijunyi
     * Date: 3/22
     *
     */
    private function jieshu($mid,$gameInfo)
    {

       $roomInfo =  $this->roomInfo;

       if($roomInfo['nowjushu'] == $roomInfo['guize']['jushu']){
                $data = [
                  'roomInfo'=> $this->roomInfo,
                   'userInfo'=> $this->userInfo,
                    'gameInfo'=>$gameInfo

                ];
           yield $this->redis_pool->del($this->room_id);
           yield $this->redis_pool->del('uids_'.$this->room_id);

                $this->sendToUids($this->uids,reData('game_over', $data),false);
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
           $this->sendToUids($this->uids,reData('over', $data),false);
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