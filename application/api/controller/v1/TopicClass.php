<?php
/**
 * Created by 项目名称.
 * User: the kite runner
 * Date: 2019/12/11 0011
 * Time: 20:59
 */

namespace app\api\controller\v1;
use app\common\model\TopicClass as TopicClassModel;

use app\common\controller\BaseController;
use app\common\validate\TopicClassValidate;

class TopicClass extends BaseController
{
    //获取话题分类列表
    public function index()
    {
        $list = (new TopicClassModel())->getPostClassList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
    // 获取指定话题分类下的话题列表
    public function topic()
    {
        (new TopicClassValidate())->goCheck();
        $list = (new TopicClassModel())->getTopic();
        return self::showResCode('获取成功',['list'=>$list]);
    }
}