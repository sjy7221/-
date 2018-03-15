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
    public function login()
    {
        $i = Input::all();
       if (empty($i['openid'])  || empty($i['nickname']) || empty($i['headimgurl']) || empty($i['version'])) {
            return json_encode(['status' => 0, 'msg' => json_encode($i)]);
        }
        $ip = $_SERVER["REMOTE_ADDR"];
        $member = DB::table('member')
            ->where('openid', $i['openid'])
            ->first();
        $token = md5(time());
        if ($member) {
            /* if($member->room_type>0 && $member->room_id>0 && $member->status>0){
                 return json_encode(['status'=>0,'msg'=>'无法登入,当前用户正在游戏!']);
             }*/
            if ($member->is_black == 1) {
                return json_encode(['status' => 0, 'msg' => '当前用户被拉黑，请联系管理员!']);
            }
            //保存微信头像
                $name = md5($i['openid']);
                $re  =  getImage($i['headimgurl'],'users',"$name.jpg",1);
                if($re['error']==0){
                    $headimgurl = 'http://'.$_SERVER['SERVER_NAME'].'/'.$re['save_path'];
                }else{
                    $headimgurl = 'http://'.$_SERVER['SERVER_NAME'].'/images/mr.jpg';
                }

            DB::table('member')
                ->where('id', $member->id)
                ->update([
                    'token' => $token,
                    'time' => time(),
                    'ip' => $ip,
                    'version' => $i['version'],
                    'status' => 1,
                    'headimgurl' => $headimgurl
                ]);
            $data = [
                'mid' => $member->id,
                'token' => $token,
                'room_id'=>$member->room_id
            ];
            return json_encode(['status' => 1, 'data' => $data]);
        } else {
            $headimgurl = $i['headimgurl'];
            if($headimgurl == '/0'){
                $headimgurl = 'http://'.$_SERVER['SERVER_NAME'].'/images/mr.jpg';
            }else{
                //保存微信头像
                $name = md5($i['openid']);
                $re  =  getImage($headimgurl,'users',"$name.jpg",1);
                if($re['error']==0){
                    $headimgurl = 'http://'.$_SERVER['SERVER_NAME'].'/'.$re['save_path'];
                }else{
                    $headimgurl = 'http://'.$_SERVER['SERVER_NAME'].'/images/mr.jpg';
                }
            }
            $set = DB::table('settings')->where('id',1)->value('syst');
            $set = unserialize($set);
            $num = $set['param6'];
            $mid = DB::table('member')
                ->insertGetId([
                    'openid' => $i['openid'],
                    'nickname' => $i['nickname'],
                    'headimgurl' => $headimgurl,
                    'ip' => $ip,
                    'time' => time(),
                    'create_time' => time(),
                    'version' => $i['version'],
                    'sex' => $i['sex'],
                    'token' => $token,
                    'status' => 1,
                    'num'=>$num
                ]);
            if ($mid) {
                if($num > 0 ){
                    DB::table('mnum_info')->insert([
                        'mid'=>$mid,
                        'num'=>$num,
                        'title'=>'新用户注册',
                        'type'=>1
                    ]);
                }

                $data = [
                    'mid' => $mid,
                    'token' => $token,
                    'room_id'=>0
                ];
                return json_encode(['status' => 1, 'data' => $data]);
            } else {
                return json_encode(['status' => 0, 'msg' => '请稍后重试!']);
            }
        }

    }

    /**
     * 进入大厅
     */
    public function dating()
    {
        $mid = Input::get('mid');
        $member = DB::table('member')
            ->where('id', $mid)
            ->select('ip','pid','is_agency','phone', 'num', 'room_id','headimgurl')
            ->first();
        $msg = DB::table('msg')
            ->where('id', 3)
            ->select('content')
            ->first();
        $set = DB::table('settings')->where('id',1)->value('syst');
        $set = unserialize($set);
        $num = $set['param5'];
        if ($member) {
            return json_encode(['status' => 1, 'data' => ['member'=>$member,'msg'=>$msg,'num'=>$num]]);
        } else {
            return json_encode(['status' => 0, 'msg' => '无法获取该用户信息！']);
        }
    }

    //获取消息
    public function getMsg(){
        $msg = DB::table('msg')
            ->where('id', 2)
            ->select('content')
            ->first();
        return json_encode(['status' => 1, 'data' => $msg]);
    }

    //获取玩法
    public function getWan(){

        $msg = DB::table('msg')
            ->where('id', 1)
            ->select('content')
            ->first();
        return json_encode(['status' => 1, 'data' => $msg]);
    }


    /**
     * 获取房间信息
     */
    public function getRoom(){
        $mid = Input::get('mid');
        $gid = 1;
        $rooms = DB::table('rooms_user as u')
            ->leftJoin('rooms as r', 'u.room_id', '=', 'r.room_id')
            ->where('u.mid', $mid)
            ->where('r.gid',1)
            ->where('r.status','<',2)
            ->select('r.room_id', 'r.difen', 'r.jushu', 'r.users')
            ->orderBy('r.time', 'desc')
            ->get();
        return json_encode(['status' => 1, 'data' => $rooms]);
    }

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
        var_dump($member);die;
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
               
                'jushu' => $jushu,
                'fangfei' => $fei,
                'suanfa' => $suanfa,
                'gid'=>51 //游戏种类
            ],
            'fangzhu' => $mid,
            'status' => 0,//0代表未开始
            'users' => []//玩家
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
        }else{
            echo 'cunzai';
        }
        //判断是否是重新进入房间
        $users = Redis::sort('fang_'.$room_id);
        if(in_array($mid,$users)){
            return json_encode(['status' => 1, 'room_id' => $room_id]);
        }
        //判断房间人数
        if(count($users)>=4){
            return json_encode(['status' => 0, 'msg' => '房间人数已满！']);
        }
        //判断砖石是足够
        $roomInfo = unserialize(Redis::hget($room_id, 'roomInfo'));

        //如果是玩家支付 判断一下砖石是否足够
        if($roomInfo['guize']['fangfei'] == 2){
            //查询是否有房卡
            $member = DB::table('member')->where('id', $mid)->first();
            if ($member->num <  $roomInfo['guize']['jushu']) {
                return json_encode(['status' => 0, 'msg' => '钻石不足，请充值!']);
            }
        }
        //如果开启了ip防作弊 判断ip是否一样
        if(in_array(2,$roomInfo['guize']['suanfa'])){
            $ip = $_SERVER["REMOTE_ADDR"];
            $re1 = DB::table('member')
                ->where(['ip'=>$ip,'room_id'=>$room_id])
                ->first();
            if($re1){
                return json_encode(['status' => 0, 'msg' => '无法进入,房间内有相同IP玩家!']);
            }
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
    public function logs()
    {
        $mid  = Input::get('mid');
        $gid  = 1;
        $data = DB::table('rooms_user as u')
            ->leftJoin('rooms as r','u.rid','=','r.rid')
            ->where('u.mid',$mid)
            ->where('r.gid',$gid)
            ->where('r.status',2)
            ->select('r.time','r.fangzhu','r.rid')
            ->orderBy('r.time','desc')
            ->paginate(20);
        foreach ($data as  &$v){
            $v->users = DB::table('rooms_user as u')
                ->leftJoin('member as m','u.mid','=','m.id')
                ->select('m.nickname','u.fen')
                ->where('u.rid',$v->rid)
                ->where('u.type',1)
                ->get();
        }
        return json_encode(['status'=>1,'data'=>$data]);
    }

    /**
     * 记录详情
     */
    public function logs_info()
    {
        $rid = Input::get('rid');
        $data = DB::table('logs_info')
            ->select('id as logs_id', 'time', 'users')
            ->where('rid',$rid)
            ->get();
        return json_encode(['status' => 1, 'data' => $data]);
    }

    /**
     * 回放
     */
    public function playback()
    {
        $logs_id = Input::get('logs_id');
        $data = DB::table('logs_info')
            ->select('info')
            ->where('id', $logs_id)
            ->get();
        return json_encode(['status' => 1, 'data' => $data]);
    }

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


    /**
     * 发送验证码
     */
    public  function send(){
        $i = Input::all();
        //表单验证
        $rules = array(                                     //定义验证规则
            'phone' => 'required|digits:11'
        );
        $message = array(                                   //定义错误提示信息
            'phone.required' => '手机号不能为空',
            'phone.digits' => '手机号格式不正确'
        );
        $validator = validator($i,$rules,$message);          //传递参数,进行验证
        if($validator->passes()) {
            $re1 = DB::table('member')->where('phone',$i['phone'])->first();
            if($re1){
                return json_encode(['status'=>0,'msg'=>'该手机号已经被绑定!'],JSON_UNESCAPED_UNICODE);
            }
            $re = send($i['phone']);
            if($re==1){
                return json_encode(['status'=>1,'msg'=>'验证码发送成功！'],JSON_UNESCAPED_UNICODE);
            }else{
                return json_encode(['status'=>0,'msg'=>'请稍后再试!'],JSON_UNESCAPED_UNICODE);
            }
        }else{
            return json_encode(['status'=>0,'msg'=>$validator->errors()->first()],JSON_UNESCAPED_UNICODE);
        }

    }

    /**
     * 绑定手机号
     */
    public  function bindPhone(){
        $i = Input::all();
        //表单验证
        $rules = array(                                     //定义验证规则
            'phone' => 'required|digits:11',
            'mid'=>'required',
            'code'=>'required'
        );
        $message = array(                                   //定义错误提示信息
            'phone.required' => '手机号不能为空',
            'phone.digits' =>   '手机号格式不正确',
            'mid.required' =>   '用户id不能为空',
            'code.required' =>  '验证码不能为空'
        );
        $validator = validator($i,$rules,$message);          //传递参数,进行验证
        if($validator->passes()) {
            $re = DB::table('verification')->where(['phone'=>$i['phone'],'code'=>$i['code']])->first();
            if(!$re){
                return json_encode(['status'=>0,'msg'=>'验证码不正确']);
            }
            $ree = DB::table('member')
                ->where('id',$i['mid'])
                ->update([
                    'phone'=>$i['phone']
                ]);
            if($ree){
                $set = DB::table('settings')->where('id',1)->value('syst');
                $set = unserialize($set);
                $num = $set['param5'];
                DB::table('member')->where('id',$i['mid'])->increment('num',$num);
                DB::table('mnum_info')->insert(['mid'=>$i['mid'],'num'=>$num,'title'=>'绑定奖励','type'=>1]);
                return json_encode(['status'=>1,'msg'=>'手机号绑定成功','num'=>$num]);
            }else{
                return json_encode(['status'=>0,'msg'=>'请稍后重试']);
            }

        }else{
            return json_encode(['status'=>0,'msg'=>$validator->errors()->first()]);
        }

    }

    /**
     * 绑定推荐码
     */
    public  function bindAgent(){
        $i = Input::all();
        //表单验证
        $rules = array(                                     //定义验证规则
            'pid' => 'required',
            'mid'=>'required'
        );
        $message = array(                                   //定义错误提示信息
            'pid.required' => '请填写推荐码',
            'mid.required' =>   '用户id不能为空'
        );
        $validator = validator($i,$rules,$message);          //传递参数,进行验证
        if($validator->passes()) {
            $re1 = DB::table('agent')->where('mid',$i['pid'])->first();
            if(!$re1){
                return json_encode(['status'=>0,'msg'=>'该推荐码不存在!'],JSON_UNESCAPED_UNICODE);
            }
            $ree = DB::table('member')
                ->where('id',$i['mid'])
                ->update([
                    'pid'=>$i['pid']
                ]);
            if($ree){
                return json_encode(['status'=>1,'msg'=>'推荐码绑定成功']);
            }else{
                return json_encode(['status'=>0,'msg'=>'请稍后重试']);
            }

        }else{
            return json_encode(['status'=>0,'msg'=>$validator->errors()->first()],JSON_UNESCAPED_UNICODE);
        }

    }

    /**
     * 意见反馈
     */
    public  function fankui(){
        $i = Input::all();
        //表单验证
        $rules = array(                                     //定义验证规则
            'phone' => 'required|digits:11',
            'content'=>'required'
        );
        $message = array(                                   //定义错误提示信息
            'phone.required' => '手机号不能为空',
            'phone.digits' =>   '手机号格式不正确',
            'content.required' => '评论内容不能为空'
        );
        $validator = validator($i,$rules,$message);          //传递参数,进行验证
        if($validator->passes()) {
           $re = DB::table('feedback')
                 ->insert([
                     'phone'=> $i['phone'],
                     'content'=>$i['content']
                 ]);
            if($re){
                return json_encode(['status'=>1,'msg'=>'感谢您的反馈,我们会尽快解决您反馈的问题！']);
            }else{
                return json_encode(['status'=>0,'msg'=>'请稍后重试']);
            }
        }else{
            return json_encode(['status'=>0,'msg'=>$validator->errors()->first()]);
        }

    }

    /**
     * 商品信息
     */
    public  function goods(){
        $data = DB::table('goods')
               ->select('num','money','id as goods_id')
               ->where('is_show',0)
               ->orderBy('sort','asc')
               ->get();
        return json_encode(['status'=>1,'data'=>$data]);
    }

    /**
     * 支付完成
     */
    public function pay(){
        $data = Input::all();
        if(isset($data['amount']) && $data['amount'] > 0 ){
            $goods = DB::table('goods')->where('id',$data['product_id'])->first();
            if($goods){
                 DB::beginTransaction();
                 $re = $this->commission($data['game_user_id'],$data['amount'],$data['order_id']);
                 if($re){
                     $re1 = DB::table('member')->where('id',$data['game_user_id'])->increment('num',$goods->num);
                     $re2 = DB::table('mnum_info')
                            ->insert([
                             'sn'=>$data['order_id'],
                             'mid' => $data['game_user_id'],
                             'num' => $goods->num,
                             'type' => 3,
                             'title' => '线上充值'
                            ]);
                     $re3 = DB::table('recharge')->insert([
                             'sn'=>$data['order_id'],
                             'mid'=>$data['game_user_id'],
                             'agent1'=>$re['pid1'],
                             'agent2'=>$re['pid2'],
                             'num'=>$goods->num,
                             'balance'=>$goods->money,
                             'money'=>$data['amount'],
                             'balance1'=>$re['b1'],
                             'balance2'=>$re['b2'],
                              'time'=>time()
                     ]);

                     if($re1 && $re2 && $re3){
                             DB::commit();
                     }else {
                             DB::rollBack();
                     }
                 }
            }
        }
        echo "ok";
    }


    //分佣金
    public function commission($mid,$balance,$sn){
        $set = DB::table('settings')->where('id',1)->value('syst');
        $set = unserialize($set);
        $p1 = $set['param3'];  //一级佣金
        $p2 = $set['param4'];  //二级佣金

        $pid1 = DB::table('member')->where('id',$mid)->value('pid');
        $pid2 = 0;
        $b2  = 0;
        //一级分佣
        $ree = false;
        $b1 =  round($balance*$p1/100,2);
        if($p1 > 0 && $balance>0 && $pid1 > 0 && $b1>0){
            DB::beginTransaction();
            $re1 = DB::table('agent')->where('mid',$pid1)->increment('balance',$b1);
            $re2 = DB::table('angent_info')->insert([
                'sn'=>$sn,
                'mid'=>$pid1,
                'num'=>$b1,
                'title'=>'一级佣金',
                'type'=>3,
                'b1'=>$b1,
                'month'=>date('Y-m',time())
                                    ]);
            if($re1 && $re2){
                $ree = true;
            }
            $pid2 = DB::table('member')->where('id',$pid1)->value('pid');
            //二级分佣
            if($ree && $p2 > 0 && $balance>0 && $pid2 > 0){
                $b2 = round($balance*$p2/100,2);
                if($b2>0){
                    $re3 = DB::table('agent')->where('mid',$pid2)->increment('balance',$b2);
                    $re4 = DB::table('angent_info')->insert([
                        'sn'=>$sn,
                        'mid'=>$pid2,
                        'num'=>$b2,
                        'title'=>'二级级佣金',
                        'type'=>3,
                        'b2'=>$b2,
                        'month'=>date('Y-m',time())
                    ]);
                    if($re3 && $re4){
                        $ree = true;
                    }
                }
            }
            if($ree){
                DB::commit();
                return ['pid1'=>$pid1,'pid2'=>$pid2,'b1'=>$b1,'b2'=>$b2];
            }else{
                DB::rollBack();
                return false;
            }
        }
        return ['pid1'=>0,'pid2'=>0,'b1'=>0,'b2'=>0] ;
    }

}
