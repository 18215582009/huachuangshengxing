<?php
//长连接内部专用api

namespace app\api\controller;

use think\Db;
use think\Cache;

class Increment extends Base
{
    protected $jurisdiction = ['checklogin','getNumber','updateNum'];
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    public function __construct()
    {
        parent::__construct();
        $v = $this->request->get('v')?$this->request->get('v'):false;//获取签名
        if ( $v != 'hcsxichoolwescoket' )  return 404;//验证签名
    }
    //验证登录
    public function checklogin(){
        $token = $this->request->param('tk');
        $uid = $this->request->param('uid');
        $st = Cache::get('userInfo'.$uid)['tk'];
        if(!($token == $st) || !$token || !$st){
            return 404;
        }
        return 200;
    }
    //查询用户秒配总次数
    public function getNumber()
    {
        $uid = $this->request->get('uid')?$this->request->get('uid'):false;
        if (!$uid)  return 404;
        $find = Db::table('users_increment')->field('incre_total,incre_last,incre_used')->where('user_id',$uid)->find();
        if (!empty($find) && $find['incre_last'] > 0) {
            return json_encode($find);
        }else{
            return 404;
        }
    }
    //扣除用户秒配次数
    public function updateNum(){
        $uid = $this->request->get('uid')?$this->request->get('uid'):false;
        $incre_total = $this->request->get('incre_total')?$this->request->get('incre_total'):false;
        $incre_last = $this->request->get('incre_last')?$this->request->get('incre_last'):false;
        $incre_used = isset($_GET['incre_used'])?$_GET['incre_used']:false;
        if (!$uid)  return 404;
        if (!$incre_total || $incre_total < 1)  return 404;
        if (!$incre_last  || $incre_last < 1)  return 404;
        if (!is_numeric($incre_used) )  return 404;
        Db::startTrans();
        try{
            $update = Db::table('users_increment')
            ->where('user_id',$uid)
            ->where('incre_total',$incre_total)
            ->where('incre_last',$incre_last)
            ->where('incre_used',$incre_used)
            ->update([
                'incre_last'=>$incre_last-1,
                'incre_used'=>$incre_used+1,
            ]);
            $insert = Db::table('users_consume')->insert(['user_id'=>$uid,'type'=>2,'sum'=>1,'ctime'=>time()]);
            // 提交事务
            Db::commit();
            return 200;  
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return 404;
        }
    }
}
