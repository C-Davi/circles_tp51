<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
//无token
Route::group('api/:v/',function (){
    Route::post('user/sendcode','api/:v.User/sendCode');
    Route::post('user/phonelogin','api/:v.User/phoneLogin');
    Route::post('user/login','api/:v.User/login');
    Route::post('user/otherlogin','api/:v.User/otherLogin');
});
//验证token
Route::group('api/:v/',function (){

})->middleware(['ApiUserAuth']);
//验证token，是否绑定手机号，是否禁用，按顺序
Route::group('api/:v/',function (){

})->middleware(['ApiUserAuth','ApiUserBindPhone','ApiUserStatus']);