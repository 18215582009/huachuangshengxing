<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/17
 * Time: 9:43
 */

namespace app\api\controller;



use app\common\error\Error;
/**
 * Class Banner   # 上船 --
 * @package app\api\controller
 */
class Banner extends Base
{
    protected $table = '';
    protected $jurisdiction = ['showBanner'];
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    public function __construct()
    {
        parent::__construct();
        $this->table = new \app\common\model\Banner();
    }

    /**
     * 获取banner图
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function showBanner()
    {
        $number = $this->request->post('number')?$this->request->post('number'):-1; // 接收获取多少张图片
        $type = $this->request->post('type')?$this->request->post('type'):1;     //1代表首页  2代表上传模块
        if($number){
            $rows = [];
            if($number == '-1'){
                $rows = $this->table->field('banner_id,url')
                    ->where('type',$type)
                    ->where('status',1)
                    ->order('banner_id','desc')
                    ->select();
            }
            if(($number != '-1') && $number){
                $rows = $this->table->field('banner_id,url')
                    ->where('type',$type)
                    ->where('status',1)
                    ->order('banner_id','desc')
                    ->limit(0,$number)
                    ->select();
            }
            #dump($rows);exit;
            if($rows){
                foreach ($rows as $key=>$item) {
                    #$rows[$key]['url'] = $item;
                }
                return $this->json('获取成功',Error::$SUCCESS , $rows);
            }else{
                return $this->json('获取轮播图失败',Error::$OBTAIN_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }


}
