<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午1:44
 */

namespace app\Models;


use Server\CoreBase\Model;

class CommModel extends Model
{
	public function __construct(){
		return 11;
	}
    public function exit($data)
    {
       if(empty($data->mid) || empty($data->room_id)){
       	return true;
       }
       if (!yield $this->redis_pool->EXISTS($data->room_id)) {
            return true;
        }
        return false;
    }
}