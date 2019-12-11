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
class PostClass extends BaseController
{
    public function index()
    {
        $list = (new PostClassModel())->getPostClassList();
        return self::showResCode('获取成功',['list'=>$list]);
    }
}