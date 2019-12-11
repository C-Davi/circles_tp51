<?php

namespace app\http\middleware;
use app\common\model\User;
//检测用户是否禁用
class ApiUserStatus
{
    public function handle($request, \Closure $next)
    {
        $param = $request->userTokenUserInfo;
        (new User())->checkStatus($param,true);
        return $next($request);
    }
}
