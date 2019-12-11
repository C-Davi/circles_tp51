<?php

namespace app\http\middleware;
//token合法性  是否登录
use app\lib\exception\BaseException;
use \think\facade\Cache;

class ApiUserAuth
{
    public function handle($request, \Closure $next)
    {
        $param = $request->header();
        if (!array_key_exists('token',$param)){
            throw new BaseException(['code'=>200,'msg'=>'非法token','errorCode'=>20003]);
        }
        $token = $param['token'];
        $User = Cache::get($token);
        if (!$User){
            throw new BaseException(['code'=>200,'msg'=>'非法token,请重新登录','errorCode'=>20003]);
        }
        $request->userToken = $token;
        $request->userId = array_key_exists('type',$User)?$User['user_id']:$User['id'];
        $request->userTokenUserInfo = $User;
        return $next($request);
    }
}
