<?php
/**
 * Created by 项目名称.
 * User: the kite runner
 * Date: 2019/12/11 0011
 * Time: 21:36
 */

namespace app\common\validate;
use think\Validate;

class TopicClassValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id'=>'require|integer|>:0',
        'page'=>'require|integer|>:0',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [];
}