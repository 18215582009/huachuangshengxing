<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/31
 * Time: 11:06 AM
 */

namespace app\api\controller;


use app\common\error\Error;
use app\common\lib\Txcos;

class Obtaink extends Base
{
    protected $jurisdiction = [];                    # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）

    /**
     * 获取云存储临时密钥
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function getTxyCos()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            if($uid){
                $cos = new Txcos();
                $key = $cos->tempKeys();
                if(isset($key['code'])){
                    return $this->json('获取出错',Error::$OBTAIN_ERROR);
                }else{
                    return $this->json('获取成功',Error::$SUCCESS,$key);
                }
            }
        }

    }

}