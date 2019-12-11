<?php
/**
 * Created by 项目名称.
 * User: the kite runner
 * Date: 2019/12/11 0011
 * Time: 20:59
 */

namespace app\common\model;


use think\Model;

class TopicClass extends Model
{
    //获取所有的话题分类
    public function getPostClassList()
    {
        return $this->field('id,classname')->where('status',1)->select();
    }
    //管理话题
    public function topic()
    {
        return $this->hasMany('Topic');
    }
    //获取指定分类下的话题  分页
    public function getTopic()
    {
        // 获取所有参数
        $param = request()->param();
        return self::get($param['id'])->topic()->page($param['page'],10)->select();
    }
}