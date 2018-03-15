<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;

/**
 *用户控制器
 */
class MemberController extends Controller
{
    protected $table = 'member';

    /*用户列表*/
    public function index()
    {
        /*获取用户提交搜索条件*/
        $input = Input::all();
        $where = [];
        /*根据用户id搜索*/
        $member_id = empty($input['id']) ? '' : trim($input['id']);
        if($member_id){
            $where['id']=$member_id;
        }
        /*根据上级id搜索*/
        $pid= empty($input['pid']) ? '' : trim($input['pid']);
        if($pid){
            $where['pid']=$pid;
        }
        /*根据是否是代理搜索*/
        $is_agency= empty($input['is_agency']) ? '' : trim($input['is_agency']);
        if($is_agency){
            $where['is_agency']=$is_agency;
        }
        /*根据用户昵称搜索*/
        $nickname = empty($input['nickname']) ? '' : trim($input['nickname']);
        /*手机号*/
        $phone = empty($input['phone']) ? '' : trim($input['phone']);
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
        $members = DB::table($this->table)
          ->where('nickname', 'like', "$nickname%")
            ->where('phone', 'like', "$phone%")
            ->where($where)
            ->whereBetween('create_time', $tt,'and',$not)
            ->orderBy('id', 'desc')
            ->paginate(15);
        foreach ($members as &$v) {
            if ($v->sum > 0) {
                $v->wins = round($v->win / $v->sum, 4) * 100;
            } else {
                $v->wins = 0;
            }
        }
        $num = DB::table($this->table)
            ->where('nickname', 'like', "$nickname%")
            ->where('phone', 'like', "$phone%")
            ->where($where)
            ->whereBetween('create_time', $tt,'and',$not)
            ->select(DB::raw(" count(id) as num,sum(num)as sum"))
            ->first();

        return view('admin/member/index', compact('count', 'members', 'input','num'));
    }


    /*用户房卡充值显示*/
    public function edit($id)
    {
        $member = DB::table($this->table)
            ->where('id', $id)
            ->first();
        return view('admin/member/edit', compact('member'));
    }

    /*用户房卡数据更新*/
    public function update()
    {
        $mid= Input::get('id');
        $phone = Input::get('phone');
        $old_phone = Input::get('old_phone');
        $num = Input::get('num');

        if($num){
            if($num > 0){
                //增加被冲者的房卡数
                $flag11 = DB::table($this->table)->where('id', $mid)->increment('num', $num);
            }else if($num<0){
                $flag11 = DB::table($this->table)->where('id', $mid)->decrement('num', abs($num));
            }
            DB::table('mnum_info')
                ->insert([
                    'mid' => $mid,
                    'num' => $num,
                    'type' => 2,
                    'title' => '后台充值'
                ]);
            plog('充值房卡 ID:'.$mid." 钻石数:".$num);
        }
        if($phone && $old_phone != $phone ){
             $re = DB::table('member')->where('phone',$phone)->first();
             if($re){
                 return error('该手机号已存在');
             }
             DB::table('member')->where('id',$mid)->update(['phone'=>$phone]);
            plog('修改用户手机 ID:'.$mid." 原手机号:".$old_phone." 新手机号:".$phone);
        }
        return success('操作成功！', 'admin/member/index');

    }

    /*添加用户到黑名单*/
    public function pass($id)
    {
        $this->changeStatus($this->table, $id, 1);
        plog('添加用户到黑名单 ID:'.$id);
        return success('操作成功!');
    }

    /*解除用户到黑名单*/
    public function refuse($id)
    {
        $this->changeStatus($this->table, $id, 0);
        plog('解除用户黑名单 ID:'.$id);
        return success('操作成功!');
    }

    /*状态修改*/
    public function changeStatus($table, $id, $state)
    {
        $update = DB::table($table)->where('id', $id)->update(['is_black' => $state]);
    }



}
