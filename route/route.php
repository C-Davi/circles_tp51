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
    Route::get('postclass','api/:v.PostClass/index');
    Route::get('topicclass','api/:v.TopicClass/index');
    Route::get('hottopic','api/v1.Topic/index');//获取热门话题
    Route::get('topicclass/:id/topic/:page', 'api/v1.TopicClass/topic');// 获取指定话题分类下的话题列表
    
});
//验证token
Route::group('api/:v/',function (){
    //退出登录
    Route::post('user/logout','api/:v.User/logout');
})->middleware(['ApiUserAuth']);
//验证token，是否绑定手机号，是否禁用，按顺序
Route::group('api/:v/',function (){
    //上传多图
    Route::post('image/uploadmore','api/:v.Image/uploadMore');
})->middleware(['ApiUserAuth','ApiUserBindPhone','ApiUserStatus']);