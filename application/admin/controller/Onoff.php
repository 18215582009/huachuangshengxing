<?php
namespace app\admin\controller;

use think\Db;
//管理后台-开关管理
class Onoff extends Base
{
    //学校开关、路径
    protected function getSchoolPath(){
        return dirname(dirname(dirname(dirname(__DIR__)))).DS.'gatewayworker'.DS.'Applications'.DS.'YourApp'.DS.'school_id.txt';//这里写绝对路径，不然linux识别不了
    }
    //开关列表
    public function index(){
    	$data = [];
    	$school_path = $this->getSchoolPath();
    	if (file_exists($school_path)) {
    		$data['school_lock'] = file_get_contents($school_path);
    	}
    	else{
    		$data['school_lock'] = -1;
    	}
    	$this->assign('data',$data);
        return $this->fetch();
    }
    //学校开关、关闭
    public function closeschool(){
    	if ($this->request->method() == "POST") {
    		if ( !isset($_GET['v']) || $_GET['v']!='hcsxschoollockclose' ) {
	    		$this->echojson(201,'参数错误');
	    	}
	    	$school_path = $this->getSchoolPath();
	    	if (file_exists($school_path)) {
	    		file_put_contents($school_path, 0);
	    		$this->echojson(200,'关闭成功');
	    	}
	    	else{
	    		$this->echojson(201,'关闭失败，开关文件不存在');
	    	}
    	}
    }
    //学校开关、开启
    public function openschool(){
    	if ($this->request->method() == "POST") {
    		if ( !isset($_GET['v']) || $_GET['v']!='hcsxschoollockopen' ) {
	    		$this->echojson(201,'参数错误');
	    	}
	    	$school_path = $this->getSchoolPath();
	    	if (file_exists($school_path)) {
	    		file_put_contents($school_path, 1);
	    		$this->echojson(200,'开启成功');
	    	}
	    	else{
	    		$this->echojson(201,'开启失败，开关文件不存在');
	    	}
    	}
    }
}