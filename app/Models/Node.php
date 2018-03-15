<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    protected $table = 'admin_node';
    public $timestamps=false;

    public static function createNode($attributes){
        extract($attributes);
            $data = [
                'pid'=>$pid,
                'sort'=>$sort,
                'name'=>$name,
                'url'=>$url,
                'action'=>$action,
                'type'=>$type
            ];

           return static::insert($data);
    }

    //获取主节点
    public  static function getZNode()
    {
        $data = static::where('pid',0)
                ->orderBy('sort','desc')
                ->select('name','id')
                ->get()
                ->toArray();
       return $data;
    }

    //获取修改内容
    public  static function getNode(int $id)
    {
        $data = static::where('id',$id)->first()->toArray();
        return $data;
    }

    public static function editNode($attributes){
        extract($attributes);
        $data = [
            'pid'=>$pid,
            'sort'=>$sort,
            'name'=>$name,
            'url'=>$url,
            'action'=>$action,
            'type'=>$type
        ];
        return static::where('id',$id)->update($data);
    }
    public static function delNode(int $id){
        $data = static::where('id',$id)->delete();
        return $data;
    }

    public static function  getAll(){
        $data = static::orderBy('sort','desc')->orderBy('id','asc')->get()->toArray();
        return $data;
    }

    public static function getRoleNode($role_id)
    {
        $data = static::rightJoin('admin_role_node','admin_node.id','=','admin_role_node.node_id')
              ->select('admin_node.name as nodename','admin_node.id as node_id')
              ->where('admin_role_node.role_id',$role_id)
              ->get()
              ->toArray();
        return $data;
    }

    public static function editSort($id,int $sort){
        static::where('id',$id)->update(['sort'=>$sort]);
    }

}
