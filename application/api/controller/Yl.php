<?php
/**
 * 语聊
 * User: smallseven
 * Date: 2019/2/14
 * Time: 1:28 PM
 */

namespace app\api\controller;


use app\common\error\Error;

class Yl extends Base
{
    protected $jurisdiction = [];                    # 不需要token 验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外)

    /**
     * 获取语聊在线人数
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function curNumber()
    {
        $num = mt_rand(1000,2000);

        $data = [
            'upline'    => $num
        ];
        return $this->json('获取成功',Error::$SUCCESS,$data);

    }

}