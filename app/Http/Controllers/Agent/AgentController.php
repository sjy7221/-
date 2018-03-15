<?php

namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use App\Models\Admin;
use Symfony\Component\Console\Helper\Table;
class AgentController extends Controller
{

    public function  __construct(){
        $mid = session('mid');
        $balance = DB::table('agent')->where('mid',$mid)->value('balance');
        view()->share('balance', $balance);
   }

    /**
     * 代理首页
     */
    public function index(){
        return view('agent.index');
    }

    /**
     * 玩家列表
     */
    public function player(){
        $input = Input::all();
        $mid = session('mid');
        $where =['pid'=>$mid];
        if(!empty($input['id'])){
            $where['id'] = $input['id'];
        }
        if(!empty($input['is_agent'])){
            $where['is_agency'] = 1;
        }
        $data = DB::table('member')
            ->select('id','nickname','num','create_time')
            ->where($where)
            ->orderBy('create_time', 'desc')
            ->paginate(10);
        $mid= session('mid');
        $num = DB::table('member')->where('id',$mid)->value('num');
        $set = DB::table('settings')->where('id',1)->first();
        $set = $set->syst;
        $set = unserialize($set);
        $set = $set['param7'];
        return view('agent/player', compact('data', 'input','num','set'));
    }

    /**
     * 业绩查询
     */
    public function cashback(){
        $mid= session('mid');
        $data = DB::table('angent_info')
            ->where('mid',$mid)
            ->whereIn('type',[3,4])
            ->select(DB::Raw('sum(b1) as b1,sum(b2) as b2,month'))
            ->groupBy('month')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('agent/cashbackmonth', compact('data'));
    }

    /**
     * 充值统计
     */
    public function orderstatis(){
        $mid= session('mid');
        $input = Input::all();
        $arr=['pid'=>$mid];
        if(!empty($input['mid'])) {
            $arr['id']= $input['mid'];
        }
        $data = DB::table('member')
            ->select('id as mid','nickname','num','create_time')
            ->where($arr)
            ->orderBy('create_time', 'desc')
            ->paginate(10);
        foreach ($data as &$v){
            $v->num = DB::table('recharge')
                ->where('mid',$v->mid)
                ->sum('money');
        }
        return view('agent/orderstatis', compact('data'));
    }

    /**
     * 提现管理
     */
    public function applylist(){
        $id = session('mid');
        $data = DB::table('angent_record')
            ->where('mid',$id)
            ->select('creat_time','money','is_pay','describe')
            ->orderBy('creat_time', 'desc')
            ->paginate(10);

        return view('agent/applywithdraw', compact('data'));
    }

    /**
     * 提现
     */
    public function apply(){
        $mid= session('mid');
        $input = Input::all();
        $money = $input['money'];
        $money = num($money);
        $set = DB::table('settings')->where('id',1)->first();
        $set = $set->syst;
        $set = unserialize($set);
        if ($money < $set['param2']) return error('最少提现金额为' . $set['param2'] . ',请输入大于' . $set['param2'] . '的数');
        if ($money % 100 != 0) return error('请输入100的倍数');
        $agent = DB::table('agent')->where('mid',$mid)->first();
        if ($agent->balance < $money) return error('佣金不足，无法提现');
        $realname = $agent->realname;
        $ti_type = $agent->ti_type ;
        $number = $agent->number;
        if(empty($realname) || empty($ti_type) || empty($number)){
            return error('请到个人资料页面完善提现信息（真实姓名/提现方式/提现账号）');
        }
        $sn = 'TX' . date('YmdHis') . mt_rand(1000, 9999);
        //数据插入
        $data = [
            'sn' => $sn,
            'mid' => $mid,
            'num' => $money,
            'rate' => $set['param1'],
            'money' => $money,
            'realname' => $realname,
            'ti_type' => $ti_type,
            'number' => $number,
            'creat_time' => time()
        ];
        DB::beginTransaction();
        //1.生成提现订单
        $re1 = DB::table('angent_record')->insert($data);
        //2.扣钱
        $res2 = DB::table('agent')->where('mid', $mid)->decrement('balance',$money);
        //3.生成记录
        $msg = [
            'sn' => $sn,
            'mid' => $mid,
            'num' => $money * -1,
            'title' => '申请提现',
            'type' => '1'
        ];
        $res3 = DB::table('angent_info')->insert($msg);
        if ($re1 && $res2 && $res3) {
            DB::commit();
            return success('申请成功！请耐心等待处理', 'agent/applylist');
        } else {
            DB::rollBack();
            return error('申请失败，请联系管理员！');
        }
    }

    /**
     * 个人信息
     */
    public function userinfo(){
        $mid= session('mid');
        $data = DB::table('agent as a')
            ->leftJoin('member as m','a.mid','=','m.id')
            ->where('a.mid',$mid)
            ->select('a.mid','m.nickname','a.balance','a.realname','a.ti_type','a.number','m.num')
            ->first();
        return view('agent/userinfo', compact('data'));
    }

    /**
     * 修改密码
     */
    public function changepw(){
        $mid= session('mid');
        $data = DB::table('agent')
            ->where('mid',$mid)
            ->first();
        return view('agent/changepw', compact('data'));
    }
    /**
     * 修改密码
     */
    public function changepw_store(Request $request){

           if(!empty($request->input('password'))){
               if($request->input('old_password_confirmation') !== md5(md5($request->input('old_password').$request->input('salts')))){
                   return error('原密码输入不正确！');
               }
               $data['password'] = md5(md5($request->input('password') . $request->input('salts')));
           }
                 $data ['ti_type'] =$request->input('ti_type');
                 $data ['realname'] =$request->input('realname');
                 $data ['number'] =$request->input('number');

            $re = DB::table('agent')->where('id', $request->input('id'))->update($data);
            if($re){
                return success('信息修改成功！','agent/userinfo');
            }else{
                return error('密码修改失败！');
            }

    }


   /**
    * 给玩家充房卡
    */
   public  function chong(Request $request){
       $set = DB::table('settings')->where('id',1)->first();
       $set = $set->syst;
       $set = unserialize($set);
       if($set['param7']!= 1){
           return error('充值失败，请稍后重试');
       }
         $id= session('mid');
         $data = $request->input();
         $agent_num = DB::table('member')
                  ->where('id',$id)
                  ->value('num');
         if($agent_num > 0 && $agent_num >= $data['num']){
             if($data['num']<=0){
                 return error('请填写正确的房卡数！');
             }
             $re1 = DB::table('member')->where('id',$data['mid'])->increment('num',$data['num']);
             $re2 = DB::table('member')->where('id',$id)->decrement('num',$data['num']);
             $re3 = DB::table('mnum_info')->insert([
                 'mid'=>$data['mid'],
                 'num'=>$data['num'],
                 'title'=>'代理充值',
                 'type'=>4
             ]);
             $re4 = DB::table('mnum_info')->insert([
                 'mid'=>$id,
                 'num'=>$data['num'] * -1,
                 'title'=>'给玩家充值',
                 'type'=>4
             ]);
             if($re1 && $re2 && $re3 && $re4){
                 DB::commit();
                 return success('充值成功', 'agent/player');
             }else{
                 DB::rollBack();
                 return error('充值失败，请稍后重试');
             }

         }else{
             return error('您的房卡不足,请联系平台充值');
         }
   }



}
