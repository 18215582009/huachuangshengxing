<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/24
 * Time: 9:39 AM
 */

namespace app\api\controller;



use app\common\error\Error;
use think\Db;

class Journal extends Base
{
    protected $jurisdiction = [];                    # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）


    /**
     * 崩溃日志记录
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function journalRecord()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $comefrom = $this->request->post('comefrom')?$this->request->post('comefrom'):false;
            $content = $this->request->post('content')?$this->request->post('content'):false;
            $phone_model = $this->request->post('phone_model')?$this->request->post('phone_model'):false;

            if($uid && $comefrom && $content && $phone_model){

                $data = [
                    'user_id'       => $uid,
                    'comefrom'      => $comefrom,
                    'content'       => $content,
                    'phone_model'   => $phone_model,
                    'create_time'   => date('Y-m-d H:i:s')
                ];
                $bool = Db::table('crash_log')->insert($data);
                if($bool) return $this->json('记录成功',Error::$SUCCESS);
                else return $this->json('记录失败',Error::$INSERT_ERROR);

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }

        }


    }

}