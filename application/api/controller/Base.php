<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/11
 * Time: 20:44
 */

namespace app\api\controller;


use app\Basics\Api;
use think\Cache;
use think\Request;
use think\Response;


class Base extends Api
{
    protected $jurisdiction = [];   # 定义不需要进行验证的方法接口

    protected $_check = true;          # 验证后的结果  true | false
    protected $_realCheck = true;      # 实名认证与非实名认证的权限控制  true | false

    public function __construct(Request $request = null)
    {
        parent::__construct();
        //(new Ws())->connect();exit;
        if(!$this->_check){
            echo $this->ds('token验证失败',501);exit;
        }

        if(!$this->_realCheck){
            #echo $this->ds('实名认证后才能访问',503);exit;
        }
    }

    /**
     * 验证token
     */
    protected function limitAction()
    {
        $action = $this->request->action();
        $jur = $this->jurisdiction;
        foreach($jur as $k=>$item){
            $jur[$k] = strtolower($item);
        }
        $action = strtolower($action);
        if(empty($jur) || !in_array($action,$jur)){
            $_token = $this->request->param('tk');
            $uid = $this->request->param('user_id');
            $st = Cache::get('userInfo'.$uid)['tk'];
            if(!($_token == $st) || !$_token || !$st){
                $this->_check = false;
            }
        }
    }

    /**
     * 实名认证权限控制
     * @return false|string
     */
    protected function limitCheck()
    {
        $uid = $this->request->param('user_id');
        if(Cache::has('userInfo'.$uid)){
            $real = Cache::get('userInfo'.$uid)['real'];
            switch ($real){
                case 0:
                    $ac = $this->request->action();
                    $ac = strtolower(substr($ac,0,4));
                    if($ac != 'show'){
                        $this->_realCheck = false;
                    }
                    break;
            }
        }else{
            return $this->ds('无法获取用户信息',307);
        }
    }

    private function ds($msg, $status = 200, $data = null,$total=null, $type = 1)
    {
        $jsonStr = '';
        $array = [
            'status' => $status,
            'data'   => $data,
            'total'  => $total,
            'msg'    => $msg
        ];
        $array = array_filter($array);
        switch ($type){
            case 1 :
                $jsonStr = json_encode($array, JSON_UNESCAPED_UNICODE);
                break;
            case 2 :
                $jsonStr = json_encode($array);
        }
        header('Content-type: application/json');
        return $jsonStr;

    }



}
