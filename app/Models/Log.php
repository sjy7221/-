<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'log';
    public $timestamps=false;
    public static function getLog($input){
        $id = num(isset($input['id'])?$input['id']:'');
        $name = Trim_All(isset($input['name'])?$input['name']:'');
        $where = !($id>=1)?[]:['admin.id'=>$id];
        return static::leftJoin('admin','log.admin_id','=','admin.id')
                       ->where($where)
                       ->where('name','like',"$name%")
                       ->select('admin.name','log.*','admin.id as aid')
                       ->orderBy('log.id','desc')
                       ->paginate(15);

    }

}
