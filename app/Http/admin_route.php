<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/24
 * Time: 11:15
 */


Route::get('admin/login', 'Admin\LoginController@index');
Route::post('admin/login', 'Admin\LoginController@login');
Route::get('admin/outlogin', 'Admin\LoginController@outLogin');
Route::get('admin/editPassword', 'Admin\AdminController@editPassword');
Route::post('admin/editPassword', 'Admin\AdminController@editPassword');
Route::get('verify', 'Admin\LoginController@verify');
Route::get('/', function (){

});
Route::get('admin/set/admin',function (){
    return view('admin.set.admin');
});
Route::group(['prefix'=>'admin','namespace'=>'Admin','middleware'=>['admin']], function(){
    Route::get('/', 'IndexController@index');
    Route::any('getPeople', 'IndexController@getPeople');
    Route::any('getMoney', 'IndexController@getMoney');

    //AdminController
    Route::get('admin', 'AdminController@index');
    Route::post('admin', 'AdminController@index');
    Route::get('admin/create/{id}', 'AdminController@create');
    Route::post('admin/store', 'AdminController@store');
    Route::get('admin/{id}/edit', 'AdminController@edit');
    Route::post('admin/save', 'AdminController@save');
    Route::get('admin/{id}/black', 'AdminController@black');

    //SystemController
    Route::get('system/log', 'SystemController@log');

    //MemberController
    Route::get('member/index','MemberController@index');
    Route::get('member/{id}/edit', 'MemberController@edit');
    Route::post('member/update', 'MemberController@update');
    Route::get('member/pass/{id}', 'MemberController@pass');
    Route::get('member/refuse/{id}', 'MemberController@refuse');

    //NoticeController
    Route::get('notice/method','NoticeController@method');
    Route::post('notice/save', 'NoticeController@save');
    Route::get('notice/notice', 'NoticeController@notice');
    Route::get('notice/inform', 'NoticeController@inform');
    Route::get('notice/feedback', 'NoticeController@feedback');

    //GoodsController
    Route::get('goods/index','GoodsController@index');
    Route::get('goods/edit_show/{id}','GoodsController@edit_show');
    Route::post('goods/edit','GoodsController@edit');
    Route::get('goods/add_show','GoodsController@add_show');
    Route::post('goods/add','GoodsController@add');
    Route::get('goods/{id}/black', 'GoodsController@black');

    //SettingController
    Route::get('set/index','SettingController@index');
    Route::post('set/edit','SettingController@edit');
    Route::post('set/admin_save','SettingController@admin_save');

    //RecordController
    Route::get('record/index_draw','RecordController@index_draw');   //提现管理
    Route::get('record/index_recharge','RecordController@index_recharge'); //充值记录
    Route::get('record/{sn}/{status}/draw_true','RecordController@draw_true'); //提现操作
    Route::post('record/draw_false','RecordController@draw_false'); //提现操作拒绝
    Route::get('record/{id}/member_info','RecordController@member_info'); //钻石记录
    Route::get('record/{id}/agent_info','RecordController@agent_info'); //佣金记录
    Route::get('record/index_hou','RecordController@index_hou'); //后台充值记录

    //agent
    Route::get('agent/{mid}/create','AgentController@create');          //创建代理
    Route::get('agent/index','AgentController@index');    //代理列表
    Route::get('agent/{mid}/{status}/edit_status','AgentController@edit_status'); //拉黑解除代理
    Route::get('agent/{mid}/del','AgentController@del');  //删除代理
    Route::get('agent/{mid}/reset','AgentController@reset'); //重置代理密码

});

//代理
Route::get('agent/login', 'Agent\LoginController@index');
Route::post('agent/login', 'Agent\LoginController@login');
Route::get('agent/outlogin', 'Agent\LoginController@outLogin');
Route::group(['prefix'=>'agent','namespace'=>'Agent','middleware'=>['agent']], function(){
    Route::any('/', 'AgentController@index');
    Route::get('index','AgentController@index');
    Route::get('player','AgentController@player');
    Route::get('cashback','AgentController@cashback');
    Route::get('orderstatis','AgentController@orderstatis');
    Route::get('applylist','AgentController@applylist');
    Route::get('apply','AgentController@apply');
    Route::get('userinfo','AgentController@userinfo');
    Route::get('changepw','AgentController@changepw');
    Route::post('changepw_store','AgentController@changepw_store');
    Route::post('chong','AgentController@chong');
});