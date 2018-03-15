<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use App\Models\Admin;
use Symfony\Component\Console\Helper\Table;
class IndexController extends Controller
{
    public function index(){

        $admin_id= session('admin_id');
        $data = Admin::getFirst($admin_id);
        //平台总玩家
        $num1  = DB::table('member')->count('id');
        //平台总代理
        $num2  = DB::table('agent')->count('mid');
        //代理总佣金
        $num3  = DB::table('agent')->sum('balance');
        //平台总充值金额
        $num4 = DB::table('recharge')->sum('money');
        //平台总提现金额
        $num5 = DB::table('angent_record')->where('is_pay',2)->sum('money');

        return view('admin.index',compact('data','num1','num2','num3','num4','num5'));
    }

    //获取每月用户统计
    public function getPeople(){
          $month = Input::get('month');
          $year = date('Y');

          $day = date('t', strtotime("$year-$month"));
         $days = [];   //天数
          $data1 = [];  //新增用户
          $data2 = [];  //新增代理
          $num1 = 0;    //用户总数
          $num2 = 0;     //代理总数
          $d = $year.'-'.$month.'-01';
          $t1 = strtotime("{$d} 00:00:01");
          $t2 = strtotime("{$d} 23:59:59");
          for ($i=1;$i<=$day;$i++){
                  //新增用户
                  $count1 = DB::table('member')->whereBetween('create_time',[$t1,$t2])->count('id');
                  $data1[] = $count1;
                  $num1 += $count1;

                  //新增代理
                  $count2 = DB::table('agent')->whereBetween('time',[$t1,$t2])->count('mid');
                  $data2[] = $count2;
                  $num2 += $count2;

                  $t1 += 86400;
                  $t2 += 86400;

                  $days[] = $i;
          }
         $data = [
             'days'=>$days,
             'members'=>$data1,
             'num1'=>$num1,
             'agent'=>$data2,
             'num2'=>$num2
         ];
        return $data;

    }

    //获取每月金额统计
    public function getMoney(){
        $month = Input::get('month');
        $year = date('Y');
        $day = date('t', strtotime("$year-$month"));
        $days = [];   //天数
        $data1 = [];  //
        $data2 = [];  //新增代理
        $num1 = 0;    //用户总数
        $num2 = 0;     //代理总数
        $d = $year.'-'.$month.'-01';
        $t1 = strtotime("{$d} 00:00:01");
        $t2 = strtotime("{$d} 23:59:59");
        for ($i=1;$i<=$day;$i++){
            //充值记录
            $count1 = DB::table('recharge')->whereBetween('time',[$t1,$t2])->sum('money');
            if(!$count1){
                $count1 = 0;
            }
            $data1[] = $count1;
            $num1 += $count1;

            //新增代理
            $count2 = DB::table('angent_record')->where('is_pay',2)->whereBetween('finish_time',[$t1,$t2])->sum('money');
            if(!$count2){
                $count2 = 0;
            }
            $data2[] = $count2;
            $num2 += $count2;

            $t1 += 86400;
            $t2 += 86400;

            $days[] = $i;
        }
        $data = [
            'days'=>$days,
            'recharge'=>$data1,
            'num1'=>$num1,
            'record'=>$data2,
            'num2'=>$num2
        ];
        return $data;

    }



}
