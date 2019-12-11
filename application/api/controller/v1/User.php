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
}
