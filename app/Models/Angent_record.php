<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Angent_record extends Model{
    protected $table = 'angent_record';
    public $timestamps = false;

    public static function get_index(array $input){
        extract($input);
        $data = static::leftJoin('admin','angent_record.mid','=','admin.id')
            ->where('angent_record.mid',$fu,session('admin_id'))//如果是代理，之恩能够查自己的订单，如果是管理员，能查所有的订单
            ->where($id)//根据用户id搜索
            ->whereIn('angent_record.is_pay',$is_pay)//提现状态
            ->whereBetween('angent_record.creat_time',$time)//创建时间
            ->where(function($sql) use($info){
                $sql->where('angent_record.sn','like',"$info%")//订单号
                ->orWhere('angent_record.tbank_num','like',"$info%");//提现卡号
            })
            ->where('admin.name','like',"$name%")//代理账号
            ->select('angent_record.*','admin.name')
            ->orderBy('angent_record.creat_time','desc')
            ->paginate(15);
        return $data;
    }

    //根据id查询一条数据
    public static function get_first(int $id){
        return static::where('id',$id)->first();
    }

    //根据id修改这条记录的状态
    public static function update_one(array $param){
        extract($param);
        $re = static::where('id',$id)
            ->update([
                'is_pay'=> $state,
                'finish_time'=>time()
            ]);
        return $re;
    }

    //插入一条数据
    public static function insert_one(array $data){
        return static::insertGetId($data);
    }

}