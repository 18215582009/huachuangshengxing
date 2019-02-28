<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/31
 * Time: 10:54 AM
 */

namespace app\common\lib;

/**
 *
 * Class 腾讯云  cos对象存储
 * @package app\common\lib
 */
class Txcos
{

    /**
     * 提供对外使用的接口
     * @return mixed|string
     */
    public function tempKeys()
    {
        return $this->getTempKeys();

    }

    /**
     * 使用curl 获取签名密钥
     * @return mixed|string
     */
    private function getTempKeys()
    {
        $config = $this->paras();

        $ShortBucketName = substr($config['bucket'],0, strripos($config['bucket'], '-'));
        $AppId = substr($config['bucket'], 1 + strripos($config['bucket'], '-'));
        $policy = array(
            'version'=> '2.0',
            'statement'=> array(
                array(
                    'action'=> $config['allowActions'],
                    'effect'=> 'allow',
                    'principal'=> array('qcs'=> array('*')),
                    'resource'=> array(
                        'qcs::cos:' . $config['region'] . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/' . $config['allowPrefix']
                    )
                )
            )
        );

        $policyStr = str_replace('\\/', '/', json_encode($policy));
        $Action = 'GetFederationToken';
        $Nonce = rand(10000, 20000);
        $Timestamp = time();
        $Method = 'POST';

        $params = array(
            'Region'=> 'gz',
            'SecretId'=> $config['secretId'],
            'Timestamp'=> $Timestamp,
            'Nonce'=> $Nonce,
            'Action'=> $Action,
            'durationSeconds'=> $config['durationSeconds'],
            'name'=> 'cos',
            'policy'=> urlencode($policyStr)
        );
        $params['Signature'] = $this->getSignature($params, $config['secretKey'], $Method);

        $url = $config['url'];
        $ch = curl_init($url);
        $config['proxy'] && curl_setopt($ch, CURLOPT_PROXY, $config['proxy']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->json2str($params));
        $result = curl_exec($ch);
        if(curl_errno($ch)) $result = curl_error($ch);
        curl_close($ch);

        $result = json_decode($result, 1);
        if (isset($result['data'])) {
            $result = $result['data'];
            $result['startTime'] = $result['expiredTime'] - $config['durationSeconds'];
        }

        return $result;

    }

    /**
     * 将数据装入一个二进制 字符串
     * @param $data
     * @return string
     */
    private function _hex2bin($data) {
        $len = strlen($data);
        return pack("H" . $len, $data);
    }


    // obj 转 query string
    private function json2str($obj, $notEncode = false) {
        ksort($obj);
        $arr = array();
        foreach ($obj as $key => $val) {
            array_push($arr, $key . '=' . ($notEncode ? $val : rawurlencode($val)));
        }
        return join('&', $arr);
    }

    // 计算临时密钥用的签名
    private function getSignature($opt, $key, $method) {
        $config = $this->paras();
        $formatString = $method . $config['domain'] . '/v2/index.php?' . $this->json2str($opt, 1);
        $sign = hash_hmac('sha1', $formatString, $key);
        $sign = base64_encode($this->_hex2bin($sign));
        return $sign;
    }


    /**
     * 参数配置
     * @return array
     */
    private function paras(){
        return array(
            'url' => 'https://sts.api.qcloud.com/v2/index.php',
            'domain' => 'sts.api.qcloud.com',
            'proxy' => '',
            'secretId' => 'AKIDNR6Y0H1ilbR7kDFDVZg5f2W2ueR7AvnP', // 固定密钥
            'secretKey' => 'aZdTUav1q6nFExhIkwFSPW8lHm2Teibd', // 固定密钥
            'bucket' => 'ischool-1258260532', // 换成你的 bucket
            'region' => 'ap-chengdu', // 换成 bucket 所在园区
            'durationSeconds' => 1800, // 密钥有效期
            'allowPrefix' => '*', // 必填，这里改成允许的路径前缀，这里可以根据自己网站的用户登录态判断允许上传的目录，例子：* 或者 a/* 或者 a.jpg
            // 密钥的权限列表
            'allowActions' => array(
                // 所有 action 请看文档 https://cloud.tencent.com/document/product/436/31923
                // 简单上传
                'name/cos:PutObject',
                // 分片上传
                'name/cos:InitiateMultipartUpload',
                'name/cos:ListMultipartUploads',
                'name/cos:ListParts',
                'name/cos:UploadPart',
                'name/cos:CompleteMultipartUpload'
            )
        );
    }

}