<?php

namespace app\common\model;

use think\Model;

class Post extends Model
{
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 关联图片表
    public function images()
    {
        return $this->belongsToMany('Image','post_image');
    }
    // 关联顶踩表
    public function support()
    {
        return $this->hasMany('Support');
    }
    //发布文章
    public function createPost()
    {
        // 获取所有参数
        $params = request()->param();
        $userModel = new User();
        // 获取用户id
        $user_id = request()->userId;
        $currentUser = $userModel->get($user_id);
        $path = $currentUser->userinfo->path;
        // 发布文章
        $title = mb_substr($params['text'],0,30);
        $post = $this->create([
            'user_id'=>$user_id,
            'title'=>$title,
            'titlepic'=>'',
            'content'=>$params['text'],
            'path'=>$path ? $path : '未知',
            'type'=>0,
            'post_class_id'=>$params['post_class_id'],
            'share_id'=>0,
            'isopen'=>$params['isopen']
        ]);
        //关联图片
        $imglistLength = count($params['imglist']);
        if ($imglistLength>0){
            $ImageModel = new Image();
            for ($i=0;$i<$imglistLength;$i++){

            }
        }
    }
}
