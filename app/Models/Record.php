<?php
/**
 * Created by MJ.
 * User: MJ
 * Date: 2017/7/12
 * Time: 10:03
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = 'record';
    public $timestamps = false;

    //用户订单列表信息

    public static function get_index(array $input)
    {
        extract($input);
        $data = static::leftJoin('member', 'record.mid', '=', 'member.id')
            ->where('member.agency_phone', $fu, session('name'))//如果是代理，之恩能够查自己的订单，如果是管理员，能查所有的订单
            ->where($id)//根据用户id搜索
            ->whereIn('record.is_pay', $is_pay)//支付状态
            ->whereBetween('record.creat_time', $time)//创建时间
            ->where(function ($sql) use ($info) {
                $sql->where('record.sn', 'like', "$info%")//订单号
                ->orWhere('member.phone', 'like', "$info%")//用户手机号
                ->orWhere('member.nickname', 'like', "$info%");//用户昵称
            })
            ->where('member.agency_phone', 'like', "$agent_name%")//用户手机号
            ->select('record.*', 'member.nickname', 'member.phone')
            ->orderBy('record.creat_time', 'desc')
            ->paginate(15);
        return $data;
    }

    public static function store($data)
    {
        return static::insert($data);
    }

    public static function change_status($sn,$update)
    {
        return static::where('sn',$sn)->update($update);
    }

}