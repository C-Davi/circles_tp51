<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;
use app\common\validate\CommentValidate;
use think\Controller;
use think\Request;
use app\common\model\Comment as CommentModel;
class Comment extends BaseController
{
    //用户发表评论
    public function comment()
    {
        (new CommentValidate())->goCheck();
        (new CommentModel())->comment();
        return self::showResCodeWithOutData('评论成功');
    }
}
