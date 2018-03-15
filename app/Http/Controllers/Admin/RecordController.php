<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agent_info;
use App\Models\Angent_record;
use App\Models\Mnum_info;
use App\Models\Record;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Input;
use App\Models\Set;
use Excel;

/**
 *记录控制器
 */
class RecordController extends Controller
{

    /*
     * 代理提现列表
    */
    public function index_draw()
    {
        /*获取用户提交搜索条件*/
        $input = Input::all();
        $where = [];
        $sn = empty($input['sn']) ? '' : trim($input['sn']);
        /*根据用户id搜索*/
        $member_id = empty($input['id']) ? '' : trim($input['id']);
        if($member_id){
            $where['mid'] =   $member_id;
        }
        /*提现状态*/
        $is_pay = empty($input['is_pay']) ? '' : trim($input['is_pay']);
        if($is_pay){
            $where['is_pay'] =  $is_pay;
        }
        /*提现方式*/
        $ti_type = empty($input['ti_type']) ? '' : trim($input['ti_type']);
        if($ti_type){
            $where['ti_type'] =  $ti_type;
        }

        /*根据创建时间*/
        $creat_time= empty($input['creat_time']) ? '' : trim($input['creat_time']);
        $not1 = true;
        if($creat_time){
            $tt = explode(' -- ',$creat_time);
            $startime=strtotime("{$tt[0]} 00:00:01");
            $endtime=strtotime("{$tt[1]} 23:59:59");
            $tt1 = [$startime,$endtime];
            $not1 = false;
        }else{
            $tt1 = ['',''];
        }

        /*根据完成时间*/
        $finish_time= empty($input['finish_time']) ? '' : trim($input['finish_time']);
        $not2 = true;
        if($finish_time){
            $tt = explode(' -- ',$finish_time);
            $startime=strtotime("{$tt[0]} 00:00:01");
            $endtime=strtotime("{$tt[1]} 23:59:59");
            $tt2 = [$startime,$endtime];
            $not2 = false;
        }else{
            $tt2 = ['',''];
        }

        $data = DB::table('angent_record')
            ->where($where)
            ->where('sn', 'like', "$sn%")
            ->whereBetween('creat_time',$tt1,'and',$not1)
            ->whereBetween('finish_time',$tt2,'and',$not2)
            ->orderBy('id', 'desc')
            ->paginate(10);
        $num = DB::table('angent_record')
            ->where($where)
            ->where('sn', 'like', "$sn%")
            ->whereBetween('creat_time',$tt1,'and',$not1)
            ->whereBetween('finish_time',$tt2,'and',$not2)
            ->select(DB::raw("count(sn) as num,sum(num) as nums,sum(rate) as rate,sum(money) as money"))
            ->first();
        if(isset($input['excel']) && $input['excel'] == 1){
            $lis[] = array(
                '提现单号',
                '代理ID',
                '提现金额',
                '手续费',
                '实际到账金额',
                '提现姓名',
                '提现方式',
                '提现账户',
                '提现状态',
                '申请时间',
                '处理时间',
                '驳回原因'
            );
            $export_data = DB::table('angent_record')
                ->where($where)
                ->where('sn', 'like', "$sn%")
                ->whereBetween('creat_time',$tt1,'and',$not1)
                ->whereBetween('finish_time',$tt2,'and',$not2)
                ->orderBy('id', 'desc')
                ->get();
            foreach ($export_data as  $v)
            {
                if($v->ti_type == 1){
                    $ti_type = '支付宝';
                }elseif($v->ti_type == 2){
                    $ti_type = '银行卡';
                    }
                if($v->is_pay == 1){
                    $is_pay = '未转';
                }elseif($v->is_pay == 2){
                    $is_pay = '已转';
                }elseif($v->is_pay == 3){
                    $is_pay = '驳回';
                }
                if($v->finish_time){
                   $finish_time =  date('Y-m-d H:i:s',$v->finish_time);
                   }else{
                    $finish_time = '无处理';
                }
                $lis[] = array(
                    $v->sn,
                    $v->mid,
                    $v->num,
                    $v->rate,
                    $v->money,
                    $v->realname,
                    $ti_type,
                    $v->number,
                    $is_pay,
                    date('Y-m-d H:i:s',$v->creat_time),
                    $finish_time,
                    $v->describe
                );
            }
            $name='提现记录'.date('Ymdhis',time());
            Excel::create($name,function($excel) use ($lis){
                $excel->sheet('lis', function($sheet) use ($lis){
                    $sheet->rows($lis);
                });
            })->export('xls');

        }

        return view('admin/record/index_draw', compact('data', 'input','num'));
    }

