<?php

namespace app\common\model;

use app\lib\exception\BaseException;
use think\facade\Cache;
use think\Model;
use app\common\controller\AliSMSController;
class User extends Model
{
    protected $autoWriteTimestamp = true;
    //发送验证码
    public function sendCode()
    {
        $phone = request()->param('phone');
        if (Cache::get($phone)){
            throw new   BaseException(['code'=>200,'msg'=>'操作过于频繁','errorCode'=>30001]);
        }
        $code = random_int(1000,9999);
        if (!config('api.aliSMS.isopen')){
            Cache::set($phone,$code,config('api.aliSMS.expire'));
            throw new BaseException(['code'=>200,'msg'=>'验证码：'.$code,'errorCode'=>30005]);
        }
        $res = AliSMSController::sendSMS($phone,$code);
        if ($res['Code']=='OK'){
            return Cache::set($phone,$code,config('api.aliSMS.expire'));
        }else{
            if ($res['Code']=='isv.MOBILE_NUMBER_ILLEGAL'){
                throw new BaseException(['code'=>200,'msg'=>'无效号码','errorCode'=>30002]);
            }
            if ($res['Code']=='isv.DAY_LIMIT_CONTRO'){
                throw new BaseException(['code'=>200,'msg'=>'今日发送短信条数超限，改日再来！','errorCode'=>30003]);
            }
            throw new BaseException(['code'=>200,'msg'=>'发送失败','errorCode'=>30004]);
        }
    }
    //手机登录
    public function phoneLogin()
    {
        $param = request()->param();
        // 验证用户是否存在
        $user = $this->isExist(['phone'=>$param['phone']]);
        // 用户不存在，直接注册
        if(!$user){
            // 用户主表
            $user = self::create([
                'username'=>$param['phone'],
                'phone'=>$param['phone'],
                 'password'=>password_hash($param['phone'],PASSWORD_DEFAULT)
            ]);
            // 在用户信息表创建对应的记录（用户存放用户其他信息）
            $user->userinfo()->create([ 'user_id'=>$user->id ]);
            $user->logintype = 'phone';
            return $this->CreateSaveToken($user->toArray());
        }
        // 用户是否被禁用
        $this->checkStatus($user->toArray());
        // 登录成功，返回token
        return $this->CreateSaveToken($user->toArray());
    }
    //验证用户是否存在
    public function isExist($arr=[])
    {
        if (!is_array($arr)) return false;
        //手机号
        if (array_key_exists('phone',$arr)){
            return $this->where('phone',$arr['phone'])->find();
        }
        //用户id
        if (array_key_exists('id',$arr)){
            return $this->where('id',$arr['id'])->find();
        }
        //邮箱
        if (array_key_exists('email',$arr)){
            return $this->where('email',$arr['email'])->find();
        }
        //用户名
        if (array_key_exists('username',$arr)){
            return $this->where('username',$arr['username'])->find();
        }
        //第三方参数
        if (array_key_exists('provider',$arr)){
            $where = ['type'=>$arr['provider'],'openid'=>$arr['openid']];
            return $this->userbind()->where($where)->find();
        }
        return false;
    }
    //绑定用户信息表
    public function userinfo()
    {
        return $this->hasOne('Userinfo');
    }
    //生成保存token
    public function CreateSaveToken($arr=[])
    {
        $token = sha1(md5(uniqid(md5(microtime(true)),true)));
        $arr['token'] = $token;
        //登录过期时间
        $expire = array_key_exists('expires_in',$arr)?$arr['expires_in']:config('api.token_expire');
        //保存缓存中
        if (!Cache::set($token,$arr,$expire)){
            throw new BaseException();
        }
        return $token;
    }
    //判断用户是否禁用
    public function checkStatus($arr,$isReget=false)
    {
        $status =1;
        if ($isReget){
            $userid = array_key_exists('user_id',$arr) ? $arr['user_id']:$arr['id'];
            $user = $this->find($userid)->toArray();
            $status = $user['status'];
        }else{
            $status=$arr['status'];
        }
        if ($status==0){
            throw  new  BaseException(['code'=>200,'msg'=>'该用户已被禁用','errorCode'=>20001]);
        }
        return $arr;
    }
    //账号登录
    public function login()
    {
        $param = request()->param();
        $user = $this->isExist($this->filterUserData($param['username']));
        if (!$user){
            throw new BaseException(['code'=>200,'msg'=>'昵称/邮箱/手机号错误','errorCode'=>20000]);
        }
        $this->checkStatus($user->toArray());
        $this->checkPassWord($param['password'],$user->password);
        return $this->CreateSaveToken($user->toArray());
    }
    //验证用户名格式 昵称邮箱手机号
    public function filterUserData($data)
    {
        $arr=[];
        if (preg_match('^1(3|4|5|7|8)[0-9]\d{8}$^',$data)){
            $arr['phone']=$data;
            return $arr;
        }
        if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/',$data)){
            $arr['email']=$data;
            return $arr;
        }
        $arr['username']=$data;
        return $arr;
    }
    //验证密码
    public function checkPassWord($password,$hash)
    {
        if (!$hash){
            throw new BaseException(['code'=>200,'msg'=>'密码错误','errorCode'=>20002]);
        }
        if (!password_verify($password,$hash)){
            throw new BaseException(['code'=>200,'msg'=>'密码错误','errorCode'=>20002]);
        }
        return true;
    }
    //绑定第三方登录
    public function userbind()
    {
        return $this->hasMany('UserBind');
    }
    //第三方登录
    public function otherlogin()
    {
        $param = request()->param();
        $user = $this->isExist(['provider'=>$param['provider'],'openid'=>$param['openid']]);
        $arr = [];
        if (!$user){
            $data = [
                'type'=>$param['provider'],
                'openid'=>$param['openid'],
                'nickname'=>$param['nickName'],
                'avatarurl'=>$param['avatarUrl']
            ];
            $user = $this->userbind()->create($data);
            $arr = $user->toArray();
            $arr['expires_in']=$param['expires_in'];
            return $this->CreateSaveToken($arr);
        }
        $arr = $this->checkStatus($user->toArray());
        $arr['expires_in'] = $param['expires_in'];
        return $this->CreateSaveToken($arr);
    }
    //验证第三方登录绑定手机号
    public function OtherLoginIsBindPhone($user)
    {
        if (array_key_exists('type',$user)){
            if ($user['user_id']<1){
                throw new BaseException(['code'=>200,'msg'=>'请先绑定手机!','errorCode'=>20008]);
            }
            return $user['user_id'];
        }
        return $user['id'];
    }
    //退出登录
    public function logout()
    {
        if (!Cache::pull(request()->userToken)){
            throw new BaseException(['code'=>200,'msg'=>'退出登录','errorCode'=>30006]);
        }
        return true;
    }
    //关联文章模型
    public function post()
    {
        return $this->hasMany('Post');
    }
    //获取指定用户下的文章(分页)
    public function getPostList()
    {
        $params = request()->param();
        $user = $this->get($params['id']);
        if (!$user){
            TApiException('用户不存在',1000);
        }
        return $user->post()
            ->with([
                'user'=>function($query){return $query->field('id,username,userpic');},
                'images'=>function($query){return $query->field('url');},
                'share'
            ])
            ->where('isopen',1)->page($params['page'],10)->select();
    }
    //获取指定用户下的所有文章
    public function getAllPostList()
    {
        $params = request()->param();
        $user = $this->get($params['id']);
        if (!$user){
            TApiException('该用户不存在',10000);
        }
        return $user->post()
            ->with([
                'user'=>function($query){return $query->field('id,username,userpic');},
                'images'=>function($query){return $query->field('url');},
                'share'
            ])
            ->page($params['page'],10)->select();
    }
    //搜索用户
    public function search()
    {
        $param = request()->param();
        return $this->where('username','like','%'.$param['keyword'].'%')
            ->page($param['page'],10)->hidden('password')->select();
    }
    //验证当前绑定类型是否冲突
    public function checkBindType($current,$bingType)
    {
        if ($bingType==$current){
            TApiException('绑定类型冲突');
        }
        return true;
    }
    //绑定手机号
    public function bindphone()
    {
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        $currentLoginType = $currentUserInfo['logintype'];
        $this->checkBindType($currentLoginType,'phone');
        $binduser = $this->isExist(['phone'=>$params['phone']]);
        //存在
        if ($binduser){
            //账号邮箱登录
            if ($currentLoginType=='username'||$currentLoginType=='email'){
                TApiException('已绑定',20006,200);
            }
            //第三方登录
            if ($binduser->userbind()->where('type',$currentLoginType)->find()) TApiException('已绑定',20006,200);
            //直接修改
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $binduser->id;
            if ($userbind->save()){
                //更新缓存
                $currentUserInfo['uesr_id'] = $binduser->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        //不存在
        //账号邮箱存在
        if ($currentLoginType=='username'||$currentLoginType=='email'){
            $user = $this->save(['phone'=>$params['phone'],['id'=>$currentUserId]]);
            //更新缓存
            $currentUserInfo['phone']=$params['phone'];
            Cache::set($currentUserInfo['token'],$currentUserInfo,config('api.token_expire'));
            return true;
        }
        //第三方登录
        if (!$currentUserId){
            //user表创建账号
            $user = $this->create(['phone'=>$params['phone'],'username'=>$params['phone']]);
            //绑定
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $user->id;
            if ($userbind->save()){
                //更新缓存
                $currentUserInfo['user_id']=$user->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        //直接修改
        if ($this->save(['phone'=>$params['phone']],['id'=>$currentUserId]))return true;
        TApiException();

    }
    //绑定邮箱
    public function bindemail()
    {
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType,'email');
        // 查询该手机是否绑定了其他用户
        $binduser = $this->isExist(['email'=>$params['email']]);
        // 存在
        if ($binduser) {
            // 账号手机登录
            if ($currentLoginType == 'username' || $currentLoginType == 'phone') TApiException('已被绑定',20006,200);
            // 第三方登录
            if ($binduser->userbind()->where('type',$currentLoginType)->find()) TApiException('已被绑定',20006,200);
            // 直接修改
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $binduser->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $binduser->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        // 不存在
        // 账号手机登录
        if ($currentLoginType == 'username' || $currentLoginType == 'phone'){
            $user = $this->save([
                'email'=>$params['email']
            ],['id'=>$currentUserId]);
            // 更新缓存
            $currentUserInfo['email'] = $params['email'];
            Cache::set($currentUserInfo['token'],$currentUserInfo,config('api.token_expire'));
            return true;
        }
        // 第三方登录
        if (!$currentUserId) {
            // 在user表创建账号
            $user = $this->create([
                'username'=>$params['email'],
                'email'=>$params['email'],
            ]);
            // 绑定
            $userbind = $this->userbind()->find($currentUserInfo['id']);
            $userbind->user_id = $user->id;
            if ($userbind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $user->id;
                Cache::set($currentUserInfo['token'],$currentUserInfo,$currentUserInfo['expires_in']);
                return true;
            }
            TApiException();
        }
        // 直接修改
        if($this->save([
            'email'=>$params['email']
        ],['id'=>$currentUserId])) return true;
        TApiException();
    }
    //绑定第三方登录
    public function bindother()
    {
        $params = request()->param();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType,$params['provider']);
        // 查询是否存在
        $binduser = $this->isExist(['provider'=>$params['provider'],'openid'=>$params['openid']]);
        // 存在
        if ($binduser) {
            if ($binduser->user_id) TApiException('已被绑定',20006,200);
            $binduser->user_id = $currentUserId;
            return $binduser->save();
        }
        // 不存在
        return $this->userbind()->create([
            'type'=>$params['provider'],
            'openid'=>$params['openid'],
            'nickname'=>$params['nickName'],
            'avatarurl'=>$params['avatarUrl'],
            'user_id'=>$currentUserId
        ]);
    }
    //修改头像
    public function editUserpic()
    {
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid=request()->userId;
        $image = (new Image())->upload($userid,'userpic');
        // 修改用户头像
        $user = self::get($userid);
        $user->userpic = getFileUrl($image->url);
        if($user->save()) return true;
        TApiException();
    }
    // 修改资料
    public function editUserinfo(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid=request()->userId;
        // 修改昵称
        $user = $this->get($userid);
        $user->username = $params['name'];
        $user->save();
        // 修改用户信息表
        $userinfo = $user->userinfo()->find();
        $userinfo->sex = $params['sex'];
        $userinfo->qg = $params['qg'];
        $userinfo->job = $params['job'];
        $userinfo->birthday = $params['birthday'];
        $userinfo->path = $params['path'];
        $userinfo->save();
        return true;
    }
    // 修改密码
    public function repassword(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $user = self::get($userid);
        // 手机注册的用户并没有原密码,直接修改即可
        if ($user['password']) {
            // 判断旧密码是否正确
            $this->checkPassword($params['oldpassword'],$user['password']);
        }
        // 修改密码
        $newpassword = password_hash($params['newpassword'],PASSWORD_DEFAULT);
        $res = $this->save([
            'password'=>$newpassword
        ],['id'=>$userid]);
        if (!$res) TApiException('修改密码失败',20009,200);
        $user['password'] = $newpassword;
        // 更新缓存信息
        Cache::set(request()->Token,$user,config('api.token_expire'));
    }
    // 关联关注
    public function withfollow(){
        return $this->hasMany('Follow','user_id');
    }
    // 关注用户
    public function ToFollow(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $user_id = request()->userId;
        $follow_id = $params['follow_id'];
        // 不能关注自己
        if($user_id == $follow_id) TApiException('非法操作',10000,200);
        // 获取到当前用户的关注模型
        $followModel = $this->get($user_id)->withfollow();
        // 查询记录是否存在
        $follow = $followModel->where('follow_id',$follow_id)->find();
        if($follow) TApiException('已经关注过了',10000,200);
        $followModel->create([
            'user_id'=>$user_id,
            'follow_id'=>$follow_id
        ]);
        return true;
    }
    // 取消关注
    public function ToUnFollow(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $user_id = request()->userId;
        $follow_id = $params['follow_id'];
        // 不能取消关注自己
        if($user_id == $follow_id) TApiException('非法操作',10000,200);
        $followModel = $this->get($user_id)->withfollow();
        $follow = $followModel->where('follow_id',$follow_id)->find();
        if(!$follow) TApiException('暂未关注',10000,200);
        return $follow->delete();
    }
    // 获取互关列表
    public function getFriendsList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $page = $params['page'];
        $follows = \Db::table('user')->where('id','IN', function($query) use($userid){
            // 找出所有关注我的人的用户id
            $query->table('follow')
                ->where('user_id', 'IN', function ($query) use($userid){
                    // 找出所有我关注的人的用户id
                    $query->table('follow')->where('user_id', $userid)->field('follow_id');
                })->where('follow_id',$userid)
                ->field('user_id');
        })->field('id,username,userpic')->page($page,10)->select();
        return $follows;
    }
    // 关联粉丝列表
    public function fens(){
        return $this->belongsToMany('User','Follow','user_id','follow_id');
    }
    // 关联关注列表
    public function follows(){
        return $this->belongsToMany('User','Follow','follow_id','user_id');
    }
    // 获取当前用户粉丝列表
    public function getFensList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $fens = $this->get($userid)->fens()->page($params['page'],10)->select()->toArray();
        return $this->filterReturn($fens);
    }
    // 关注和粉丝返回字段
    public function filterReturn($param = []){
        $arr = [];
        $length = count($param);
        for ($i=0; $i < $length; $i++) {
            $arr[] = [
                'id'=>$param[$i]['id'],
                'username'=>$param[$i]['username'],
                'userpic'=>$param[$i]['userpic'],
            ];
        }
        return $arr;
    }
    // 获取当前用户关注列表
    public function getFollowsList(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $follows = $this->get($userid)->follows()->page($params['page'],10)->select()->toArray();
        return $this->filterReturn($follows);
    }
}
