<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/2/21
 * Time: 10:45 AM
 */

namespace app\gzh\controller;


use PHPMailer\PHPMailer\PHPMailer;
use think\Cache;
use think\Controller;
use think\Request;

class Com extends Controller
{
    private $saveDir = 'uploads'.DS.'aboards'.DS;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $uid = $this->request->param('u')?$this->request->param('u'):false;


        $rbac = $this->request->controller().'/'.$this->request->action();
        if((strtolower($rbac)=='recruit/index')){
            $uid = Cache::get('uid');
        }
        $info = Cache::get('user_'.$uid);
        if((!$uid && (empty($info) || $info == 'null'))){
            $this->success('请先登陆',url('login/index'));
        }
    }


    /**
     * 图片上传
     * @param $fileData
     * @param null $dir
     * @return array|bool
     */
    protected function uploads($fileData,$dir = null){
        $path = [];
        if(!$fileData) return false;
        if(strtolower(gettype($fileData)) == strtolower('object')){
            $res = $fileData->validate(['ext'=>'jpg,png,jpeg'])->move(config('uploadPath'));
            if($res){
                $path[] = $this->saveDir.$res->getSaveName();
            }
        }else{
            foreach($fileData as $item){
                $res = $item->validate(['ext'=>'jpg,png,jpeg'])->move(config('uploadPath'));
                if($res){
                    $path[] = $this->saveDir.$res->getSaveName();
                }
            }
        }
        return $path;
    }


}