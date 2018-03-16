<?php

namespace app\Controllers;

use app\Models\AppModel;
use Server\CoreBase\Controller;

/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午3:51
 */
class RoomController extends Controller
{
    /**
     * @var AppModel
     */
    public $CommModel;
    public $data; // 传过来数据

    protected function initialization($controller_name, $method_name)
    {
        parent::initialization($controller_name, $method_name);
        $this->CommModel = $this->loader->model('CommModel', $this);
        $this->data = $this->client_data->data;
      $res =  yield $this->CommModel->exit($this->data);//判断传过来的类型;
      var_dump($res);
      // if($res){
      //       $this->send('nonono,数据错误',false);
      //      $this->close();
      //       return;
      // }
    }

    public function jinru()
    {
     echo 1;
    }
}