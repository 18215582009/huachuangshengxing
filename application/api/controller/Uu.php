<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/2/15
 * Time: 11:57 AM
 */

namespace app\api\controller;


use app\common\lib\Conf;
use think\Cache;
use think\Controller;

/**
 * 主要用生成用户uuid，过滤靓号存储起来，暂时存在缓存中，后续做其他修改，存放在数据库或者redis
 *
 * Class Uu
 * @package app\api\controller
 */
class Uu extends Controller
{
    private $conf;
    public function __construct()
    {
        parent::__construct();
        $this->conf = new Conf();
        $this->onlyId();
    }

    /**
     * 获取唯一id
     * @return mixed
     */
    protected function uuid(){
        if(Cache::has($this->conf->get('only'))){
            $rows = json_decode(Cache::get($this->conf->get('only')),true);
            if($rows){
                $uuid = array_rand($rows);

                unset($rows[$uuid]);

                Cache::set($this->conf->get('only'),json_encode($rows));
                return $uuid;
            }

        }
    }
    /**
     * 预先生成2万的惟一编号id （并保留靓号）
     *
     * @param int $start
     * @param int $num
     */
    private function onlyId($start = 123456,$num = 323456)
    {
        if(!Cache::has($this->conf->get('only'))){
            $n = []; # 不是靓号
            $y = []; # 靓号
            for($i=$start;$i<$num;$i++){
                if($this->prettyRule($i)){
                    $y[$i] = $i;
                }else{
                    $n[$i] = $i;
                }
            }
            Cache::set($this->conf->get('only'),json_encode($n));
            Cache::set($this->conf->get('rule'),json_encode($y));
        }

    }

    /**
     * 过滤靓号
     *
     *
     * @param $number
     * @return bool
     */
    private function prettyRule($number)
    {
        $number = strval($number);
        $len = strlen($number);
        $n = [];
        for($i=0;$i<$len;$i++){
            $n[] = $number[$i];
        }
        if($n){
            $rs = $n;
            sort($rs);
            $str = join('',$rs);
            if($number == $str){ # 过滤6位连号 顺
                return true;
            }
            rsort($rs);
            $str = join('',$rs);
            if($number == $str){ # 过滤6位连号 倒
                return true;
            }
            $rs = $n;
            $rs = array_unique($rs);
            if(count($rs)<=3){ # 过滤有3位以上在一起相同的数字组合
                foreach($rs as $v){
                    $v = $v.$v.$v;
                    $num = 'a'.$number;
                    if(stripos($num,$v)){
                        return true;
                    }
                }

            }
            return false;
        }


    }

}