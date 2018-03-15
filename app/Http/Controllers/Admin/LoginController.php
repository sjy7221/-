<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Admin;

class LoginController extends Controller
{
    public function index(Request $request){
       if($request->session()->has('admin_id')){
           return redirect('admin');
       }else{
           return view('admin.login');
        }
    }

    //登入
    public function login(Request $request){

        $this->validate($request,[
            'name' => 'required',
            'password' => 'required',
             'code' => 'required'
        ],[
            'name.required'=>'请填写用户名！',
             'password.required'=>'请填写密码！',
             'code.required'=>'请填写验证码！'
        ]);
        if($request->input('code') != $request->session()->get('admin_verify')){
             return back()->withErrors('验证码不正确!');
        }

        if($re = Admin::isHave($request->input('name'))){

            if($re->status==1){
                return back()->withErrors('该用户被拉黑,请联系管理员!');
            }
        }else{
            return back()->withErrors('该用户不存在!');
        }

            if($admin = Admin::login($request->input('name'),$request->input('password'))){
                $request->session()->put('admin_id',$admin['id']);
                $request->session()->put('name',$admin['name']);
                $ip = $_SERVER["REMOTE_ADDR"];
                plog($admin['name'].'登入成功 登入IP:'.$ip.' 登入地点:'.getCity());
                return success("欢迎回来！{$admin['name']}",'admin');
            }else{
                return back()->withErrors('用户名或密码不正确!');
           }
    }

    public function outLogin(Request $request){
        plog($request->session()->get('name').'退出登入');
        Admin::outLogin($request->session()->get('admin_id'));
        $request->session()->flush();
        return redirect('admin/login');
    }

    /**
     * 生成验证码
     */
    public function verify(Request $request){
        $img=imagecreatetruecolor(84, 32);
        $imgcolor=imagecolorallocate($img,mt_rand(100,200), mt_rand(100,200), mt_rand(100,200));
        imagefill($img, 0, 0, $imgcolor);
        $pixelcolor=imagecolorallocate($img, mt_rand(100,200), mt_rand(100,200), mt_rand(100,200));
        for($i=0;$i<200;$i++){
            imagesetpixel($img, mt_rand(0,124)-1, mt_rand(0,32)-1, $pixelcolor);
        }
        $linecolor=imagecolorallocate($img,mt_rand(150,250), mt_rand(150,250), mt_rand(150,250));
        for($j=0;$j<5;$j++){
            imageline($img, mt_rand(0,124), mt_rand(0,32), mt_rand(0,124), mt_rand(0,32),$linecolor);
        }
        $yzmcolor=imagecolorallocate($img,mt_rand(50,100),mt_rand(50,100),mt_rand(50,100));
        $yz='12345679';
        $Verify="";
        for($i=0;$i<4;$i++){
            $yzm=mt_rand(0,strlen($yz)-1);
            $Verify.=$yzm;
        }
        $request->session()->put('admin_verify', $Verify);
        imagettftext($img,18,3, 15, 23,$yzmcolor,'layui/font/1.ttf', $Verify);
        header("Cache-Control: no-cache, must-revalidate");
        header('Content-Type: image/png');
        imagepng($img);
    }
}
