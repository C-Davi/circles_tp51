<?php
/**
 * Created by 项目名称.
 * User: the kite runner
 * Date: 2019/12/11 0011
 * Time: 21:12
 */

namespace app\common\model;


use think\Model;

class Topic extends Model
{
    //热门话题列表
    public function gethotlist()
    {
        return $this->where('type',1)->limit(10)->select()->toArray();
    }
}