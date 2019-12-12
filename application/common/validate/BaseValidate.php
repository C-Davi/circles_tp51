<?php

namespace app\common\validate;

use app\common\model\Post;
use app\common\model\PostClass;
use app\common\model\Topic;
use app\common\model\User;
use think\Validate;
use app\lib\exception\BaseException;

class BaseValidate extends Validate
{
    public function goCheck($scene=false)
    {
        // 获取请求传递过来的所有参数
        $params = request()->param();
        // 开始验证
        $check = $scene ?
            $this->scene($scene)->check($params):
            $this->check($params);
        if (!$check) {
            throw new BaseException(['msg'=>$this->getError(),'errorCode'=>10000,'code'=>400]);
        }
        return true;
    }
    //验证验证码
    protected function isPerfectCode($value,$rule='',$data='',$filed='')
    {
        $beforeCode = cache($data['phone']);
        if (!$beforeCode){
            return '请冲洗获取验证码';
        }
        if ($value!=$beforeCode){
            return '验证码错误';
        }
    }
    //话题是否存在
    protected function isTopicExist($value, $rule='', $data='', $field='')
    {
        if ($value==0){
            return true;
        }
        if (Topic::field('id')->find($value)){
            return true;
        }
        return '该话题已不存在';
    }
    //文章分类是否存在
    protected function isPostClassExist($value, $rule='', $data='', $field='')
    {
        if (PostClass::field('id')->find($value)){
            return true;
        }
        return '该文章分类已不存在';
    }
    //文章是否存在
    protected function isPostExist($value, $rule='', $data='', $field='')
    {
        if (Post::field('id')->find($value)){
            return true;
        }
        return '该文章已不存在';
    }
    //用户是否存在
    protected function isUserExist($value, $rule='', $data='', $field='')
    {
        if (User::field('id')->find($value)){
            return true;
        }
        return '该用户不存在';
    }
}
