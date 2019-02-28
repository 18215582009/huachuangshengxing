<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019.01.09
 * Time: 10:35
 */
namespace app\common\lib;

use think\Response;

class Func{
    public static function ds($msg, $status = 200, $arr = null,$total=null, $type = 1)
    {
        $data = [
            'status'    => $status,
            'msg'       => $msg,
            'data'      => $arr,
            'total'     => $total,
        ];
        $data = array_filter($data);
        return Response::create($data,'json');

    }
}

function ds($msg, $status = 200, $data = null,$total=null, $type = 1)
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

