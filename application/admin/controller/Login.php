<?php
namespace app\admin\controller;

use think\Controller;
use think\Session;
use think\Db;
//登录、退出
class Login extends Controller
{
    //登录
    public function login()
    {
    	if (!empty(Session::get('admin'))) {
    		$this->redirect('index/index');
    	}
    	$method = $this->request->method();
    	//登录页面
    	if ($method == "GET") {
    		return $this->fetch();
    	}
    	//登录判断
    	if ($method == "POST") {
    		//接收信息
    		$data = $this->request->post();
    		if ( !isset($data['account']) || !isset($data['password']) || !isset($data['code']) ) {
    			$this->error('参数不足');
    		}
    		if ($data['account'] == '') {
    			$this->error('请输入账户');
    		}
    		if ($data['password'] == '') {
    			$this->error('请输入密码');
    		}
    		if ($data['code'] == '验证码:') {
    			$this->error('请输入验证码');
    		}
    		$captcha = new \think\captcha\Captcha();
	        if(!$captcha->check($data['code'])){
	            $this->error('验证码错误');
	        }
	        // dump($data);
    		$check_account = Db::table('admin')->where('account',$data['account'])->find();
    		if (empty($check_account)) {
    			$this->error('账户不存在');
    		}
    		if ($check_account['status']==0) {
    			$this->error('您的账户已冻结，请联系管理员');
    		}
    		if (md5(md5($data['password'])) != $check_account['password']) {
    			$this->error('密码错误，请重新输入');
    		}
    		//写操作日志
    		$admin_log = [
    			'admin_id'    => $check_account['id'],
    			'type'  	  => 1,
    			'content'  	  => '登录成功！',
    			'ip'          => $_SERVER['REMOTE_ADDR'],
    			'create_time' => date('Y-m-d H:i:s')
    		];
    		Db::table('admin_log')->insert($admin_log);
    		//修改登录次数
    		Db::table('admin')->where('id',$check_account['id'])->update(['login_sum'=>$check_account['login_sum']+1]);
            //查询最新的管理账号信息
            $admin = Db::table('admin')->where('id',$check_account['id'])->find();
            //释放密码
            unset($admin['password']);
            //加入当前查询时间、Base.php中做每1分钟更新一次
            $admin['life_time'] = time()+60;
            //写入session缓存用
            Session::set('admin',$admin);
    		//跳转到后台主页
    		$this->redirect('index/index');
    	}
    }
    //退出
    public function loginout()
    {
        $admin = Session::get('admin');
        if (empty($admin)) {
            $this->error('未登陆');
        }
        //写操作日志
        $admin_log = [
            'admin_id'    => $admin['id'],
            'type'        => 1,
            'content'     => '退出登录 成功！',
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'create_time' => date('Y-m-d H:i:s')
        ];
        Db::table('admin_log')->insert($admin_log);
        Session::clear();
        $this->redirect('login/login');
    }
}