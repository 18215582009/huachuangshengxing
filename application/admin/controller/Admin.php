<?php
namespace app\admin\controller;

use think\Db;
//管理员管理
class Admin extends Base
{
    //管理员列表
    public function index(){
    	//管理员信息
    	$admin = $this->admin;
        $where = "id > 0";
        $request = $this->request->get();
        if ( isset($request['datemin']) && $request['datemin'] !='' ) {
            $where .= " and create_time >= '".$request['datemin']."'";
        }
        if ( isset($request['datemax']) && $request['datemax'] !='' ) {
            $where .= " and create_time < '".$request['datemax']."'";
        }
        if ( isset($request['name']) && $request['name'] !='' ) {
            $where .= " and name like '%".$request['name']."%'";
        }
    	//查询所有管理员
    	$page_data = Db::name('admin')
            ->where($where)
            ->whereIn('status',[0,1])
            ->paginate(10,false,['query' => request()->param()]);
        $data = $page_data->items();
        //释放掉所有管理员的密码
        foreach ($data as $key => $value) {
            unset($data[$key]['password']);
        }
        $page['total'] = $page_data->total();
        $page['current'] = $page_data->currentPage();
        $page['last'] = $page_data->lastPage();

        $this->assign('adm',$admin);
        $this->assign('data',$data);
        $this->assign('page_data',$page_data);
    	$this->assign('page',$page);
        $this->assign('datemin',isset($request['datemin'])?$request['datemin']:'');
        $this->assign('datemax',isset($request['datemax'])?$request['datemax']:'');
        $this->assign('name',isset($request['name'])?$request['name']:'');
        return $this->fetch();
    }
    //添加管理员页面
    public function add(){
        //管理员信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if ( !isset($data['name']) || !isset($data['account']) || !isset($data['password']) || !isset($data['password2']) || !isset($data['roles_id']) || !isset($data['status']) ) {
                $this->echojson(201,'参数不足');
            }
            if ($data['name'] == '') {
                $this->echojson(201,'管理员名称不能为空');
            }
            if ($data['account'] == '') {
                $this->echojson(201,'请输入账户');
            }
            if ($data['password'] == '') {
                $this->echojson(201,'请输入初始密码');
            }
            if ($data['password2'] == '') {
                $this->echojson(201,'请输入确认密码');
            }
            if ($data['password'] != $data['password2']) {
                $this->echojson(201,'两次密码不一致，请重新输入');
            }
            if ($data['roles_id'] == '') {
                $this->echojson(201,'请选择角色');
            }
            $check_account = Db::table('admin')->where('account',$data['account'])->value('id');
            if ($check_account) {
                $this->echojson(201,'账户已存在，请重新输入');
            }
            $check_roles_name = Db::table('admin_roles')->where('id',$data['roles_id'])->value('name');
            if (empty($check_roles_name)) {
                $this->echojson(201,'角色不存在');
            }
            unset($data['password2']);
            $data['password'] = md5(md5($data['password']));
            $data['roles_name'] = $check_roles_name;
            $data['create_time'] = date('Y-m-d H:i:s');
            
            $new_admin_id = Db::table('admin')->insertGetId($data);
            if ($new_admin_id) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '添加管理员:ID '.$new_admin_id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'添加成功');
            }
            else{
                $this->echojson(201,'添加失败，请重试');
            }
        }
        //所有角色信息
        $roles = Db::table('admin_roles')->field('id,name')->where('status',1)->select();
        $this->assign('roles',$roles);
        return $this->fetch();
    }
    //修改管理员页面
    public function edit(){
        //管理员信息
        $admin = $this->admin;
        //修改提交处理
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if ( !isset($data['name']) || !isset($data['account']) || !isset($data['password']) || !isset($data['roles_id']) ) {
                $this->echojson(201,'参数不足');
            }
            $old_info = Db::table('admin')->field('id,name,account,roles_id')->where('id',$data['id'])->find();
            if (empty($old_info)) {
                $this->echojson(201,'账户不存在');
            }
            if (empty($data['password'])) {
                unset($data['password']);
                if ($old_info == $data) {
                    $this->echojson(201,'未做任何修改');
                }
            }
            if ($data['name'] == '') {
                $this->echojson(201,'管理员名称不能为空');
            }
            if ($data['account'] == '') {
                $this->echojson(201,'账户不能为空');
            }
            if ($data['roles_id'] == '') {
                $this->echojson(201,'请选择角色');
            }
            $arr = [];
            if (!empty($data['password'])) {
                $arr['password']=md5(md5($data['password']));
            }
            if ($data['name'] != $old_info['name']) {
                $arr['name']=$data['name'];
            }
            if ($data['account'] != $old_info['account']) {
                $arr['account']=$data['account'];
            }
            if ($data['roles_id'] != $old_info['roles_id']) {
                $arr['roles_id']  =$data['roles_id'];
                $arr['roles_name']=Db::table('admin_roles')->where('id',$data['roles_id'])->value('name');
            }
            if (empty($arr)) {
                $this->echojson(201,'未做任何修改');
            }
            $old_data = Db::table('admin')->where('id',$data['id'])->find();
            $update = Db::table('admin')->where('id',$old_info['id'])->update($arr);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '修改管理员:ID '.$data['id'],
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
        $data = Db::table('admin')->field('id,name,account,roles_id')->where('id',$id)->find();
        if (empty($data)) {
            $this->error('参数错误');
        }
        $roles = Db::table('admin_roles')->field('id,name')->where('status',1)->select();
        $this->assign('data',$data);
        $this->assign('roles',$roles);
        return $this->fetch();
    }
    //禁用管理员处理
    public function stop(){
        //管理员信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $status = $this->request->post('status');
            $check = Db::table('admin')->field('name,status')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($admin['id'] == $id) {
                $this->echojson(201,'不能操作自己');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是禁用状态');
            }
            $update = Db::table('admin')->where('id',$id)->update(['status'=>0]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '禁用管理员:ID '.$id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'禁用成功');
            }
            else{
                $this->echojson(201,'禁用失败，请重试');
            }
        }
    }
    //启用管理员处理
    public function start(){
        //管理员信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $status = $this->request->post('status');
            $check = Db::table('admin')->field('name,status')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($admin['id'] == $id) {
                $this->echojson(201,'不能操作自己');
            }
            if ($check['status'] == 1) {
                $this->echojson(201,'已是启用状态');
            }
            $update = Db::table('admin')->where('id',$id)->update(['status'=>1]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '启用管理员:ID '.$id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'启用成功');
            }
            else{
                $this->echojson(201,'启用失败，请重试');
            }
        }
    }
    //删除管理员处理
    public function del(){
        //管理员信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $check = Db::table('admin')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($admin['id'] == $id) {
                $this->echojson(201,'不能操作自己');
            }
            if ($admin['status'] == 4) {
                $this->echojson(201,'已是删除状态');
            }
            $update = Db::table('admin')->where('id',$id)->update(['status'=>4]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 4,
                    'content'    => '删除管理员:ID '.$id,
                    'before'     => json_encode($check),
                    'create_time'=> date('Y-m-d H:i:s')
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