<?php

namespace App\Models;
use App\Models\Node;
use Illuminate\Database\Eloquent\Model;
use DB;

class Role extends Model
{
    protected $table = 'admin_role';
    public $timestamps=false;

    public static function  getAll(){
        $data = static::get()->toArray();
        foreach ($data as &$v){
            $v['jurisdiction'] = Node::getRoleNode($v['id']);
        }
       return $data;
    }


    public static function createRole($attributes){
        extract($attributes);
            $data = [
                'name'=>Trim_All($name),
            ];
           $role_id =static::insertGetId($data);
           foreach ($node_id as $v){
               $dd = [
                   'role_id'=>$role_id,
                   'node_id'=>num($v)
               ];
               DB::table('admin_role_node')->insert($dd);
           }
           return $role_id;
    }

    public static function getFirst(int $id)
    {
        $data = static::where('id',$id)->first()->toArray();
        $data['jurisdiction'] = Role_Node::getFirst($id);
        return $data;
    }

    public static function editRole($attributes){
        extract($attributes);
//        $data = [
//            'name'=>$name,
//        ];
//        static::where('id',$id)->update($data);
        DB::table('admin_role_node')->where('role_id',num($id))->delete();
        foreach ($node_id as $v){
            $dd = [
                'role_id'=>num($id),
                'node_id'=>num($v)
            ];
            DB::table('admin_role_node')->insert($dd);
        }
        return true;
    }

    public static function delRode(int $id){
        if(Admin::where('role_id',$id)->first()){
            return false;
        }else{
            static::where('id',$id)->delete();
            DB::table('admin_role_node')->where('role_id',$id)->delete();
            return true;
        }


    }




}
