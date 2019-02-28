<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:18
 */

namespace app\common\lib;


class Fsock{
    public function asyncExecute($url, $post_data = array(), $cookie = array()) {
        ignore_user_abort(1);

        $method = "POST";
        $url_array = parse_url($url);
        $port = isset($url_array['port']) ? $url_array['port'] : 80;

        $fp = fsockopen($url_array['host'], $port, $errno, $errstr, 30);
        if (!$fp) {
            return FALSE;
        }
        $getPath = isset($url_array['path']) ? $url_array['path'] : '/';
        if (isset($url_array['query'])) {
            $getPath .= "?" . $url_array['query'];
        }
        if (!empty($post_data)) {
            $method = "POST";
        }
        $header = $method . " /" . $getPath;
        $header .= " HTTP/1.1\r\n";
        $header .= "Host: " . $url_array['host'] . "\r\n";

        $header .= "Connection: Close\r\n";
        if (!empty($cookie)) {
            $_cookie = strval(NULL);
            foreach ($cookie as $k => $v) {
                $_cookie .= $k . "=" . $v . "; ";
            }
            $cookie_str = "Cookie: " . base64_encode($_cookie) . " \r\n";
            $header .= $cookie_str;
        }
        if (!empty($post_data)) {
            $_post = strval(NULL);
            $atComma = '';
            foreach ($post_data as $k => $v) {
                $_post .= $atComma . $k . "=" . $v;
                $atComma = '&';
            }
            $post_str = "Content-Type: application/x-www-form-urlencoded\r\n";
            $post_str .= "Content-Length: " . strlen($_post) . "\r\n";
            $post_str .= "\r\n".$_post . "\r\n";
            $header .= $post_str;
        }
        $header .= "\r\n";
        fwrite($fp, $header);
        fclose($fp);
        return true;
    }
}