<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/2/15
 * Time: 1:58 PM
 */

namespace app\common\lib;

/**
 * 分离出其他配置项-做扩展
 *
 * Class Conf
 * @package app\common\lib
 */

class Conf{
    public function __construct()
    {

    }

    public function get($name)
    {
        return $this->conf($name);

    }
    private function conf($name){


        $res = $this->confInfo();

        return $res[$name];
    }

    private function confInfo(){

        return [

            'only'  => 'u_only_id',     # 唯一uuid 非靓号 缓存 键名
            'rule'  => 'u_rule_id',     # 唯一uuid  缓存 靓号 键名

        ];
    }
}