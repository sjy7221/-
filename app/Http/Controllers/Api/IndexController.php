<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Route;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\ServerBag;


class IndexController extends Controller
{
    //redis
      public function test($room_id){
        Redis::del($room_id);
        for ($i=1;$i<=30;$i++){
            Redis::sadd($room_id,$i.'_'.rand(111,222));
        }
        $re = Redis::Smembers($room_id);
        dd($re);
    }

    public function all($room_id=0)
    {
        if(empty($room_id)){
            dd(Redis::keys('*'));
        }else{
           $re =  Redis::Hgetall($room_id);
           $a=[];
           if(isset($re['roomInfo']) && !empty($re['roomInfo'])){
               $a['roomInfo'] = unserialize($re['roomInfo']);
           }
            if(isset($re['gameInfo']) && !empty($re['gameInfo'])){
                $a['gameInfo'] = unserialize($re['gameInfo']);
            }
            if(isset($re['userInfo']) && !empty($re['userInfo'])){
                $a['userInfo'] = unserialize($re['userInfo']);
            }
            $aa  = Redis::Hgetall('uids_'.$room_id);
            if($aa){
                $a['users_status'] = $aa;
            }
            dd($a);
        }
    }

    public function del($room_id=0)
    {
        Redis::del($room_id);
        Redis::del('uids_'.$room_id);
        Redis::del('jx_'.$room_id);
        Redis::del('js_'.$room_id);
        Redis::del('logs_'.$room_id);
        DB::table('member')->where('room_id',$room_id)->delete();
        DB::table('rooms')->where('room_id',$room_id)->delete();
        echo '清除完成';
    }

    public function del_all(){
       dd(Redis::flushdb());
    }




    /**
     * 登入
     */


    /**
     *创建房间
     */
    public function create_pdk()
    {
     
        $mid = Input::get('mid');//用户ID
    
        $jushu = Input::get('jushu'); //局数 10/20？砖石1个、2
        $renshu = Input::get('renshu');//人数
        $suanfa = Input::get('suanfa');//31为黑桃3 102//红桃十 //1为显示0为不显示
        $suanfa = explode(',',$suanfa);
        
        $roo = DB::table('member')->where('id',$mid)->value('room_id');

        if($roo){
            return json_encode(['status' => 1, 'data' => $roo]);
        }
        //10局为1个砖石，20局为2个砖石
        if($jushu == 10){
            $fei =1;
        }else{
            $fei = 2;
        }
        //查询是否有房卡
        $member = DB::table('member')->where('id', $mid)->first();
        if ($member->num < $fei) {
            return json_encode(['status' => 0, 'msg' => '钻石不足，请充值!']);
        }


        //删除8分钟的空房间
        $rooms = DB::table('rooms')->where('status',0)->where('users',0)->get();
        foreach ($rooms as $v){
            $b = time()-strtotime($v->time);
            if($v->users==0  && $b>=480){
                Redis::del($v->room_id);
                DB::table('rooms')->where('rid',$v->rid)->delete();
                DB::table('rooms_user')->where('rid',$v->rid)->delete();
            }
        }

        $fang = $this->getNum();//生成房间号
       
        $roomInfo = [
            'guize' => [
                'room_id' => $fang,
                'renshu' => $renshu,
                'jushu' => $jushu,
                'fangfei' => $fei,
                'suanfa' => $suanfa,
                'gid'=>51 //游戏种类
            ],
            'fangzhu' => $mid,
            'nowjushu'=>'1',
            'status' => 0,//0代表未开始
            'users' => [],//
            'weizhi'=>[] //位置
                                //投票
        ];
        $userInfo = [];
        $re = Redis::hmset($fang, 'roomInfo', serialize($roomInfo), 'userInfo', serialize($userInfo));
        if ($re) {
            $rid =  DB::table('rooms')->insertGetId([
                'room_id' => $fang,
                'difen' => 0,
                'jushu' => $jushu,
                'users' => 0,
                'fangzhu' => $member->nickname,
                'f_id'=>$mid,
                'gid' => 51
            ]);
            DB::table('rooms_user')->insert([
                'rid'=>$rid,
                'mid' => $mid,
                'room_id' => $fang,
            ]);
            //1建房成功
            return json_encode(['status' => 1, 'data' => $fang]);
        } else {
            return json_encode(['status' => 0, 'msg' => '请稍后重试！']);
        }
    }


    /**
     * 进入房间
     */
    public function join()
    {
        $mid = Input::get('mid');
        $room_id = Input::get('room_id');
        //如果有房间 就连之前的
        $roo = DB::table('member')->where('id',$mid)->value('room_id');

        if($roo){
            $room_id = $roo;
            return json_encode(['status' => 1, 'room_id' => $room_id]);
        }
        //删除8分钟的空房间
        $rooms = DB::table('rooms')->where('status',0)->where('users',0)->get();
        foreach ($rooms as $v){
            $b = time()-strtotime($v->time);
            if($v->users==0  && $b>=480){
                Redis::del($v->room_id);
                DB::table('rooms')->where('room_id',$v->room_id)->delete();
                DB::table('rooms_user')->where('room_id',$v->room_id)->delete();
            }
        }
       //判断房间是否存在
        if (!Redis::exists($room_id)) {
            return json_encode(['status' => 0, 'msg' => '房间不存在！']);
        }
        //判断是否是重新进入房间
        $users = Redis::sort('fang_'.$room_id);
        if(in_array($mid,$users)){
            return json_encode(['status' => 1, 'room_id' => $room_id]);
        }
       $roominfo =  Redis::hget($room_id,'roomInfo');
     $renshu =   unserialize($roominfo);

        //判断房间人数
        var_dump($users);die;
        if(count($users)>= $renshu['guize']['renshu']){
            return json_encode(['status' => 0, 'msg' => '房间人数已满！']);
        }
      
   
        $room = DB::table('rooms')->where('room_id',$room_id)->where('status',0)->first();
        if ($room) {
            $rr = DB::table('rooms_user')->where(['mid'=>$mid,'rid'=>$room->rid])->first();
            if(!$rr){
                DB::table('rooms_user')->insert([
                    'rid'=>$room->rid,
                    'mid' => $mid,
                    'room_id' => $room_id
                ]);
            }
            return json_encode(['status' => 1, 'room_id' => $room_id]);
        } else {
            return json_encode(['status' => 0, 'msg' => '请稍后重试！']);
        }

    }

    /**
     * 游戏记录
     */
    /**
     *  房间号剔除重复
     */
    public function getNum()
    {
        while (true) {
            $num = rand(100000, 999999);
            if (Redis::exists($num)) {
                continue;
            } else {
                $re = DB::table('rooms')->where('room_id',$num)->first();
                if(!$re){
                    return $num;
                }else{
                    continue;
                }
            }
        }
    }

}
