<?php
namespace app\admin\controller;

use think\Db;
//后台首页
class Index extends Base{
    //首页
    public function index(){
    	//管理员信息
    	$admin = $this->admin;
    	//查询角色名、角色权限
    	$roles = Db::table('admin_roles')
            ->field('name,functions')
            ->where('id',$admin['roles_id'])
            ->where('status',1)
            ->cache(60)//60秒缓存
            ->find();
    	$data = [
            'admin'     =>$admin,
    		'admin_name'=>$admin['name'],
    		'roles_name'=>isset($roles['name'])?$roles['name']:'角色不存在'
    	];
        $menus = $this->getAccessMenus($roles['functions']);
        $this->assign('data',$data);
    	$this->assign('menu',$menus);
        return $this->fetch();
    }
    //欢迎页面
    public function welcome(){
    	//管理员信息
    	$admin = $this->admin;
    	//查询登录ip，time
    	$login_info = Db::table('admin_log')->field('ip,create_time')->where('admin_id',$admin['id'])->where('type',1)->order('id desc')->limit(2)->select();
    	if (count($login_info)==1) {
    		$admin['last_ip']   = '';
    		$admin['last_time'] = '';
    	}
    	else{
    		$admin['last_ip']   = $login_info[1]['ip'];
    		$admin['last_time'] = $login_info[1]['create_time'];
    	}

    	$this->assign('data',$admin);
        return $this->fetch();
    }
    //获取一二级菜单、加载首页菜单栏用
    protected function getAccessMenus($functions = []){
        $menus = [];
        if ($functions == 'all') {
            return $this->getAllMenus();
        }
        elseif($functions == ''){
            return [];
        }
        else{
            $functions = json_decode($functions,true);
        }
        $allmenus=Db::table('admin_func')
            ->field('id,name,url')
            ->where('status',1)
            ->where('level < 3')
            ->order('order_num asc')
            ->cache(60)//60秒缓存
            ->select();
        $arr = [];
        foreach ($allmenus as $k => $v) {
            $arr[$v['id']]['name'] = $v['name'];
            $arr[$v['id']]['url'] = $v['url'];
        }
        unset($allmenus);
        foreach ($functions as $k => $v) {
            //一级
            if (isset($arr[$v[0]])) {
                $menus[$k]['name'] = $arr[$v[0]]['name'];
                $menus[$k]['url'] = $arr[$v[0]]['url'];
                $menus[$k]['child'] = [];
            }
            //二级
            foreach ($v['child'] as $k2 => $v2) {
                if (isset($arr[$v2[0]])) {
                    $menus[$k]['child'][$v2[0]]['name'] = $arr[$v2[0]]['name'];
                    $menus[$k]['child'][$v2[0]]['url'] = $arr[$v2[0]]['url'];
                    if (!isset($v2['child'])) {
                        $v2['child'] = [];
                    }
                }
            }
        }
        return $menus;
    }
    //修改密码
    public function changepw(){
        //管理信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if (!isset($data['id']) || $data['id'] !=$admin['id']) {
                $this->echojson(201,'id错误');
            }
            if (!isset($data['password']) || empty($data['password'])) {
                $this->echojson(201,'请输入密码');
            }
            $id = $data['id'];
            $password = md5(md5($data['password']));
            $check = Db::table('admin')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'查询失败，请重试');
            }
            if ($check['password'] == $password) {
                $this->echojson(201,'密码与旧密码相同，请重新输入');
            }
            $update = Db::table('admin')->where('id',$id)->update(['password'=>$password]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '修改了自己的密码 :ID '.$id,
                    'before'     => json_encode($check),
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'修改成功');
            }
            else{
                $this->echojson(201,'修改失败，请重试');
            }
        }
    }
}