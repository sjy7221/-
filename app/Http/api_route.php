<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods:POST,GET');



Route::any('login',function (){
    return view('login');
});

Route::any('api/login','Api\IndexController@login');
/**
 * 游客登入
 */
// redis 
    Route::any('redis/{room_id?}','Api\IndexController@all');
    Route::any('redis/{room_id}/del','Api\IndexController@del');
    Route::any('redis_delAll','Api\IndexController@del_all');

    
Route::any('api/youke','Api\IndexController@youke');
Route::group(['prefix'=>'api','namespace'=>'Api','middleware'=>['appApi']], function() {

    /**口口翻创建房间
     */
    Route::any('create_pdk','IndexController@create_pdk');
    /**口口翻进入房间
     */
    Route::any('join','IndexController@join');

   


});
