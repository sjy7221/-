<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Input;
use App\Models\Notice;
class NoticeController extends Controller
{


    //玩法
    public function method(){
        $data = Notice::getFirst(1);
        return view('admin.notice.method',compact('data'));
    }

    //修改入库
    public  function  save(){
        $i = Input::all();
        $re = Notice::mod_func($i);
        if($re){
            return success('修改成功');
        }else{
            return error('修改失败');
        }
    }

    //公告管理
    public  function  notice(){
        $data = Notice::getFirst(2);
        return view('admin.notice.notice',compact('data'));
    }

    //通知
    public  function  inform(){
        $data = Notice::getFirst(3);
        return view('admin.notice.inform',compact('data'));
    }

    public function feedback(){
        $data = DB::table('feedback')->orderBy('time','desc')->paginate(15);

        return view('admin.notice.feedback',compact('data'));
    }



}