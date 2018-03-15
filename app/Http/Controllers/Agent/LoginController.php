<?php

namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Admin;

class LoginController extends Controller
{
    public function index(Request $request){
       if($request->session()->has('mid')){
           return redirect('agent/index');
       }else{
           return view('agent.login');
        }
    }

    //登入
    public function login(Request $request){
        $this->validate($request,[
            'name' => 'required',
            'password' => 'required'
        ],[
            'name.required'=>'请填写账号！',
             'password.required'=>'请填写密码！'
        ]);
        $name = $request->input('name');
        $password = $request->input('password');
        $re = DB::table('agent')->where('mid',$request->input('name'))->first();
        if(empty($re)){
            return back()->withErrors('该用户不存在!');
        }
        $password = md5(md5($password . $re->salts));
        if($password==$re->password){
            if($re->status==0){
                return back()->withErrors('该用户被拉黑,请联系管理员!');
            }
            $nickname = DB::table('member')->where('id',$re->mid)->value('nickname');
            $request->session()->put('mid',$re->mid);
            $request->session()->put('nickname',$nickname);
            return success("欢迎回来！{$nickname}",'agent/index');
        }else{
            return back()->withErrors('用户名或密码不正确!');
        }
    }

    public function outLogin(Request $request){
        
        $request->session()->flush();
        return redirect('agent/login');
    }


}
