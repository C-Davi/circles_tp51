<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Request;
use app\common\controller\BaseController;
use app\common\validate\UserValidate;
use app\common\model\User as UserModel;

class User extends BaseController
{
    //发送短信
    public function sendCode()
    {
        (new UserValidate())->goCheck('sendCode');
        (new UserModel())->sendCode();
        return self::showResCodeWithOutData('发送成功');
    }
    //手机号登录
    public function phoneLogin()
    {
        (new UserValidate())->goCheck('phonelogin');
        $token = (new UserModel())->phoneLogin();
        return self::showResCode('登录成功',['token'=>$token]);
    }
    //账号登录
    public function login()
    {
        (new UserValidate())->goCheck('login');
        $token = (new UserModel())->login();
        return self::showResCode('登录成功',['token'=>$token]);
    }
    //第三方登录
    public function otherLogin()
    {
        (new UserValidate())->goCheck('otherlogin');
        $token = (new UserModel())->otherlogin();
        return self::showResCode('登陆成功',['token'=>$token]);
    }
    //退出登录
    public function logout()
    {
        (new UserModel())->logout();
        return self::showResCodeWithOutData('退出成功');
    }
    //用户发表文章列表分页
    public function post()
    {
        (new UserValidate())->goCheck('post');
        $list = (new UserModel())->getPostList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
    //用户发表文章列表
    public function Allpost()
    {
        (new UserValidate())->goCheck('allpost');
        $list = (new UserModel())->getAllPostList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
    //绑定手机
    public function bindphone()
    {
        (new UserValidate())->goCheck('bindphone');
        (new UserModel())->bindphone();
        return self::showResCodeWithOutData('绑定成功');
    }
    //绑定邮箱
    public function bindemail()
    {
        (new UserValidate())->goCheck('bindemail');
        (new UserModel())->bindemail();
        return self::showResCodeWithOutData('绑定成功');
    }
    //绑定第三方
    public function bindother()
    {
        (new UserValidate())->goCheck('bindother');
        (new UserModel())->bindother();
        return self::showResCodeWithOutData('绑定成功');
    }
    // 修改头像
    public function editUserpic()
    {
        (new UserValidate())->goCheck('edituserpic');
        (new UserModel())->editUserpic();
        return self::showResCodeWithOutData('修改头像成功');
    }
    // 修改资料
    public function editinfo()
    {
        (new UserValidate())->goCheck('edituserinfo');
        (new UserModel())->editUserinfo();
        return self::showResCodeWithOutData('修改成功');
    }
    // 修改密码
    public function rePassword()
    {
        (new UserValidate())->goCheck('repassword');
        (new UserModel())->repassword();
        return self::showResCodeWithOutData('修改密码成功');
    }
    // 关注
    public function follow()
    {
        (new UserValidate())->goCheck('follow');
        (new UserModel())->ToFollow();
        return self::showResCodeWithOutData('关注成功');
    }
    // 取消关注
    public function unfollow()
    {
        (new UserValidate())->goCheck('unfollow');
        (new UserModel())->ToUnFollow();
        return self::showResCodeWithOutData('取消关注成功');
    }
    // 互关列表
    public function friends()
    {
        (new UserValidate())->goCheck('getfriends');
        $list = (new UserModel())->getFriendsList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
    // 粉丝列表
    public function fens()
    {
        (new UserValidate())->goCheck('getfens');
        $list = (new UserModel())->getFensList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
    // 关注列表
    public function follows()
    {
        (new UserValidate())->goCheck('getfollows');
        $list = (new UserModel())->getFollowsList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
    //微信小程序登录
    public function wxLogin(Request $request)
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        // 参数
        $params['appid']= config('api.wx.appid');
        $params['secret']=  config('api.wx.secret');
        $params['js_code']= $request->param('code');
        $params['grant_type']= 'authorization_code';
        // 微信API返回的session_key 和 openid
        $arr = httpWurl($url, $params, 'POST');
        $arr = json_decode($arr,true);
        // 判断是否成功
        if(isset($arr['errcode']) && !empty($arr['errcode'])){
            return self::showResCodeWithOutData($arr['errmsg']);
        }
        // 拿到数据
        $request->provider = 'weixin';
        $request->openid = $arr['openid'];
        $request->expires_in = 1000000;
        //第三方登录
        $user =(new UserModel())->otherlogin();
        return self::showResCode('登录成功',$user);
    }
    //支付宝小程序登录
    public function alilogin()
    {

    }
}
