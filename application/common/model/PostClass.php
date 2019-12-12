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
    //关联文章模型
    public function post()
    {
        return $this->hasMany('Post');
    }
    //获取指定分类下的文章（分页）
    public function getPost()
    {
        $param = request()->param();
        $userId = request()->userId?request()->userId:0;
        return self::get($param['id'])->post()
            ->with([
                'user'=>function($query){return $query->field('id,username,userpic');},
                'images'=>function($query){return $query->field('url');},
                'share',
                'support'=>function($query)use($userId){return $query->where('user_id',$userId);}
            ])
            ->page($param['page'],10)->select();
    }
}