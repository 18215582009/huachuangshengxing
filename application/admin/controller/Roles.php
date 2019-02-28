<?php
namespace app\admin\controller;

use think\Db;
//角色管理
class Roles extends Base{
    //角色列表
    public function index(){
    	//管理员信息
    	$admin = $this->admin;
    	//查询角色名
    	$page_data = Db::name('admin_roles')->where('status',1)->paginate(10);
        $data = $page_data->items();
        $admins = Db::table('admin')
            ->field('a.roles_id,a.id,a.name,a.account')
            ->alias('a')
            ->join('admin_roles b','a.roles_id = b.id')
            ->where('a.status <= 1 ')
            ->where('b.status  = 1 ')
            ->select();
        foreach ($data as $k1 => $v1) {
            $data[$k1]['admins'] = [];
            foreach ($admins as $k2 => $v2) {
                if ($v2['roles_id'] == $v1['id']) {
                    $data[$k1]['admins'][]=$v2;
                }
            }
        }
        unset($admins);
        $page['total'] = $page_data->total();
        $page['current'] = $page_data->currentPage();
        $page['last'] = $page_data->lastPage();
        $this->assign('data',$data);
        $this->assign('page_data',$page_data);
    	$this->assign('page',$page);
        return $this->fetch();
    }
    //添加角色
    public function add(){
        //管理信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if ( !isset($data['name']) || !isset($data['description']) || !isset($data['type']) ) {
                $this->echojson(201,'参数不足');
            }
            if ($data['name'] == '') {
                $this->echojson(201,'角色名称不能为空');
            }
            $data['functions'] = null;
            if ($data['type'] == 'all') {
                $data['functions'] = 'all';
            }
            if ($data['type'] == '1') {
                if (isset($data['data'])) {
                    $data['functions'] = json_encode($data['data']);
                }else{
                    $data['functions'] = '';
                }
            }
            if ( isset($data['data']) ) {
                unset($data['data']);
            }
            unset($data['type']);
            $check_name = Db::table('admin_roles')->where('name',$data['name'])->where('status',1)->value('id');
            if ($check_name) {
                $this->echojson(201,'角色名称已存在，请重新输入');
            }
            $data['create_time'] = date('Y-m-d H:i:s');
            $new_roles_id = Db::table('admin_roles')->insertGetId($data);
            if ($new_roles_id) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '添加角色:ID '.$new_roles_id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'添加成功');
            }
            else{
                $this->echojson(201,'添加失败，请重试');
            }
        }
        //查询所有菜单信息
        $menus = $this->getAllMenus();
        $this->assign('menus',$menus);
        return $this->fetch();
    }
    //修改角色
    public function edit(){
        //管理信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if ( !isset($data['name']) || !isset($data['description']) || !isset($data['type']) ) {
                $this->echojson(201,'参数不足');
            }
            if ($admin['id'] == $data['id'] && $admin['id'] !=1) {
                $this->echojson(201,'不能修改自己');
            }
            if ($admin['id'] ==1 && $data['id'] ==1 && $data['type'] == 1) {
                $this->echojson(201,'超级管理员不可改为普通管理员');
            }
            if ($data['name'] == '') {
                $this->echojson(201,'角色名称不能为空');
            }
            $data['functions'] = null;
            if ($data['type'] == 'all') {
                $data['functions'] = 'all';
            }
            if ($data['type'] == '1') {
                if (isset($data['data'])) {
                    $data['functions'] = json_encode($data['data']);
                }else{
                    $data['functions'] = '';
                }
            }
            if ( isset($data['data']) ) {
                unset($data['data']);
            }
            unset($data['type']);
            $id = $data['id'];
            unset($data['id']);
            $check = Db::table('admin_roles')->field('name,description,functions')->where('id',$id)->where('status',1)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($check == $data) {
                $this->echojson(201,'未做任何修改');
            }
            if ($check['name'] != $data['name']) {
                $check_name = Db::table('admin_roles')->where('name',$data['name'])->where('status',1)->value('id');
                if ($check_name) {
                    $this->echojson(201,'角色名称已存在，请重新输入');
                }
            }
            $data['create_time'] = date('Y-m-d H:i:s');
            $old_data=Db::table('admin_roles')->where('id',$id)->find();
            $update = Db::table('admin_roles')->where('id',$id)->update($data);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '修改角色:ID'.$id,
                    'before'     => json_encode($old_data),
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'修改成功');
            }
            else{
                $this->echojson(201,'修改失败，请重试');
            }
        }
        $id = $this->request->get('id');
        $data = Db::table('admin_roles')->where('id',$id)->find();
        if($data['status']==0){
            $this->error('该角色已被删除');
        }
        $func = [];
        if (!empty($data['functions'])) {
            if ($data['functions'] != 'all') {
                $functions = json_decode($data['functions'],true);
                foreach ($functions as $k => $v) {
                    $func[] = $v[0];
                    if (isset($v['child'])) {
                        foreach ($v['child'] as $k1 => $v1) {
                            $func[] = $v1[0];
                            if (isset($v1['child'])) {
                                foreach ($v1['child'] as $k2 => $v2) {
                                    $func[] = $v2[0];
                                }
                            }
                        }
                    }
                }
                $data['cc'] = 3;
            }else{
                $data['cc'] = 1;
            }
        }
        else{
            $data['cc'] = 2;
        }
        //查询所有菜单信息
        $menus = $this->getAllMenus();
        $this->assign('data',$data);
        $this->assign('func',$func);
        $this->assign('menus',$menus);
        return $this->fetch();
    }
    //删除角色
    public function del(){
        //管理信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $check = Db::table('admin_roles')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态');
            }
            if ($id == 1) {
                $this->echojson(201,'超级管理员不可删除');
            }
            if ($id == $admin['id']) {
                $this->echojson(201,'不能删除自己');
            }
            //判断角色下面是否有管理账户
            $check2 = Db::table('admin')->where('roles_id',$id)->where('status <= 1')->count();
            if ($check2 > 0) {
                $this->echojson(201,'删除失败，该角色下有'.$check2.'个管理账户');
            }
            $arr = ['status'=>0];
            $update = Db::table('admin_roles')->where('id',$id)->update($arr);
            if ($update) {
                $now_time = date('Y-m-d H:i:s');
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 4,
                    'content'    => '删除角色:ID '.$id,
                    'before'     => json_encode($check),
                    'create_time'=> $now_time
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'删除成功');
            }
            else{
                $this->echojson(201,'删除失败，请重试');
            }
        }
    }
}