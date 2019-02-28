<?php
namespace app\admin\controller;

use think\Db;
//关于我们、协议管理
class Aboutus extends Base{
    //列表
    public function index(){
    	//管理员信息
    	$admin = $this->admin;
    	//查询角色名
    	$page_data = Db::name('about_us')->where('status',1)->paginate(10,false,['query' => request()->param()]);
        $data = $page_data->items();
        //获取分类名称
        $classes = $this->getclass();
        foreach ($data as $k => $v) {
            $data[$k]['classname']=$classes[$v['type']];
        }
        $page['total'] = $page_data->total();
        $page['current'] = $page_data->currentPage();
        $page['last'] = $page_data->lastPage();
        $this->assign('data',$data);
        $this->assign('page_data',$page_data);
    	$this->assign('page',$page);
        return $this->fetch();
    }
    //添加
    public function add(){
        //管理信息
        $admin = $this->admin;
        //获取分类名称
        $classes = $this->getclass();
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if ($data['title'] == '') {
                $this->echojson(201,'标题不能为空');
            }
            if ( !isset($data['type']) || empty($data['type']) ) {
                $this->echojson(201,'请选择分类');
            }
            if ( !isset($data['editorValue']) || empty($data['editorValue']) ) {
                $this->echojson(201,'内容不能为空');
            }
            $data['content'] = $data['editorValue'];
            unset($data['editorValue']);

            $path = 'h5/'.date('Y').date('m').date('d').date('H').date('i').date('s').'.html' ;
            $files_data = $this->h5(1).$data['content'].$this->h5(2);
            file_put_contents(ROOT_PATH.'public'.DS.$path, $files_data);
            $data['url'] = $path;
            $data['create_time'] = date('Y-m-d H:i:s');
            $new_id = Db::table('about_us')->insertGetId($data);
            if ($new_id) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '添加'.$classes[$data['type']].':ID '.$new_id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'添加成功');
            }
            else{
                $this->echojson(201,'添加失败，请重试');
            }
        }
        $this->assign('classes',$classes);
        return $this->fetch();
    }
    //修改
    public function edit(){
        //管理信息
        $admin = $this->admin;
        //获取分类名称
        $classes = $this->getclass();
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if ($data['title'] == '') {
                $this->echojson(201,'标题不能为空');
            }
            if ( !isset($data['editorValue']) ) {
                $this->echojson(201,'内容不能为空');
            }
            $data['content'] = $data['editorValue'];
            unset($data['editorValue']);
            $id = $data['id'];
            unset($data['id']);
            $old_data = Db::table('about_us')->where('id',$id)->find();
            if ( empty($old_data) ) {
                $this->echojson(201,'未查到信息、参数错误');
            }
            //检查是否有修改
            if ($data['title']==$old_data['title'] && $data['content']==$old_data['content']) {
                $this->echojson(201,'无修改');
            }
            if ($data['content']!=$old_data['content']) {
                $path = $old_data['url'];
                $files_data = $this->h5(1).$data['content'].$this->h5(2);
                file_put_contents(ROOT_PATH.'public'.DS.$path, $files_data);
            }
            $update = Db::table('about_us')->where('id',$id)->update($data);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '修改'.$classes[$old_data['type']].':ID'.$id,
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
        $data = Db::table('about_us')->where('id',$id)->find();
        if (empty($data)) {
            $this->error('参数错误');
        }
        $this->assign('data',$data);
        return $this->fetch();
    }
    //删除
    public function del(){
        //管理信息
        $admin = $this->admin;
        //获取分类名称
        $classes = $this->getclass();
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $check = Db::table('about_us')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态');
            }
            if ($check['is_used'] == 1) {
                $this->echojson(201,'该条信息在使用中，请先停用，再删除');
            }
            $update = Db::table('about_us')->where('id',$id)->update(['status'=>0]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 4,
                    'content'    => '删除'.$classes[$check['type']].':ID '.$id,
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
    //停用
    public function stop(){
        //管理员信息
        $admin = $this->admin;
        //获取分类名称
        $classes = $this->getclass();
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $status = $this->request->post('status');
            $check = Db::table('about_us')->where('id',$id)->find();
            if (empty($check) || !isset($status)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['is_used'] == 0) {
                $this->echojson(201,'已是停用状态');
            }
            $checkcount = Db::table('about_us')->where('type',$check['type'])->where('status',1)->where('is_used',1)->count();
            if ($checkcount == 1) {
                $this->echojson(201,'停用失败，该分类下至少要有一个在启用中');
            }
            $update = Db::table('about_us')->where('id',$id)->update(['is_used'=>0]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '停用'.$classes[$check['type']].':ID '.$id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'停用成功');
            }
            else{
                $this->echojson(201,'停用失败，请重试');
            }
        }
    }
    //启用
    public function start(){
        //管理员信息
        $admin = $this->admin;
        //获取分类名称
        $classes = $this->getclass();
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $status = $this->request->post('status');
            $check = Db::table('about_us')->where('id',$id)->find();
            if (empty($check) || !isset($status)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['is_used'] == 1) {
                $this->echojson(201,'已是启用状态');
            }
            $checkcount = Db::table('about_us')->where('type',$check['type'])->where('status',1)->where('is_used',1)->count();
            if ($checkcount > 0) {
                $this->echojson(201,'启用失败，该分类下已有'.$checkcount.'个在启用中');
            }
            $update = Db::table('about_us')->where('id',$id)->update(['is_used'=>1]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '启用'.$classes[$check['type']].':ID '.$id,
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
    //h5页面head、foot
    protected function h5($type=1){
        if ($type==1) {
            return '<!DOCTYPE HTML><html><head><meta charset="utf-8"><meta name="renderer" content="webkit|ie-comp|ie-stand"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/><meta http-equiv="Cache-Control" content="no-siteapp" /><title>关于我们</title><style type="text/css">*{margin:0;boder:0;padding:0;}</style></head><body>';
        }
        if ($type==2) {
            return '</body></html>';
        }
    }
    //分类
    protected function getclass(){
        return [
            '1'=>'关于我们',
            '2'=>'用户协议',
            '3'=>'隐私协议',
            // '4'=>'用户反馈',
            // '5'=>'vip中心',
            // '6'=>'我的等级',
        ];
    }
}