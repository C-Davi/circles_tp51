<?php

namespace app\common\controller;

use think\Controller;
use think\Request;

class BaseController extends Controller
{
    /**
     *
     * Desc: api统一返回格式
     * Date: 2019/12/10
     * @param string $msg
     * @param array $data
     * @param int $code
     * @return \think\response\Json
     */
    static public function showResCode($msg='未知',$data=[],$code =200)
    {
        $res = [
            'msg'   => $msg,
            'data'  => $data
        ];
        return json($res,200);
    }
    //无数据返回格式
    static public function showResCodeWithOutData($msg='未知',$code =200)
    {
        return self::showResCode($msg,[],$code);
    }
}
