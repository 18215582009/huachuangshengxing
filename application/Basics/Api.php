<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/11
 * Time: 20:39
 */

namespace app\Basics;


use think\Cache;
use think\Controller;
use think\Response;
use think\session\driver\Redis;

/**
 *
 * Class Api
 * @package app\Basics
 */
class Api extends Controller
{
    protected $_school = 1; # 用户当前所在学校 1 # 开放省份限制 2 # 开放所有 3
    protected $client = null;
    protected $saveDir = '';  #     图片存储位置
    protected $url = '';

    public function __construct()
    {
        parent::__construct();
        $this->saveDir = 'uploads'.DS.'aboards'.DS;
        $this->url = '148.70.73.177';

    }
    /**
     * 检测是否是正常的时间戳
     * @param $timestamp
     * @return bool
     */
    protected function is_timestamp($timestamp) {
        $date = date('Y-m-d H:i:s',$timestamp);
        if(strtotime($date) == $timestamp) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * json格式
     * @param $msg
     * @param int $status
     * @param array $arr
     * @param null $total
     * @return Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    protected function json($msg ,$status=200 ,$arr=[],$total=null){
        $data = [];
        if(is_array($msg)){
            $data = $msg;
        }
        if(is_string($msg)){
            $data = [
                'status'    => $status,
                'msg'       => $msg,
                'data'      => $arr,
                'total'     => $total,
            ];
        }
        $data = array_filter($data);
        return Response::create($data,'json');
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

