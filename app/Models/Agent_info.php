<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent_info extends Model{
    protected $table = 'angent_info';
    public $timestamps = false;

    //插入一条数据
    public static function insert_one(array $arr){
        return static::insert($arr);
    }
    //数据显示
    public static function get_index($id){
        return static::where('aid',$id)->paginate(15);
    }
}