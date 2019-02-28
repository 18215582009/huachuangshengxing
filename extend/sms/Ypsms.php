<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 16:43
 */

namespace sms;
#  云片
class Ypsms
{
    private $apikey = '0ebc0e442cf15cebb5db2af4bf7452b9';
    private $txet = "【华创盛星】您的验证码是()";
    private $ch;
    public function __construct()
    {
        $this->ch = curl_init();
        $this->_init($this->ch);
    }

    /**
     * curl前置参数设置
     * @param $ch
     */
    private function _init($ch){
        /* 设置验证方式 */
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8',
            'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    }
    /*
     * 开始请求接口
     */
    private function curl_post(Array $data,$url = 'https://sms.yunpian.com/v2/sms/single_send.json')
    {
        curl_setopt ($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($this->ch);
        $error = curl_error($this->ch);
        return $this->checkErr($result,$error);
    }
    public function send($code, $phone){
        $text = str_replace('()',$code,$this->txet);
        $data = ['text'=>$text,'apikey'=>$this->apikey,'mobile'=>$phone];
        return $this->curl_post($data);
    }
    /**
     * 处理错误
     * @param $result
     * @param $error
     * @return string
     */
    private function checkErr($result, $error)
    {
        if($result === false)
        {
            return 'Curl error: ' . $error;
        }else{
            return $result;
        }
    }

}