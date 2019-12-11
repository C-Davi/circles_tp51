<?php

namespace app\common\validate;

class UserValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'phone'=>'require|mobile',
        'code'=>'require|number|length:4|isPerfectCode',
        'username'=>'require',
        'password'=>'require|alphaDash',
        'provider'=>'require',
        'openid' => 'require',
        'nickName'=>'require',
        'avatarUrl'=>'require',
        'expires_in'=>'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'phone.require'=>'请填写手机号码',
        'phone.mobile' =>'手机号码不合法',
        'code.require' =>'请填写验证码',
        'code.number'  => '验证码格式错误',
        'code.length'  =>'验证码长度不正确',
        'username.require'=>'用户名不能为空'
    ];
    //配置场景
    protected $scene = [
        'sendCode' =>['phone'],
        'phonelogin'=>['phone','code'],
        'login'=>['username','password'],
        'otherlogin'=>['provider','openid','nickName','avatarUrl','expires_in']
    ];
}
