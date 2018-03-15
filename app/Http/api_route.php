<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods:POST,GET');

Route::any('nexPai','Api\IndexController@nexPai');
Route::any('test','Api\IndexController@test');
Route::any('test_kwx','Api\IndexController@test_kwx');
Route::any('qingkong','Api\IndexController@qingkong');
Route::any('test2','Api\GamesController@test');
Route::any('pay','Api\IndexController@pay');
/**
 * 发送验证码
 */
Route::any('api/send','Api\IndexController@send');

Route::any('login',function (){
    return view('login');
});

Route::any('api/login','Api\IndexController@login');
/**
 * 游客登入
 */
Route::any('api/youke','Api\IndexController@youke');
Route::group(['prefix'=>'api','namespace'=>'Api','middleware'=>['appApi']], function() {

    /**口口翻创建房间
     */
    Route::any('create_pdk','IndexController@create_pdk');
    /**口口翻进入房间
     */
    Route::any('join','IndexController@join');

    /**
     * 卡五星创建房间
     */
    // Route::any('create_pdk','IndexController@create_pdk');

    /**
     * 卡五星进入房间
     */
    Route::any('join_kwx','IndexController@join_kwx');

      /**获取房间信息
       */
    Route::any('getRoom','IndexController@getRoom');

    /**获取房卡数
     */
    Route::any('dating','IndexController@dating');

    /**游戏记录
     */
    Route::any('logs','IndexController@logs');
    /**记录详情
     */
    Route::any('logs_info','IndexController@logs_info');
    /**回放
     */
    Route::any('playback','IndexController@playback');

    /**
     * 绑定手机号
     */
    Route::any('bindPhone','IndexController@bindPhone');

    /**
     * 绑定代理手机号
     */
    Route::any('bindAgent','IndexController@bindAgent');
    /**
     * 获取消息
     */
    Route::any('getMsg','IndexController@getMsg');
    /**
     * 反馈
     */
    Route::any('fankui','IndexController@fankui');
    /**
     * 获取玩法
     */
    Route::any('getWan','IndexController@getWan');

    /**
     * 获取所有商品
     * */
    Route::any('goods','IndexController@goods');

    Route::any('pay','IndexController@pay');

});
