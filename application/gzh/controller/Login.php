<?php

/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/2/18
 * Time: 9:39 AM
 */
namespace app\gzh\controller;

use sms\Ypsms;
use think\Cache;
use think\Controller;
use think\Db;

class Login extends Controller
{
    private $saveDir = 'uploads'.DS.'aboards'.DS;
    public function index()
    {
        return view();

    }

    /**
     * @return string|\think\response\View
     *
     *
     *
     */
    public function edit()
    {

        $phone = $this->request->get('p');
        if($this->request->isAjax()){
            if($this->request->isPost()){
                $post = $this->request->post();
                $files = $this->request->file('head');
                if($files){
                    $head = $this->uploads($files);
                    $post['head_image'] = $head[0];
                }
                $post = array_filter($post);
                $post['user_birth'] = strtotime($post['user_birth']);

                $school = $post['school'];
                unset($post['school']);
                $sid = Db::table('school')->field('id')->where('sname',$school)->find();
                if($sid){
                    $post['school'] = $sid['id'];
                }else{
                    $id = Db::table('school')->insertGetId(['sname'=>$school]);
                    $post['school'] = $id;
                }
                $post['username'] = $post['phone'];
                $data = ['花生酱','小希希','悦来剑客','霞飞党'];
                $name = $data[mt_rand(0,count($data))];
                $post['nickname'] = $name;
                $post['password'] = md5(md5(123456));
                $row = Db::table('users')->insertGetId($post);
                if($row){
                    return json_encode(['status'=>200,'uid'=>$row]);
                }else{
                    return json_encode(['status'=>201]);
                }
            }
        }
        $this->assign('phone',$phone);
        return view();

    }

    /**
     * 图片上传
     * @param $fileData
     * @param null $dir
     * @return array|bool
     */
    private function uploads($fileData,$dir = null){
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

    /**
     * 登陆
     * @return string
     */
    public function logins()
    {
        if($this->request->isAjax()){
            $phone = $this->request->post('username')?$this->request->post('username'):false;
            $code = $this->request->post('code')?$this->request->post('code'):false;

            if($phone && $code){

                $bool = $this->verify($phone,$code);
                # 验证 验证码是否正确
                #if(!$bool)return 201; // 验证码错误

                $res = Db::table('users')
                    ->field('user_id,username,email,head_image,nickname,college,sex')
                    ->where('username',$phone)->find();
                if($res){
                    Cache::set('uid',$res['user_id']);
                    Cache::set('user_'.$res['user_id'],json_encode($res,JSON_UNESCAPED_UNICODE));
                    return json_encode(['status'=>200,'uid'=>$res['user_id']]);
                }else{
                    return json_encode(['status'=>202]);
                }
            }


        }
    }
    public function guide()
    {
        $phone = $this->request->get('p');
        $this->assign('phone',$phone);
        return view();
    }

    /**
     * 发送验证码
     * @return int
     */
    public function send()
    {
        if($this->request->isAjax()){
            $phone = $this->request->post('username');
            if($phone){
                $code = mt_rand(1000,9999);
                $sms = new Ypsms();
                $status = $sms->send($code,$phone);
                $status = json_decode($status,true);
                if(!$status['code']){
                    Cache::set('sms_'.$phone,$code);
                    return 200;
                }else{
                    return 201;
                }

            }
        }
    }

    /**
     * 验证验证码
     * @param $phone
     * @param $code
     * @return bool
     */
    private function verify($phone,$code)
    {
        $new = Cache::get('sms_'.$phone);
        if($code == $new){
            Cache::rm('sms_'.$phone);
            return true;
        }else{
            return false;
        }

    }


}