    /**
     * 充值记录
     */
    public function  index_recharge(){
        /*获取用户提交搜索条件*/
        $input = Input::all();
        $where = [];
        /*根据用户id搜索*/
        $sn = empty($input['sn']) ? '' : trim($input['sn']);

        /*根据用户id搜索*/
        $member_id = empty($input['id']) ? '' : trim($input['id']);
        if($member_id){
            $where['mid'] =   $member_id;
        }
        /*一级代理*/
        $agent1 = empty($input['agent1']) ? '' : trim($input['agent1']);
        if($agent1){
            $where['agent1'] =  $agent1;
        }

        /*二级代理*/
        $agent2 = empty($input['agent2']) ? '' : trim($input['agent2']);
        if($agent2){
            $where['agent2'] =   $agent2;
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
        $data = DB::table('recharge')
            ->where('sn', 'like', "$sn%")
            ->where($where)
            ->whereBetween('time',$tt,'and',$not)
            ->orderBy('id', 'desc')
            ->paginate(15);
        $num = DB::table('recharge')
            ->where('sn', 'like', "$sn%")
            ->where($where)
            ->whereBetween('time',$tt,'and',$not)
            ->select(DB::raw("count(mid) as num,sum(money) as balance,sum(balance1) as balance1,sum(balance2) as balance2"))
            ->first();
        if(isset($input['excel']) && $input['excel'] == 1){
            $lis[] = array(
                '充值单号',
                '玩家ID',
                '上一级ID',
                '上二级ID',
                '钻石数',
                '金额',
                '实付金额',
                '一级佣金',
                '二级佣金',
                '充值时间'
            );
            $export_data = DB::table('recharge')
                ->where('sn', 'like', "$sn%")
                ->where($where)
                ->whereBetween('time',$tt,'and',$not)
                ->orderBy('id', 'desc')
                ->get();
            foreach ($export_data as  $v)
            {
                $lis[] = array(
                    '充值单号'=>$v->sn,
                    '玩家ID'=>$v->mid,
                    '上一级ID'=>$v->agent1,
                    '上二级ID'=>$v->agent2,
                    '钻石数'=>$v->num,
                    '金额'=>$v->balance,
                    '实付金额'=>$v->money,
                    '一级佣金'=>$v->balance1,
                    '二级佣金'=>$v->balance2,
                    '充值时间'=>date('Y-m-d H:i:s',$v->time)
                );
            }
            $name='充值记录'.date('Ymdhis',time());
            Excel::create($name,function($excel) use ($lis){
                $excel->sheet('lis', function($sheet) use ($lis){
                    $sheet->rows($lis);
                });
            })->export('xls');
        }
        return view('admin/record/index_recharge', compact('data', 'input','num'));
    }

    /**
     * 提现操作
     */
    public  function  draw_true($sn,$status){

            $re = DB::table('angent_record')->where(['sn'=>$sn,'is_pay'=>1])->update(['is_pay'=>2,'finish_time'=>time()]);
            if($re){
                plog("处理提现申请 订单号：$sn");
                return success('操作成功');
            }
        return error('请稍后重试');
    }

    /**
     * 提现操作 拒绝
     */
    public  function  draw_false(){
        $data = Input::all();
        DB::beginTransaction();
        $agent= DB::table('angent_record')->where(['sn'=>$data['sn'],'is_pay'=>1])->select('num','mid')->first();
        $re1 = DB::table('angent_record')->where(['sn'=>$data['sn'],'is_pay'=>1])->update(['is_pay'=>3,'finish_time'=>time(),'describe'=>$data['describe']]);
        $re2 =DB::table('agent')->where('mid',$agent->mid)->increment('balance',$agent->num);
        $re3 = DB::table('angent_info')
               ->insert([
                   'sn'=>$data['sn'],
                   'mid'=>$agent->mid,
                   'num'=>$agent->num,
                   'title'=>'提现驳回',
                   'type'=>2
               ]);
        if($re1 && $re2 && $re3){
            DB::commit();
            plog("驳回提现申请 订单号：{$data['sn']}");
            return 1;
        }else{
            DB::rollBack();
            return 0;
        }
    }

    /**
     * 玩家房卡记录
     */
    public function member_info($mid){
        $time = Input::get('time');
        $not = true;
        if($time){
            $tt = explode(' -- ',$time);
            $startime="{$tt[0]} 00:00:01";
            $endtime="{$tt[1]} 23:59:59";
            $tt = [$startime,$endtime];
            $not = false;
        }else{
            $tt = ['',''];
        }
        $data = DB::table('mnum_info')
              ->whereBetween('time',$tt,'and',$not)
              ->where('mid',$mid)
              ->orderBy('time','desc')
              ->paginate(15);
        $num = DB::table('mnum_info')
              ->where('mid',$mid)
              ->whereBetween('time',$tt,'and',$not)
              ->sum('num');
        return view('admin/record/member_info', compact('data', 'time','mid','num'));
    }

    /**
     * 代理佣金记录
     */
    public function agent_info($mid){
        $time = Input::get('time');
        $not = true;
        if($time){
            $tt = explode(' -- ',$time);
            $startime="{$tt[0]} 00:00:01";
            $endtime="{$tt[1]} 23:59:59";
            $tt = [$startime,$endtime];
            $not = false;
        }else{
            $tt = ['',''];
        }
        $data = DB::table('angent_info')
            ->whereBetween('time',$tt,'and',$not)
            ->where('mid',$mid)
            ->orderBy('time','desc')
            ->paginate(15);
        $num = DB::table('angent_info')
            ->where('mid',$mid)
            ->whereBetween('time',$tt,'and',$not)
            ->sum('num');
        return view('admin/record/agent_info', compact('data', 'time','mid','num'));
    }

    /**
     * 后台充值记录
     */
    public function index_hou(){
        $time = Input::get('time');
        $not = true;
        if($time){
            $tt = explode(' -- ',$time);
            $startime="{$tt[0]} 00:00:01";
            $endtime="{$tt[1]} 23:59:59";
            $tt = [$startime,$endtime];
            $not = false;
        }else{
            $tt = ['',''];
        }
        $data = DB::table('mnum_info')
            ->whereBetween('time',$tt,'and',$not)
            ->where('type',2)
            ->orderBy('time','desc')
            ->paginate(15);
        $num = DB::table('mnum_info')
            ->where('type',2)
            ->whereBetween('time',$tt,'and',$not)
            ->sum('num');
        return view('admin/record/index_hou', compact('data', 'time','num'));
    }

}
