<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/28
 * Time: 10:21 AM
 */

namespace app\api\controller;
use app\common\error\Error;
use think\Db;


/**
 * 系统通知
 * Class Sysnotice
 * @package app\api\controller
 */

class Sysnotice extends Base
{
    protected $jurisdiction = [];                    # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）

    /**
     * 获取系统通知
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function obtainNotice()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $time = $this->request->post('times')?$this->request->post('times'):1;
            if($uid && $time){
                switch($time){
                    case 1:
                        $time = (time()-604800);
                        break;
                }

                $sql = "select sp.id sys_id,sp.title,sp.content,FROM_UNIXTIME(sp.ctime,'%Y/%m/%d') ctime from sys_push sp LEFT JOIN users u ON u.school=sp.school_id WHERE id not in(SELECT sys_id from sys_user_push WHERE user_id={$uid}) and u.user_id={$uid} and sp.ctime>{$time} ORDER BY sp.ctime DESC";

                $res = Db::query($sql);

                if($res){
                    return $this->json('获取成功',Error::$SUCCESS,$res);
                }else{
                    return $this->json('没有获取到数据',Error::$OBTAIN_ERROR);
                }

            }

        }
    }

    /**
     * @throws \think\Exception
     */
    public function sysHide()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $sys_id = $this->request->post('sys_id')?$this->request->post('sys_id'):false;
            $times = $this->request->post('times')?$this->request->post('times'):false;
            if($uid && ($sys_id || $times)){
                $bool = '';
                if($sys_id){
                    $res = Db::table('sys_user_push')->where('sys_id',$sys_id)
                        ->where('user_id',$uid)->where('status',1)
                        ->find();
                    if($res){
                        $data = [
                            'utime'     => time(),
                            'status'    => 1
                        ];
                        $bool = Db::table('sys_user_push')->where('sys_id',$sys_id)->where('user_id',$uid)->update($data);
                    }else{
                        $data = [
                            'utime'     => time(),
                            'status'    => 1,
                            'user_id'   => $uid
                        ];
                        $bool = Db::table('sys_user_push')->insert($data);
                    }
                }
                if($times){

                }
            }
        }

    }

}