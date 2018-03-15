<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table = 'goods';
    public $timestamps = false;

    //首页列表数据读取
    public static function get_index()
    {
        $data = static::orderBy('is_show','asc')->orderBy('sort', 'asc')->get();
        return $data;
    }

    //添加商品入库
    public static function insert_one(array $input)
    {
        extract($input);
        $id = static::insertGetId([
            'sort' => $sort,
            'num' => $num,
            'money' => $money,
            'create_time' => time(),
            'update_time' => time(),
            'is_show' => $is_show
        ]);
        return $id;
    }

    //获取所有的商品数
    public static function get_count()
    {
        return static::count('id');
    }

    //根据id获取一条数据
    public static function getFirst(int $id, $bool = false)
    {
        if ($bool) $data = static::where('id', $id)->sharedLock()->first();//上锁获取(然并卵，可以查，但是不能修改，造成脏读 还是要用队列，或者直接表锁死)
        else $data = static::where('id', $id)->first();
        return $data;
    }

    //根据id修改一条数据状态
    public static function update_one_status(int $id, int $status)
    {
        $re = static::where('id', $id)
            ->update([
                'is_show' => $status
            ]);
        return $re;
    }

    //根据id修改一条数据
    public static function update_one(array $input)
    {
        extract($input);
        $re = static::where('id', $id)
            ->update([
                'sort' => $sort,
                'num' => $num,
                'money' => $money,
                'is_show' => $is_show
            ]);
        return $re;
    }

}