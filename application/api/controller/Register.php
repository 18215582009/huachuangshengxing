<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/9
 * Time: 9:53 AM
 */

namespace app\api\controller;

# 用户签到

class Register extends Base
{
    protected $jurisdiction = [];  # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）

    public function getRegister()
    {

        
    }

}