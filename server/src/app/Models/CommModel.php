<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models;


use Server\CoreBase\Model;

class CommModel extends Model
{

    public function exit($data)
    {
       if(empty($data->mid) || empty($data->room_id)){
       	return true;
       }
       if (!yield $this->redis_pool->EXISTS($data->room_id)) {
            return true;
        }
        return false;
    }
    public function jinru($mid,$room_id,$roomInfo,$userInfo,$gameInfo)
    {
             //判断是重连玩家 还是新进入的玩家
            if(in_array($mid,$roomInfo['weizhi'])){
                $is_user= 1;
            }else{
                //获取玩家信息
                $member = yield $this->mysql_pool->dbQueryBuilder
                    ->select('headimgurl')
                    ->select('nickname')
                    ->select('num')
                    ->select('ip')
                    ->select('sex')
                    ->where('id', $mid)
                    ->from('gs_member')
                    ->coroutineSend();
                if(empty($member)){
                    E('无信息');
                    return false;
                }
                $member = $member['result'][0];

                //新玩家加入weihzi

                $roomInfo['weizhi'][] = $mid;
                $roomInfo['users'][] = $mid;

                $userInfo['users'][$mid] = [
                    'id' => $mid,     //用户id
                    'headimgurl' => $member['headimgurl'], //用户头像
                    'nickname' => $member['nickname'],  //用户名称
                    'num' => $member['num'],    //用户砖石
                    'ip' => $member['ip'],      //用户ip
                    'sex'=>$member['sex'],       //性别

                ];
                $gameInfo['users'][$mid] = [
                    'id'=>$mid,
                    'shoupai' =>[],  //手牌
                    'dachu'=>[],    //打出的牌
                    'fenshu'=>1000  //分数
                ];
                yield $this->mysql_pool->dbQueryBuilder ////用户表绑定房间号
                ->update('gs_member')
                    ->set('room_id', $room_id)
                    ->where('id', $mid)
                    ->coroutineSend();
                 $is_user = 0; //用来判断是否重连
            }

            if(count($userInfo['users']) ==  $roomInfo['guize']['renshu'] && $roomInfo['status'] == 0){ //如果房间人数等于规则人数
            	  yield $this->mysql_pool->dbQueryBuilder
                ->update('gs_rooms')
                ->set('status',1)                   //房间状态改为1;
                ->where('room_id',$room_id)
                ->coroutineSend();
            	  $roomInfo['status'] = 1;         // 房间状态为1；
            	   $game_start = 1;
            }else{
                    $game_start = 0;
            }

            yield $this->redis_pool->hset($room_id, 'roomInfo', serialize($roomInfo), 'userInfo', serialize($userInfo),'gameInfo',serialize($gameInfo));
             return [ 'is_user' => $is_user,'game_start' => $game_start, 'roomInfo' => $roomInfo,'userInfo'=>$userInfo];
    }
    public function likai($mid, $room_id, $roomInfo)
    {
       //查找自己的位置
         $k = array_search($mid,$roomInfo['weizhi']);
         if($k === false){
            return false;
         }
         //删除位置
         unset($roomInfo['weizhi'][$k]);
         $roomInfo['weizhi'] = array_values($roomInfo['weizhi']);

        //修改member表 清空房间号
        yield $this->mysql_pool->dbQueryBuilder
            ->update('gs_member')
            ->set('room_id', 0)
            ->where('id', $mid)
            ->coroutineSend();
        //删除用户信息
        unset($roomInfo['users'][$mid]);
        yield $this->redis_pool->hset($room_id, 'roomInfo', serialize($roomInfo));
        return $roomInfo;
    }
}