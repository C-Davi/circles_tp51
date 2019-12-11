<?php

namespace app\http\middleware;
use app\common\model\User;
//检测第三方是否绑定手机
class ApiUserBindPhone
{
    public function handle($request, \Closure $next)
    {
        $param = $request->userTokenUserInfo;
        (new User())->OtherLoginIsBindPhone($param);
        return $next($request);
    }
}
