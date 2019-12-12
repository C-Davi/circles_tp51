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
    //关联文章
    public function post()
    {
        return $this->belongsTo('Post','topic_post');
    }
    //获取指定话题下文章(分页)
    public function getPost()
    {
        $param = request()->param();
        return self::get($param['id'])->post()
            ->with([
                'user'=>function($query){return $query->field('id,username,userpic');},
                'images'=>function($query){return $query->field('url');},
                'share'
            ])
            ->page($param['page'],10)->select();
    }
    //根据标题搜索话题
    public function search()
    {
        $param = request()->param();
        return $this->where('title','like','%'.$param['keyword'].'%')->page()->select();
    }
}