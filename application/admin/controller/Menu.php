<?php
namespace app\admin\controller;

use think\Db;
//菜单管理
class Menu extends Base{
    //菜单列表
    public function index(){
    	//菜单信息
    	$admin = $this->admin;
        $where = "id > 0";
        $request = $this->request->get();
        if ( isset($request['name']) && $request['name'] !='' ) {
            $where .= " and name like '%".$request['name']."%'";
        }
    	//查询所有菜单
    	$page_data = Db::name('admin_func')->where($where)->where('status',1)->paginate(10,false,['query' => request()->param()]);
        $data = $page_data->items();
        $page['total'] = $page_data->total();
        $page['current'] = $page_data->currentPage();
        $page['last'] = $page_data->lastPage();
        // dump($page_data);
        $this->assign('adm',$admin);
        $this->assign('data',$data);
        $this->assign('page_data',$page_data);
    	$this->assign('page',$page);
        $this->assign('name',isset($request['name'])?$request['name']:'');
        return $this->fetch();
    }
    //添加菜单
    public function add(){
        //菜单信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if ( !isset($data['name']) || !isset($data['url']) || !isset($data['level']) || !isset($data['pid']) || !isset($data['order_num']) ) {
                $this->echojson(201,'参数不足');
            }
            if ($data['name'] == '') {
                $this->echojson(201,'菜单名称不能为空');
            }
            if ($data['level'] == '') {
                $this->echojson(201,'菜单级别不能为空');
            }
            if ($data['level'] > 1) {
                if ($data['url'] == '') {
                    $this->echojson(201,'二三级菜单URL必填');
                }
                if ($data['pid'] == '') {
                    $this->echojson(201,'二三级菜单pid必填');
                }
            }
            $check_name = Db::table('admin_func')->where('name',$data['name'])->value('id');
            if ($check_name) {
                $this->echojson(201,'菜单名称已存在，请重新输入');
            }
            $data['create_time'] = date('Y-m-d H:i:s');
            
            $new_menu_id = Db::table('admin_func')->insertGetId($data);
            if ($new_menu_id) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '添加菜单:ID '.$new_menu_id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'添加成功');
            }
            else{
                $this->echojson(201,'添加失败，请重试');
            }
        }
        return $this->fetch();
    }
    //修改菜单
    public function edit(){
        //菜单信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if ( !isset($data['id']) || !isset($data['level']) || !isset($data['pid']) || !isset($data['name']) || !isset($data['url']) || !isset($data['order_num']) ) {
                $this->echojson(201,'参数不足');
            }
            $old_info = Db::table('admin_func')->field('id,level,pid,name,url,order_num')->where('id',$data['id'])->find();
            if (empty($old_info)) {
                $this->echojson(201,'该菜单不存在');
            }
            if ($old_info == $data) {
                $this->echojson(201,'未做任何修改');
            }
            if ($data['name'] == '') {
                $this->echojson(201,'菜单名称不能为空');
            }
            if ($data['level'] == '') {
                $this->echojson(201,'菜单等级不能为空');
            }
            if ($data['level'] > 1) {
                if ($data['url'] == '') {
                    $this->echojson(201,'二三级菜单URL必填');
                }
                if ($data['pid'] == '') {
                    $this->echojson(201,'二三级菜单pid必填');
                }
            }
            $arr = [];
            if ($data['level'] != $old_info['level']) {
                $arr['level']=$data['level'];
            }
            if ($data['pid'] != $old_info['pid']) {
                $arr['pid']=$data['pid'];
            }
            if ($data['name'] != $old_info['name']) {
                $arr['name']  =$data['name'];
            }
            if ($data['url'] != $old_info['url']) {
                $arr['url']  =$data['url'];
            }
            if ($data['order_num'] != $old_info['order_num']) {
                $arr['order_num']  =$data['order_num'];
            }
            if (empty($arr)) {
                $this->echojson(201,'未做任何修改');
            }
            $old_data = Db::table('admin_func')->where('id',$data['id'])->find();
            $update = Db::table('admin_func')->where('id',$data['id'])->update($arr);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '修改菜单:ID '.$data['id'],
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
        $data = Db::table('admin_func')->field('id,level,pid,name,url,order_num')->where('id',$id)->find();
        $this->assign('data',$data);
        return $this->fetch();
    }
    //删除菜单
    public function del(){
        //管理信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $check = Db::table('admin_func')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态');
            }
            //一二级菜单判断是否有子菜单
            if ($check['level'] == 1 || $check['level'] == 2) {
                $check2 = Db::table('admin_func')->where('pid',$id)->count();
                if ( $check2 > 0 ) {
                    $this->echojson(201,'删除失败，该菜单下有子菜单');
                }
            }
            $update = Db::table('admin_func')->where('id',$id)->update(['status'=>0]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 4,
                    'content'    => '删除菜单:ID '.$id,
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