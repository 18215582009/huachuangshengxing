<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/19
 * Time: 19:38
 */

namespace app\api\controller;


use app\common\lib\Func;
use sms\SendSms;
use sms\Ypsms;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use app\common\error\Error;
use \app\common\model\Users as pu;

class User extends Uu
{
    private $code = '012345780123457896012345780123457896012345780123457896012345780123457896'; # 随机字符
    private $table;
    const PWD = 'ISCHOOL';
    private $kjmessage = 'iSchool_'; //极光账号标识
    private static $uuid = '';
    public function __construct()
    {
        parent::__construct();
        $this->table = new pu();

    }

    /**
     * 登录
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {

        $username = $this->request->post('username') ? $this->request->post('username') : false;
        $password = $this->request->post('password') ? $this->request->post('password') : false;
        $cid = $this->request->post('cid') ? $this->request->post('cid') : false;
        if($username && $password && $cid){
            $info = $this->table->alias('u')
                ->field('u.user_id,u.log_type,u.clientid,u.real,us.grade,u.school school_id,u.head_image,kjmessage_id,u.real,u.username,u.nickname,u.u_vip,u.sex,u.student,u.uuid')
                ->join('users_secretary us','u.user_id=us.user_id','left')
                ->where('username',$username)
                ->where('password',md5(md5($password)))
                ->find();

            if(isset($info['user_id'])){
                if(Cache::get($cid) == $cid) Cache::rm($username.'log');
                if(!(Cache::has($username.'log'))){
                    if(Cache::has($cid)) Cache::rm($cid);
                    $log_type = substr(str_shuffle($this->randStr()),0,10);
                    $_token = $this->generate($username, $this->request->controller().$this->request->action());
                    if($info['grade'] == null && $info['grade'] == 0) $info['grade'] = 0;
                    Cache::set($username.'log',$log_type);
                    $info['tk'] = $_token;
                    $bool = $this->table->where('user_id',$info['user_id'])->update(['ltime'=>time(),'log_type'=>$log_type]);
                    if($bool){
                        if(empty($info['uuid'])){
                            self::$uuid = $this->uuid();
                            $data = [
                                'uuid'  => self::$uuid
                            ];
                            Db::table('users')->where('user_id',$info['user_id'])->update($data);
                        }
                        $info = $info->toArray();
                        if(self::$uuid){
                            $info['uuid'] = self::$uuid;
                        }
                        Cache::set('userInfo'.$info['user_id'],$info);
                        return Func::ds('登录成功',Error::$SUCCESS,$info);
                    }else{
                        return Func::ds('登录出现异常',Error::$LOGIN_ERROR);
                    }
                }else{
                    Cache::set($cid,$cid);
                    # Cache::rm($username.'log');
                    return Func::ds('已在其他设备登录',Error::$OTHER_LOGIN);
                    /*if($info['log_type'] == Cache::get($username.'log')){
                        return ds('账号已登录',Error::$LOGIN_TYPE_EXIST);
                    }else{
                    }*/
                }

            }else{
                return Func::ds('请确认用户名和密码',Error::$PWD_USER_ERROR);
            }
        }else{
            return Func::ds('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

   /**
     * 退出登录，，清除登录信息
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function logOut()
    {
        $uid = $this->request->post('user_id')? $this->request->post('user_id'):false;
        if(!empty($uid)){
            $user = $this->table->field('username')->find($uid);
            if(isset($user['username'])) return Func::ds('获取用户信息失败',Error::$OBTAIN_ERROR);
            Cache::rm($user['username'].'log');
            if(!Cache::has($user['username'].'log')){
                return Func::ds('参数成功',Error::$SUCCESS);
            }else{
                return Func::ds('参数失败',Error::$LOGIN_OUT_ERROR);
            }
        }else{
            return Func::ds('参数未获取',Error::$PARAMETER_ERROR);
        }

    }


    /**
     * 发送验证码
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function codeSend()
    {
        $phone = $this->request->post('phone')? $this->request->post('phone') :false ;
        $type = $this->request->post('type')? $this->request->post('type') :false ;  # 1 注册   2 忘记密码
        if($phone && $type){
            if((time()-Cache::get('times'.$phone))>50){
                switch ($type){
                    case 1:
                        if(!$this->checkPhone($phone)){
                            $res = $this->sends($phone);
                            if(gettype($res) == 'integer'){
                                return Func::ds('发送成功',Error::$SUCCESS);
                            }else{
                                return Func::ds('发送异常，稍后再试',Error::$CODE_SYS_ERROR , $res);
                            }
                        }else{
                            return Func::ds('用户已存在',Error::$REG_EXIST);
                        }
                        break;
                    case 2:
                        if($this->checkPhone($phone)){
                            $res = $this->sends($phone);
                            if(gettype($res) == 'integer'){
                                return Func::ds('发送成功');
                            }else{
                                return Func::ds('发送异常，稍后再试',Error::$CODE_SYS_ERROR, $res);
                            }
                        }else{
                            return Func::ds('用户不存在',Error::$REG_ONT_FOUND);
                        }
                        break;
                }
            }else{
                return Func::ds('请求频繁',Error::$OFTEN_CODE_ERROR);
            }
        }else{
            return Func::ds('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

    /**
     * 注册
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function reg()
    {
        $username = $this->request->post('username')? $this->request->post('username') :false ;
        $password = $this->request->post('password')? $this->request->post('password') :false ;
        $cid = $this->request->post('cid')? $this->request->post('cid') :false ;
        $code = $this->request->post('code')? $this->request->post('code') :false ;
        $checkPhone = Db::table('users')->find(['username'=>$username]);
        if(!empty($checkPhone))return Func::ds('用户已存在','405');
        if($username && $password && $code && $cid){
            if($this->codeCheck($username,$code)){
                $i = true;
                if(Cache::has($cid)){
                    if((time() - Cache::get($cid)) < 60){
                        $i = false;
                    }else{
                        Cache::set($cid,time());
                    }
                }
                if($i){
                    $log_type = substr(str_shuffle($this->randStr()),0,10);
                    $data = [
                        'username'    => $username,
                        'phone'       => $username,
                        'password'    => md5(md5($password)),
                        'ctime'       => time(),
                        'log_type'    => $log_type,
                        'clientid'    => $cid,
                        'school'      => 1,
                        'nickname'    => $username,
                        'uuid'        => $this->uuid()

                    ];
                    $uid = $this->table->insertGetId($data);
                    if($uid){

                        $this->kjmessageId($uid);
                        $bool = $this->addUSec($uid);
                        if(!$bool) return Func::ds('小秘书创建失败',Error::$SEC_CREATE_ERROR);
                        Cache::set($cid,time());
                        Cache::set($username.'log',$log_type);
                        $_token = $this->generate($username, $this->request->controller().$this->request->action());
                        $info = [
                            'user_id'   => $uid,
                            'tk'        => $_token,
                            'username'  => $username,
                            'nickname'  => $username,
                            'head_image'=> '',
                            'log_type'  => $log_type,
                            'sex'       => 1,
                            'grade'     => 0,
                            'school_id' => 0,
                            'real'      => 0,
                            'u_vip'     => 0,
                            'student'   => '',
                            'uuid'      => $data['uuid']
                        ];
                        Cache::set('userInfo'.$uid,$info);
                        # dump(Cache::get('userInfo'.$uid));
                        return Func::ds('注册成功',Error::$SUCCESS,$info);
                    }else{
                        return Func::ds('注册失败',Error::$REG_ERROR);
                    }
                }else{
                    return Func::ds('注册频繁',Error::$REG_OFTEN);
                }
            }else{
                return Func::ds('验证码错误',Error::$CODE_VERIFY_ERROR);
            }
        }else{
            return Func::ds('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

    /**
     * 忘记密码
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function forget()
    {
        $username = $this->request->post('phone') ? $this->request->post('phone') : false;
        $newps = $this->request->post('password')?$this->request->post('password'):false;
        $code = $this->request->post('code')? $this->request->post('code'):false;
        # $cid = $this->request->post('cid')? $this->request->post('cid'):false;

        if($username && $newps && $code){
            $checkPhone = Db::table('users')->field('user_id,username')->where('username',$username)->find();
            if(empty($checkPhone))return Func::ds('用户不存在','404');
            if($this->codeCheck($username,$code)){
                $bool = $this->table->where('username',$username)
                    ->update(['password'=>md5(md5($newps))]);
                if($bool){
                    $info = $this->table->alias('u')
                        ->field('u.user_id,u.real,us.grade,u.school school_id,u.head_image,kjmessage_id,u.clientid,u.real,u.username,u.nickname,u.u_vip,u.log_type,u.sex,u.student,u.uuid')
                        ->join('users_secretary us','us.user_id=u.user_id','left')
                        ->where('u.username',$username)
                        ->where('u.password',md5(md5($newps)))
                        ->find();
                    if($info){
                        $log_type = substr(str_shuffle($this->randStr()),0,10);
                        $_token = $this->generate($username, $this->request->controller().$this->request->action());
                        $bool = $this->table->where('user_id',$info['user_id'])->update(['ltime'=>time(),'log_type'=>$log_type]);
                        if($bool){
                            Cache::set($username.'log',$log_type);
                            $info['tk'] = $_token;
                            Cache::set('userInfo'.$info['user_id'],$info);

                            return Func::ds('修改密码成功',Error::$SUCCESS,$info);
                        }else{
                            return Func::ds('修改登录状态失败','407-1');
                        }
                    }else{
                        return Func::ds('修改密码后登录失败','407-2');
                    }
                }else{
                    return Func::ds('修改密码失败',Error::$UPD_PWD_ERROR);
                }
            }else{
                return Func::ds('验证码错误',Error::$CODE_VERIFY_ERROR);
            }
        }else{
            return Func::ds('参数未获取',Error::$PARAMETER_ERROR);
        }
    }

    /**
     * 生成用户极光id
     * @param $uid
     * @return string
     */
    private function kjmessageId($uid)
    {
        $kjmessageId = '';
        if($uid){
            $kjmessageId = $this->kjmessage.$uid;
        }
        $bool = Db::table('users')->where('user_id',$uid)->update(['kjmessage_id'=>$kjmessageId]);
        return $bool;


    }

    /**
     * 注册时生成一个小秘书
     * @param int $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function addUSec($uid = 1)
    {
        if($uid){
            $arr = ['巴鲁斯','德玛西亚','小仙女','光辉女团'];
            $call = $arr[rand(0,count($arr)-1)];
            $data = [
                'user_id' => $uid, # 绑定用户id
                'sec_name' => $call, # 小秘书昵称
                'sec_sex' => '0', # 性别
                'images' => '', # 图片
                'sec_call' => 0, # 对小秘书的称呼
                'intimate' => '0', # 亲密度
                'charm' => '0', # 魅力值
                'sec_level' => '0', # 级别
                'attack' => '0', # 攻击力 10-1000
                'fatigue' => '0', # 疲劳值
                'spirit' => '0', # 精神值
                'hunger' => '0',  # 饥饿值
                'grade' => '0',  # 等级
            ];
            $res = Db::table('users_secretary')->where('user_id',$uid)->find();
            if($res) return false;
            $row = Db::table('users_secretary')
                ->insert($data);
            if(!$row) {
                return false;
            }
            return true;
        }

    }


    /**
     * 生成token
     * @param $username
     * @param $voucher
     * @return string
     */
    private function generate($username, $voucher)
    {
        $str = $username.$voucher.$this->randStr();

        $str = md5(strtolower(trim($str)));

        $pwd = substr(md5(self::PWD),0 , 16);

        $_token = md5($str.$pwd);

        Cache::set($username.'token',$_token);

        return $_token;

    }

    /**
     * 打乱字符串
     * @return string
     */
    protected function randStr()
    {
        $str = str_shuffle('01234578QWERTYUIPL960123457896QWERTYUIPLKJHGFDSAZXCVBNM0123457896');

        return str_shuffle(sha1($str));

    }

    /**
     * 验证验证码
     * @param $phone
     * @param $code
     * @return bool
     */
    private function codeCheck($phone,$code)
    {
        if($phone && $code){
            if(Cache::get('sms_'.$phone) == $this->encryption($code)){
                Cache::rm('sms_'.$phone);
                Cache::rm('times'.$phone);
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 调用发送
     * @param $phone
     * @return bool
     */
    private function sends($phone)
    {
        $code = substr(str_shuffle($this->code),0,4);
        # 发送 ...
        $res = $this->sendType($code,$phone);
        if(gettype($res) == 'integer'){
            # 加密
            Session::set('sms_'.$phone,$this->encryption($code));
            Cache::set('sms_'.$phone,$this->encryption($code));
            Cache::set('times'.$phone,time());
            Session::set('times'.$phone,time());
            return 1;
        }else{
            return $res;
        }
    }
    /**
     * 验证码加密
     * @param $code
     * @return string
     */
    protected function encryption($code)
    {
        if($code){
            $code = md5(strtolower(trim($code)));
            return $code;
        }

    }

    /**
     * 验证用户是否存在
     * @param string $phone
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function checkPhone($phone)
    {
        if($phone){
            $info = $this->table
                ->where('username',$phone)
                ->find();
            # dump($info);
            if($info){
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * 切换短信验证码接口
     * @param $code
     * @param $phone
     * @param int $type
     * @return bool|false|string
     */
    private function sendType($code, $phone, $type = 1)
    {
        switch ($type){
            case 1:
                $sms = new Ypsms();
                $status = $sms->send($code,$phone);
                $status = json_decode($status,true);
                if(!$status['code']){
                    return 1;
                }else{
                    return $status;
                }
                break;
            case 2:
                $sms = new SendSms();
                $status = $sms->send($code, $phone);
                if(stripos(json_encode($status),'OK')){
                    return 1;
                }else{
                    return $status;
                }
                break;
            default:
                break;

        }
    }


}
