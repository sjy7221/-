<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Set extends Model{
    protected $table = 'settings';
    public $timestamps = false;

    public static function get_first(){
        return static::where('id',1)->first();
    }

    //修改入库
    public static function update_one(string $sys){
        $re = static::where('id',1)
            ->update([
                'syst'=>$sys
            ]);
        return $re;
    }
}