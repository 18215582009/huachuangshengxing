<?php
namespace app\admin\controller;

use think\Db;
use JPush\Client as JPush2;
//激光
class Jpush extends Base{
    private $app_key = 'edfae6d3242e204fb0252115';
    private $app_secret = '6b80bf874b43b22b1b60afae';
    //列表
    public function index(){
    	$client = new JPush2($this->app_key, $this->app_secret);
    	$client->push()
        ->setPlatform('all')
        ->addAllAudience()
        ->setNotificationAlert('Hello, JPush')
        ->send();
    }
}