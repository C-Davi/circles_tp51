<?php

namespace app\common\model;

use app\lib\exception\BaseException;
use think\facade\Cache;
use think\Model;
use app\common\controller\AliSMSController;
class User extends Model
{
    protected $autoWriteTimestamp = true;
    //发送验证码
    public function sendCode()
    {
        $phone = request()->param('phone');
        if (Cache::get($phone)){
            throw new   BaseException(['code'=>200,'msg'=>'操作过于频繁','errorCode'=>30001]);
        }
        $code = random_int(1000,9999);
        if (!config('api.aliSMS.isopen')){
            Cache::set($phone,$code,config('api.aliSMS.expire'));
            throw new BaseException(['code'=>200,'msg'=>'验证码：'.$code,'errorCode'=>30005]);
        }
        $res = AliSMSController::sendSMS($phone,$code);
        if ($res['Code']=='OK'){
            return Cache::set($phone,$code,config('api.aliSMS.expire'));
        }else{
            if ($res['Code']=='isv.MOBILE_NUMBER_ILLEGAL'){
                throw new BaseException(['code'=>200,'msg'=>'无效号码','errorCode'=>30002]);
            }
            if ($res['Code']=='isv.DAY_LIMIT_CONTRO'){
                throw new BaseException(['code'=>200,'msg'=>'今日发送短信条数超限，改日再来！','errorCode'=>30003]);
            }
            throw new BaseException(['code'=>200,'msg'=>'发送失败','errorCode'=>30004]);
        }
    }
    //手机登录
    public function phoneLogin()
    {
        $params = request()->param();
        $user = $this->isExists(['phone'=>$params['phone']]);
        if (!$user){
            $user = self::create([
                'username'=>$params['phone'],
                'phone'=>$params['phone'],
                'password'=>password_hash($params['phone'],PASSWORD_DEFAULT)
            ]);
            $user->userinfo()->create(['user_id'=>$user->id]);
            return $this->CreateSaveToken($user->toArray());
        }
        $this->checkStatus($user->toArray());
        return $this->CreateSaveToken($user->toArray());
    }
    //验证用户是否存在
    public function isExists($arr=[])
    {
        if (!is_array($arr)) return false;
        if (array_key_exists('phone',$arr)){
            return $this->where('phone',$arr['phone'])->find();
        }
        if (array_key_exists('id',$arr)){
            return $this->where('id',$arr['id'])->find();
        }
        if (array_key_exists('email',$arr)){
            return $this->where('email',$arr['email'])->find();
        }
        if (array_key_exists('username',$arr)){
            return $this->where('username',$arr['username'])->find();
        }
        if (array_key_exists('provider',$arr)){
            $where = ['type'=>$arr['provider'],'openid'=>$arr['openid']];
            return $this->userbind()->where($where)->find();
        }
        return false;
    }
    //绑定用户信息表
    public function userinfo()
    {
        return $this->hasOne('Userinfo');
    }
    //生成保存token
    public function CreateSaveToken($arr=[])
    {
        $token = sha1(md5(uniqid(md5(microtime(true)),true)));
        $arr['token'] = $token;
        //登录过期时间
        $expire = array_key_exists('expires_in',$arr)?$arr['expires_in']:config('api.token_expire');
        //保存缓存中
        if (!Cache::set($token,$arr,$expire)){
            throw new BaseException();
        }
        return $token;
    }
    //判断用户是否禁用
    public function checkStatus($arr,$isReget=false)
    {
        $status =1;
        if ($isReget){
            $userid = array_key_exists('user_id',$arr) ? $arr['user_id']:$arr['id'];
            $user = $this->find($userid)->toArray();
            $status = $user['status'];
        }else{
            $status=$arr['status'];
        }
        if ($status==0){
            throw  new  BaseException(['code'=>200,'msg'=>'该用户已被禁用','errorCode'=>20001]);
        }
        return $arr;
    }
    //账号登录
    public function login()
    {
        $param = request()->param();
        $user = $this->isExists($this->filterUserData($param['username']));
        if (!$user){
            throw new BaseException(['code'=>200,'msg'=>'昵称/邮箱/手机号错误','errorCode'=>20000]);
        }
        $this->checkStatus($user->toArray());
        $this->checkPassWord($param['password'],$user->password);
        return $this->CreateSaveToken($user->toArray());
    }
    //验证用户名格式 昵称邮箱手机号
    public function filterUserData($data)
    {
        $arr=[];
        if (preg_match('^1(3|4|5|7|8)[0-9]\d{8}$^',$data)){
            $arr['phone']=$data;
            return $arr;
        }
        if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/',$data)){
            $arr['email']=$data;
            return $arr;
        }
        $arr['username']=$data;
        return $arr;
    }
    //验证密码
    public function checkPassWord($password,$hash)
    {
        if (!$hash){
            throw new BaseException(['code'=>200,'msg'=>'密码错误','errorCode'=>20002]);
        }
        if (!password_verify($password,$hash)){
            throw new BaseException(['code'=>200,'msg'=>'密码错误','errorCode'=>20002]);
        }
        return true;
    }
    //绑定第三方登录
    public function userbind()
    {
        return $this->hasMany('UserBind');
    }
    //第三方登录
    public function otherlogin()
    {
        $param = request()->param();
        $user = $this->isExists(['provider'=>$param['provider'],'openid'=>$param['openid']]);
        $arr = [];
        if (!$user){
            $data = [
                'type'=>$param['provider'],
                'openid'=>$param['openid'],
                'nickname'=>$param['nickName'],
                'avatarurl'=>$param['avatarUrl']
            ];
            $user = $this->userbind()->create($data);
            $arr = $user->toArray();
            $arr['expires_in']=$param['expires_in'];
            return $this->CreateSaveToken($arr);
        }
        $arr = $this->checkStatus($user->toArray());
        $arr['expires_in'] = $param['expires_in'];
        return $this->CreateSaveToken($arr);
    }
    //验证第三方登录绑定手机号
    public function OtherLoginIsBindPhone($user)
    {
        if (array_key_exists('type',$user)){
            if ($user['user_id']<1){
                throw new BaseException(['code'=>200,'msg'=>'请先绑定手机!','errorCode'=>20008]);
            }
            return $user['user_id'];
        }
        return $user['id'];
    }
    //退出登录
    public function logout()
    {
        if (!Cache::pull(request()->userToken)){
            throw new BaseException(['code'=>200,'msg'=>'退出登录','errorCode'=>30006]);
        }
        return true;
    }
}
