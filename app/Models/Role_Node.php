<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role_Node extends Model
{
    protected $table = 'admin_role_node';
    public $timestamps=false;

    public static function getFirst(int $role_id){

      $data = static::where('role_id',$role_id)->get()->toArray();

      foreach ($data as $v){
          $aa[]= $v['node_id'];
      }
      return $aa;
    }

}
