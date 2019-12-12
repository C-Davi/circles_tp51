<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;
use app\common\validate\SupportValidate;
use think\Controller;
use think\Request;
use app\common\model\Support as SupportModel;
class Support extends BaseController
{
    //用户顶踩文章
    public function index()
    {
        (new SupportValidate())->goCheck();
        (new SupportModel())->UserSupportPost();
        return self::showResCodeWithOutData('ok');
    }
}
