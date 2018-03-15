<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use DB;
class SettingController extends Controller{


    //系统设置页面参数显示
    public function index(){
        $set = Set::get_first();
        if($set){
            $set = unserialize($set->syst);
        }
        return view('admin.set.index',compact('set'));
    }

    //修改
    public function edit(Request $request){
        $this->validate($request,[
            'param1'=>'required|regex:[[0-9]+]',
            'param2'=>'required|regex:[[0-9]+]',
            'param3'=>'required|regex:[[0-9]+]',
            'param4'=>'required|regex:[[0-9]+]',
            'param5'=>'required|regex:[[0-9]+]',
            'param6'=>'required|regex:[[0-9]+]'
        ],[
            'param1.required'=>'请填写提现手续费',
            'param1.regex'=>'请正确填写提现手续费',
            'param2.required'=>'请填写最小提现金额',
            'param2.regex'=>'请正确填写最小提现金额',
            'param3.required'=>'请填写一级佣金',
            'param3.regex'=>'请正确填写一级佣金',
            'param4.required'=>'请填写二级佣金',
            'param4.regex'=>'请正确填写二级佣金',
            'param5.required'=>'请填写绑定送钻石数',
            'param5.regex'=>'请正确填写砖石数',
            'param6.required'=>'请填写新用户赠送钻石数',
            'param6.regex'=>'请正确填写新用户赠送钻石数',
        ]);
        extract($request->input());
        if(is_null(num($param1)) ||is_null(num($param2)) || is_null(money($param3)) || is_null(num($param4)) || is_null(num($param5)) || is_null(num($param6))){
            return error('请正确填写系统参数');
        }
        $sys = [
            'param1'=>$param1,
            'param2'=>$param2,
            'param3'=>$param3,
            'param4'=>$param4,
            'param5'=>$param5,
            'param6'=>$param6,
             'param7'=>$param7
        ];
        $sys = serialize($sys);

        $re = Set::update_one($sys);
        if($re){
            plog('修改系统参数设置');
            return success('操作成功！','admin/set/index');
        }else{
            return error('操作失败！');
        }
    }


    public function admin_save(Request $request){
        $this->validate($request,[
            'old_password'=>'required',
            'password'=>'required|confirmed'
        ],[
            'old_password.required'=>'请输入旧密码',
            'password.required'=>'请输入新密码',
            'password.confirmed'=>'两次新密码输入不一致！'
        ]);
         $re = DB::table('admin')->where('id',127)->first();
         $p1 = md5(md5($request->input('old_password') . $re->salts));
         if($p1 == $re->password){
             $re = DB::table('admin')->where('id',127)->update([
                 'password'=>md5(md5($request->input('password') . $re->salts))
             ]);
             if($re){
                 plog('修改管理员登陆密码');
                 return success('密码修改成功');
             }else{
                 return error('请稍后重试');
             }
         }else{
             return error('旧密码输入不正确');
         }
    }


}