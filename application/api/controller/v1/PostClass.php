<?php
/**
 * Created by 项目名称.
 * User: the kite runner
 * Date: 2019/12/11 0011
 * Time: 20:52
 */

namespace app\api\controller\v1;


use app\common\controller\BaseController;
use app\common\model\PostClass as PostClassModel;
use app\common\validate\TopicClassValidate;

class PostClass extends BaseController
{
    //获取文章分类列表
    public function index()
    {
        $list = (new PostClassModel())->getPostClassList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
    //获取指定分类下的文章
    public function post()
    {
        (new TopicClassValidate())->goCheck();
        $list = (new PostClassModel())->getPost();
        return self::showResCode('获取成功',['list'=>$list]);
    }
}