<?php
/**
 * Created by 项目名称.
 * User: the kite runner
 * Date: 2019/12/11 0011
 * Time: 20:52
 */

namespace app\common\model;


use think\Model;

class PostClass extends Model
{
    //获得所有文章分类
    public function getPostClassList()
    {
        return $this->field('id,classname')->where('status',1)->select();
    }
}