<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model{
    protected $table = 'msg';
    public $timestamps = false;

    //根据id获取一条数据
    public static function getFirst(int $id){
        $data = static::where('id',$id)->first();
        return $data;
    }

    //修改信息
    public static function mod_func(array $input){
        extract($input);
        if(empty($id)) return false;
        if($id ==1 ){
            plog('修改游戏玩法');
        }elseif($id == 2){
            plog('修改公告');
        }elseif($id == 3){
            plog('修改通知');
        }
        $re = static::where('id',$id)
            ->update([
            'content'=>$content
        ]);
        return $re;
    }
}