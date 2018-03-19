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
    public function jinru($mid,$room_id,$roominfo)
    {
    
    	 if (in_array($mid, $roominfo['weizhi']) && isset($roominfo['weizhi'])){
    	 	return false;
    	 }else{ 
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
           		return false; 
           }
           $member = $member['result'][0];
           //新玩家加入weihzi
          		 $roominfo['weizhi'][] = $mid;
                 $userinfo['users'][$mid] = [
                'id' => $mid,
                'headimgurl' => $member['headimgurl'],
                'nickname' => $member['nickname'],
                'num' => $member['num'],
                'ip' => $member['ip'],
                'sex'=>$member['sex'],
               
            ];
              $gameinfo['now'] = 0;//存该谁出牌 用户id
              $gameinfo['users'][$mid] = [
                'id'=>$mid,
                'shoupai' =>[],
                'dachu'=>[],
                'fenshu'=>1000
              ];
           yield $this->mysql_pool->dbQueryBuilder
                ->update('gs_member')
                ->set('room_id', $room_id)
                ->where('id', $mid)
                ->coroutineSend();
            yield $this->redis_pool->getCoroutine()->hset('uids_'.$room_id,$mid,1);
             // yield $this->redis_pool->getCoroutine()->hset($room_id,'userInfo',serialize($userInfo),'gameInfo',serialize($gameinfo));
    	 }
    	 
            if(count($userinfo['users']) ==  $roominfo['guize']['renshu']){
            	  yield $this->mysql_pool->dbQueryBuilder
                ->update('gs_rooms')
                ->set('status',1)
                ->where('room_id',$room_id)
                ->coroutineSend();
            	$game_start = 1;
            }else{
            	$game_start = 0;
            }

            yield $this->redis_pool->hset($room_id, 'roomInfo', serialize($roominfo), 'userInfo', serialize($userinfo),'gameInfo',serialize($gameinfo));
             return [ 'game_start' => $game_start, 'roomInfo' => $roominfo,'userInfo'=>$userinfo];
    }
}