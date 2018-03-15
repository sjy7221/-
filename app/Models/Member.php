<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model{
    protected $table = 'member';
    public $timestamps = false;

    //首页列表数据读取
    public static function get_index(array $input){
        extract($input);
        $id = num(isset($id)?$id:'');
        $where_id = !($id>=1)?[]:['id'=>$id];
        $nickname = Trim_All(isset($nickname)?$nickname:'');
        $phone = Trim_All(isset($phone)?$phone:'');
        $agency_phone = Trim_All(isset($agency_phone)?$agency_phone:'');
        $is_agency = Trim_All(isset($is_agency)?$is_agency:'');
        $where = ($is_agency == '2')?[0]:(($is_agency == '1')?[1]:[1,0]);
        $fu = (session('admin_id') == 127)?'<>':'=';
        $members = static::where($where_id)
            ->where('agency_phone',$fu,session('name'))
            ->where('nickname','like',"$nickname%")
            ->where('phone','like',"$phone%")
            ->where('agency_phone','like',"$agency_phone%")
            ->whereIn('is_agency',$where)
            ->orderBy('id','desc')
            ->paginate(3);
        return $members;
    }
    //首页修改用户是否黑名单
    public static function change_balck($id,$state){
        $id = num($id);
        //修改黑名单状态
        $re = static::where('id',$id)
            ->update([
                'is_black'=>$state
            ]);
        return $re;
    }
    //拉黑代理时，修改绑定该代理玩家的状态
    public  static  function  clean_agency($phone){
        $re = static::where('agency_phone',$phone)
            ->update([
                'agency_phone'=>'0'
            ]);
        return $re;
    }

    public static function getFirst(int $id){
        $data = static::where('id',$id)->first()->toArray();
        return $data;
    }
}