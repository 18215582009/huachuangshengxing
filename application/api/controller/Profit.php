<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/8
 * Time: 1:27 PM
 */

namespace app\api\controller;


use app\common\error\Error;
use think\Db;

class Profit extends Base
{
    protected $jurisdiction = [];  # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    private $num = 10;
    /**
     * 获取用户收益信息
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function allProfit()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        if($uid){
            #$curProfit = Db::table('users')->field('cur_profit')->find($uid);  # 获取当前收益金额
            $res = Db::table('profit')->field('id,p_money')->where('user_id',$uid)->select(); # 总收益
            $result = Db::table('carry')->field('id,money')->where('user_id',$uid)->select(); # 总提现
            $proCont = array_sum(array_column($res,'p_money'));
            $carCont = array_sum(array_column($result,'money'));
            if(true){
                $data = [
                    'cur_profit' => $proCont-$carCont,
                    'proCount' => $proCont,
                    'carCount' => $carCont,
                ];
                return $this->json('获取成功',Error::$SUCCESS,$data);
            }else{
                return $this->json('获取失败',Error::$OBTAIN_ERROR);
            }

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }


    /**
     * 获取收益记录
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function getProfit()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $page = $this->request->post('pages')?$this->request->post('pages'):1; # 页码
        $type = $this->request->post('type')?$this->request->post('type'):false;
        if($uid && $page && $type){
            $start = ($page - 1)*10;
            $table = $field = '';
            switch($type){
                case 1: # 获取收益记录
                    $table = 'profit';
                    $field = 'id profit_id,content,ctime,p_money';
                    break;
                case 2: # 获取提现记录
                    $table = 'carry';
                    $field = 'id carry_id,type,handle_id,status,h_time,ctime,money';
                    break;

            }
            $res = Db::table($table)->field($field)
                ->where('user_id',$uid)
                ->limit($start,$this->num)
                ->order('ctime','desc')
                ->select();
            $count = Db::table($table)->field($field)
                ->where('user_id',$uid)
                ->order('ctime','desc')
                ->count();
            if($res){
                return $this->json('获取成功',Error::$SUCCESS,$res,ceil($count/$this->num));
            }else{
                return $this->json('没有获取到数据',Error::$OBTAIN_ERROR);
            }

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

    /**
     * 获取收益或者提现详情
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function apInfo()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $apId = $this->request->post('ap_id')?$this->request->post('ap_id'):false;
        $type = $this->request->post('type')?$this->request->post('type'):false;
        if($uid && $apId && $type){
            $table = $field = '';
            switch($type){
                case 1: # 获取收益记录详情
                    $table = 'profit';
                    $field = 'id profit_id,content,ctime,p_money';
                    break;
                case 2: # 获取提现记录详情
                    $table = 'carry';
                    $field = 'id carry_id,type,handle_id,status,h_time,ctime,money';
                    break;

            }
            $res = Db::table($table)->field($field)
                ->where('user_id',$uid)
                ->order('ctime','desc')
                ->find($apId);
            if($res){
                return $this->json('获取成功',Error::$SUCCESS,$res);
            }else{
                return $this->json('没有获取到数据',Error::$OBTAIN_ERROR);
            }

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

}