<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Member;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class AdminController extends Controller
{

    public function index(){
        $input = Input::all();
        $all = Admin::get_index($input);
        $count = Admin::where('id','<>',127)->count('id');
        return view('admin.admin.index',compact('all','count','input'));
    }

    //后台用户添加页面
    public function create($id){
        $phone = Member::getFirst($id);
        if($phone['phone'] == '0'){
            return error('请先联系该用户绑定手机号！！');
        }
        $res = Admin::where('name',$phone['phone'])->first();
        if($res){
            return error('该用户已是代理，请勿重复设置！');
        }else{
            $role_data = Role::getAll();
            return view('admin.admin.create',compact('role_data','phone'));
        }
    }
    //后台用户添加入库
    public function store(Request $request){
        $this->validate($request,[
            'name' => 'required',
            'password'=>'required|confirmed',
            'realname'=>'required'
        ],[
            'name.required'=>'请填写手机号！',
            'password.required'=>'请填写密码！',
            'password.confirmed'=>'两次密码输入不一致！',
            'realname.required'=>'请输入真实姓名'
        ]);
        if(!is_phoneNumber($request->input('name'))) return error('手机号格式错误！！');
        if(!Admin::isHave($request->input('name'))){
            DB::beginTransaction();
            $id=Admin::createAdmin($request->input());
            $re = Member::where('phone',$request->input('name'))->update(['is_agency'=>1]);
            if($id && $re){
                DB::commit();
                plog('添加代理 ID:'.$id);
                return success('代理添加成功！','admin/admin');
            }else{
                DB::rollBack();
                return error('代理添加失败！');
            }
        }else{
            return error('该手机号已存在！');
        }
    }
    //修改页面显示
    public function edit($id){
         $data = Admin::getFirst($id);
         $role_data = Role::getAll();
        return view('admin.admin.edit',compact('data','role_data'));
    }
    //修改入库
    public function save(Request $request){
        $this->validate($request,[
            'password'=>'confirmed'
        ],[
            'password.confirmed'=>'两次新密码输入不一致！'
        ]);
        if(Admin::editAdmin($request->input())){
            plog('修改管理员信息 ID:'.$request->input('id'));
            return success('管理员信息修改成功！','admin/admin');
        }else{
            return error('管理员信息修改成功！');
        }
    }

    //拉黑和解拉黑
    public  function black(Request $request,$id){
        DB::beginTransaction();
        if(Admin::blackAdmin($id,$request->get('status'))){
             if($request->get('status') == 1) plog('拉黑管理员 ID：'.$id);
             else plog('解除管理员黑名单  ID：'.$id);
             DB::commit();
            return success('操作成功！','admin/admin');
        }else{
            DB::rollBack();
            return error('操作失败！');
        }
    }
    //前台点击修改密码
    public function editPassword(Request $request){
        if($request->input()){
            $this->validate($request,[
                'old_password'=>'required',
                'password'=>'required|confirmed'
            ],[
                'old_password.required'=>'请输入旧密码！',
                'password.required'=>'请输入新密码！',
                'password.confirmed'=>'两次新密码输入不一致！'
            ]);
            if($request->input('old_password_confirmation') !== md5(md5($request->input('old_password').$request->input('salts')))){
                return error('旧密码输入不正确！');
            }
            if(Admin::editPassword($request->input())){
                plog('后台用户修改密码 ID:'.$request->input('id'));
                return success('密码修改成功！','admin');
            }else{
                return error('密码修改失败！');
            }
        }else{
            $data = Admin::getFirst($request->session()->get('admin_id'));
            return view('admin.admin.admin',compact('data'));
        }

    }
}
