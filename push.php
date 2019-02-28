<?php
use Workerman\Worker;
require_once './vendor/workerman/workerman/Autoloader.php';
// 初始化一个worker容器，监听8081端口
$worker = new Worker('websocket://0.0.0.0:8081');

/*
 * 注意这里进程数必须设置为1，否则会报端口占用错误
 * (php 7可以设置进程数大于1，前提是$inner_text_worker->reusePort=true)
 */
$worker->count = 1;
// worker进程启动后创建一个text Worker以便打开一个内部通讯端口
$worker->onWorkerStart = function($worker)
{
    // 开启一个内部端口，方便内部系统推送数据，Text协议格式 文本+换行符
    $inner_text_worker = new Worker('text://0.0.0.0:8082');
    $inner_text_worker->onMessage = function($connection, $buffer)
    {
        // $data数组格式，里面有uid，表示向那个uid的页面推送数据
        $data = json_decode($buffer, true);
        if (isset($data['uid'])) {
            $uid = $data['uid'];
            // 通过workerman，向uid的页面推送数据
            $ret = sendMessageByUid($uid, $buffer);
            // 返回推送结果
            $connection->send($ret ? 'ok' : 'fail');
        }
        else{
            broadcast($buffer);
        }
    };
    // ## 执行监听 ##
    $inner_text_worker->listen();
};
// 新增加一个属性，用来保存uid到connection的映射
$worker->uidConnections = array();
// 当有客户端发来消息时执行的回调函数
$worker->onMessage = function($connection, $data)
{
    global $worker;
    //发送来的数据包不是json格式，断开连接
    // if (!json_validate($data)) {
    //     $connection->close();
    // }
    // //json转数组
    // $data = json_decode($data,true);
    // //fuid发送人的uid，ftk发送人的token，fda发送人发送的消息，tuid接收消息的人的uid，ttk接收消息的人的token
    // //type消息类型：
    // //验证数组格式、多了少了都、断开连接
    // if (array_keys($data) != ['fuid','ftk','fda','tuid','ttk','type']) {
    //    $connection->close();
    // }
    //验证发送人、接收人是否合法

    // 判断当前客户端是否已经验证,既是否设置了uid
    if(!isset($connection->uid))
    {
       // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
       $connection->uid = $data;
       /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
        * 实现针对特定uid推送数据
        */
       $worker->uidConnections[$connection->uid] = $connection;
       return;
    }
    broadcast($data);
};

// 当有客户端连接断开时
$worker->onClose = function($connection)
{
    global $worker;
    if(isset($connection->uid))
    {
        // 连接断开时删除映射
        unset($worker->uidConnections[$connection->uid]);
    }
};

// 向所有验证的用户推送数据
function broadcast($message)
{
   global $worker;
   foreach($worker->uidConnections as $connection)
   {
        $connection->send($message);
   }
   return true;
}

// 针对uid推送数据
function sendMessageByUid($uid, $message)
{
    global $worker;
    if(isset($worker->uidConnections[$uid]))
    {
        $connection = $worker->uidConnections[$uid];
        $connection->send($message);
        return true;
    }
    return false;
}
//判断接收的数据包、是否是标准的json格式
function json_validate($string) {
    if (is_string($string)) {
        @json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
    return false;
}
//针对接收的数据包做判断
function checkdata($data){

}
// 运行所有的worker
Worker::runAll();