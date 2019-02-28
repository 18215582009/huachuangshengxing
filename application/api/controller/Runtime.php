<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/24
 * Time: 6:07 PM
 */

namespace app\api\controller;


use think\Cache;

class Runtime extends Base
{
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    /**
     * 清除用户点击活动统计信息
     */
    public function stu()
    {
        $uid = $this->request->post('user_id');

        Cache::rm('st'.$uid);
    }

}