<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Member;
use Dotenv\Validator;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Input;

class AgentController extends Controller
{
    //代理列表
    public function index()
    {


        /*获取用户提交搜索条件*/
        $input = Input::all();
        $where = [];
        /*根据用户昵称搜索*/
        $nickname = empty($input['nickname']) ? '' : trim($input['nickname']);
        /*根据用户id搜索*/
        $member_id = empty($input['id']) ? '' : trim($input['id']);
        if($member_id){
            $where['a.mid'] =   $member_id;
        }
        //手机号
        $phone = empty($input['phone']) ? '' : trim($input['phone']);
        if($phone){
            $where['m.phone'] =   $phone;
        }
        /*根据创建时间*/
        $time= empty($input['time']) ? '' : trim($input['time']);
        $not = true;
        if($time){
            $tt = explode(' -- ',$time);
            $startime=strtotime("{$tt[0]} 00:00:01");
            $endtime=strtotime("{$tt[1]} 23:59:59");
            $tt = [$startime,$endtime];
            $not = false;
        }else{
            $tt = ['',''];
        }
        $members = DB::table('agent as a')
            ->leftJoin('member as m','a.mid','=','m.id')
            ->where('m.nickname', 'like', "$nickname%")
            ->where($where)
            ->select('a.*','m.nickname','m.headimgurl','m.phone')
            ->whereBetween('a.time',$tt,'and',$not)
            ->orderBy('a.time', 'desc')
            ->paginate(15);

        foreach ($members as &$v){
            $v->x_num = DB::table('member')->where('pid',$v->mid)->count('id');
            $v->balance1 = DB::table('recharge')->where('agent1',$v->mid)->sum('balance1');
            $v->balance2 = DB::table('recharge')->where('agent2',$v->mid)->sum('balance2');
            $v->ti_money = DB::table('angent_record')->where('mid',$v->mid)->where('is_pay',2)->sum('num');
        }

        $num= DB::table('agent as a')
            ->leftJoin('member as m','a.mid','=','m.id')
            ->where('m.nickname', 'like', "$nickname%")
            ->where($where)
            ->whereBetween('a.time',$tt,'and',$not)
            ->select(DB::raw("count('a.mid') as num,sum('a.balance') as sum"))
            ->first();

        return view('admin/agent/index', compact('members', 'input','num'));
    }

    //添加代理
    public function create($mid){
        $phone = DB::table('member')->where('id',$mid)->value('phone');
        if(!$phone){
            return error('请先绑定手机号');
        }
        DB::beginTransaction();
        $re1 = DB::table('member')->where('id',$mid)->update(['is_agency'=>1]);
        $salts = substr(uniqid(), -6);
        $password = md5(md5('88888888'.$salts));
        $re2 = DB::table('agent')->insert([
            'mid'=>$mid,
            'password'=>$password,
            'salts'=>$salts,
            'time'=>time()
        ]);
        if($re1 && $re2){
            DB::commit();
            plog('添加代理 ID:'.$mid);
            return success('添加代理成功！','admin/agent/index');
        }else{
            DB::rollBack();
            return error('请稍后再试！');
        }
    }

    //修改代理状态
    public function edit_status($mid,$status){
        DB::table('agent')->where('mid',$mid)->update(['status' => $status]);
        if($status == 1){
            plog('解除代理黑名单 ID:'.$mid);
        }else{
            plog('设置代理为黑名单 ID:'.$mid);
        }
        return success('状态修改成功');
    }

    //删除代理
    public function del($mid){
        DB::beginTransaction();
        $re1 = DB::table('agent')->where('mid',$mid)->delete();
        DB::table('member')->where('id',$mid)->update(['is_agency'=>0]);
        DB::table('member')->where('pid',$mid)->update(['pid'=>0]);
        if($re1){
            DB::commit();
            plog('删除代理 ID:'.$mid);
            return success('删除代理成功！');
        }else{
            DB::rollBack();
            return error('请稍后再试！');
        }

    }

    //重置密码
    public function reset($mid){
        $salts = DB::table('agent')->where('mid',$mid)->value('salts');
        if($salts){
            $password = md5(md5('88888888'.$salts));
            $re = DB::table('agent')->where('mid',$mid)->update(['password'=>$password]);
            if($re){
                return success('重置密码成功！默认密码为8个8！');
            }else{
                return error('请稍后重试');
            }
        }else{
            return error('代理id不存在');
        }
    }
}
