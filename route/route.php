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
    // 发送验证码
    Route::post('user/sendcode','api/:v.User/sendCode');
    // 手机登录
    Route::post('user/phonelogin','api/:v.User/phoneLogin');
    // 账号密码登录
    Route::post('user/login','api/:v.User/login');
    // 第三方登录
    Route::post('user/otherlogin','api/:v.User/otherLogin');
    // 获取文章分类
    Route::get('postclass', 'api/:v.PostClass/index');
    // 获取话题分类
    Route::get('topicclass','api/:v.TopicClass/index');
    // 获取热门话题
    Route::get('hottopic','api/:v.Topic/index');
    // 获取指定话题分类下的话题列表
    Route::get('topicclass/:id/topic/:page', 'api/:v.TopicClass/topic');
    // 获取文章详情
    Route::get('post/:id', 'api/:v.Post/index');
    // 获取指定话题下的文章列表
    Route::get('topic/:id/post/:page', 'api/:v.Topic/post');
    // 获取指定文章分类下的文章
    Route::get('postclass/:id/post/:page', 'api/:v.PostClass/post')->middleware(['ApiGetUserid']);
    // 获取指定用户下的文章
    Route::get('user/:id/post/:page', 'api/:v.User/post');
    // 搜索话题
    Route::post('search/topic', 'api/:v.Search/topic');
    // 搜索文章
    Route::post('search/post', 'api/:v.Search/post');
    // 搜索用户
    Route::post('search/user', 'api/:v.Search/user');
    // 广告列表
    Route::get('adsense/:type', 'api/:v.Adsense/index');
    // 获取当前文章的所有评论
    Route::get('post/:id/comment','api/:v.Post/comment');
    // 检测更新
    Route::post('update','api/:v.Update/update');
    // 微信小程序登录
    Route::post('wxlogin','api/:v.User/wxLogin');
    // 支付宝小程序登录
    Route::post('alilogin','api/:v.User/alilogin');
});
//验证token
Route::group('api/:v/',function (){
    //退出登录
    Route::post('user/logout','api/:v.User/logout');
    // 绑定手机
    Route::post('user/bindphone','api/:v.User/bindphone');
})->middleware(['ApiUserAuth']);
//验证token，是否绑定手机号，是否禁用，按顺序
Route::group('api/:v/',function (){
    // 上传多图
    Route::post('image/uploadmore','api/:v.Image/uploadMore');
    // 发布文章
    Route::post('post/create','api/:v.Post/create');
    // 获取指定用户下的所有文章（含隐私）
    Route::get('user/post/:page', 'api/:v.User/Allpost');
    // 绑定邮箱
    Route::post('user/bindemail','api/:v.User/bindemail');
    // 绑定第三方
    Route::post('user/bindother','api/:v.User/bindother');
    // 用户顶踩
    Route::post('support', 'api/:v.Support/index');
    // 用户评论
    Route::post('post/comment','api/:v.Comment/comment');
    // 编辑头像
    Route::post('edituserpic','api/:v.User/editUserpic');
    // 编辑资料
    Route::post('edituserinfo','api/:v.User/editinfo');
    // 修改密码
    Route::post('repassword','api/:v.User/rePassword');
    // 加入黑名单
    Route::post('addblack','api/:v.Blacklist/addBlack');
    // 移出黑名单
    Route::post('removeblack','api/:v.Blacklist/removeBlack');
    // 关注
    Route::post('follow','api/:v.User/follow');
    // 取消关注
    Route::post('unfollow','api/:v.User/unfollow');
    // 互关列表
    Route::get('friends/:page','api/:v.User/friends');
    // 粉丝列表
    Route::get('fens/:page','api/:v.User/fens');
    // 关注列表
    Route::get('follows/:page','api/:v.User/follows');
    // 用户反馈
    Route::post('feedback','api/:v.Feedback/feedback');
    // 获取用户反馈列表
    Route::get('feedbacklist/:page','api/:v1.Feedback/feedbacklist');
})->middleware(['ApiUserAuth','ApiUserBindPhone','ApiUserStatus']);