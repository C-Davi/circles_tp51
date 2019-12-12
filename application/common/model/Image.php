<?php

namespace app\common\model;

use app\common\controller\FileController;
use think\Model;

class Image extends Model
{
    //自动写入时间戳
    protected $autoWriteTimestamp = true;
    //上传多图
    public function uploadMore()
    {
        $image = $this->upload(request()->userId,'imglist');
        $imageCount = self::count($image);
        for ($i=0;$i<$imageCount;$i++){
            $image[$i]['url']=getFileUrl($image[$i]['url']);
        }
        return $image;
    }
    //上传图片
    public function upload($userid = '',$field = '')
    {
        $files = request()->file($field);
        if (is_array($files)){
            //多图
            $arr = [];
            foreach ($files as $file){
                $res = FileController::UploadEvent($file);
                if ($res['status']){
                    $arr[]=[
                        'url'=>$res['data'],
                        'user_id'=>$userid
                    ];
                }
            }
            return $this->saveAll($arr);
        }
        //单图
        if (!$files){
            TApiException('请选择上传的图片',10000,200);
        }
        $file = FileController::UploadEvent($files);
        if (!$file['status']) TApiException($file['data'],10000,200);
        return self::create(['url'=>$file['data'],'user_id'=>$userid]);
    }
}
