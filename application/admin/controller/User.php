<?php
namespace app\admin\controller;

use think\Db;
//用户管理
class User extends Base{
    //列表
    public function index(){
    	$where = "a.status > 0";//拼接搜索语句
        $request = $this->request->get();
        $fanye = ['datemin'=>'','datemax'=>'','xz'=>'','word'=>''];
        if ( isset($request['datemin']) && $request['datemin'] !='' ) {
            $where = " a.ctime >= ".strtotime($request['datemin']);
            $fanye['datemin']=$request['datemin'];
        }
        if ( isset($request['datemax']) && $request['datemax'] !='' ) {
            $where = " a.ctime >= ".strtotime($request['datemax']);
            $fanye['datemax']=$request['datemax'];
        }
        if ( isset($request['word']) && $request['word'] !='' && isset($request['xz']) && $request['xz'] !='' ) {
        	switch ($request['xz']) {
        		case '1':
        			$where .= " and a.nickname like '%".$request['word']."%'";
        			break;
        		case '2':
        			$where .= " and a.username = '".$request['word']."'";
        			break;
        		case '3':
        			$where .= " and a.phone = '".$request['word']."'";
        			break;
        		default:
        			# code...
        			break;
        	}
            $fanye['xz']=$request['xz'];
            $fanye['word']=$request['word'];
        }
    	//查询
    	$page_data = Db::name('users')
            ->field('a.*,b.sname,b.address,c.sec_name,c.grade,c.id')
            ->alias('a')
            ->join('school b','a.school = b.id','left')
            ->join('users_secretary c','a.user_id = c.user_id','left')
            ->where($where)
            ->order('a.status asc,a.ctime desc')
            ->paginate(10,false,['query' => request()->param()]);
        $data = $page_data->items();
        foreach ($data as $k => $v) {
            $data[$k]['create_time'] = date('Y-m-d H:i:s',$v['ctime']);
            if ( is_null($v['sec_name']) && is_null($v['grade']) ) {
            	$data[$k]['sec_name'] = '';
            }
            $data[$k]['vip'] = $v['u_vip']>0?$v['u_vip']:'';
            $data[$k]['sex'] = $v['sex']>1?'女':'男';
            $data[$k]['real'] = $v['real']>0?'是':'';
        }
        // dump($phone_models);
        // exit;
        $page['total'] = $page_data->total();
        $page['current'] = $page_data->currentPage();
        $page['last'] = $page_data->lastPage();
        $this->assign('data',$data);
        $this->assign('page_data',$page_data);
    	$this->assign('page',$page);
        $this->assign('fanye',$fanye);
        return $this->fetch();
    }
    //修改
    public function edit(){

    }
    //启用
    public function start(){
        if ( $this->request->isPost() ) {
            //接收信息
            $id = $this->request->post('id');
            $status = $this->request->post('status');
            $check = Db::table('users')->field('status')->where('user_id',$id)->where('status',$status)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['status'] == 1) {
                $this->echojson(201,'已是启用状态');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态，不可操作');
            }
            $admin = $this->admin;//管理员信息
            $update = Db::table('users')->where('user_id',$id)->update(['status'=>1]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '启用会员 ID:'.$id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'操作成功');
            }
            else{
                $this->echojson(201,'操作失败，请重试');
            }
        }
    }
    //停用
    public function stop(){
        if ( $this->request->isPost() ) {
            //接收信息
            $id = $this->request->post('id');
            $status = $this->request->post('status');
            $check = Db::table('users')->field('status')->where('user_id',$id)->where('status',$status)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['status'] == 2) {
                $this->echojson(201,'已是停用状态');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态，不可操作');
            }
            $admin = $this->admin;//管理员信息
            $update = Db::table('users')->where('user_id',$id)->update(['status'=>2]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '停用会员 ID:'.$id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'操作成功');
            }
            else{
                $this->echojson(201,'操作失败，请重试');
            }
        }
    }
    //修改密码
    public function changepasswd(){
        //提交判断
        if ( $this->request->isPost() ) {
            $data = $this->request->post();//接收信息
            if ( !isset($data['user_id']) || !is_numeric($data['user_id']) ) {
                $this->echojson(201,'缺少参数');
            }
            if ( !isset($data['password']) || empty($data['password']) ) {
                $this->echojson(201,'新密码不能为空');
            }
            if ( !isset($data['password2']) || empty($data['password2']) ) {
                $this->echojson(201,'确认密码不能为空');
            }
            if ( $data['password'] != $data['password2'] ) {
                $this->echojson(201,'两次密码不一致，请重新输入');
            }
            $password = md5(md5($data['password']));
            $check = Db::table('users')->where('user_id',$data['user_id'])->find();
            if (empty($check)) {
                $this->echojson(201,'查询失败，参数错误');
            }
            if ($check['password'] == $password) {
                $this->echojson(201,'新密码与旧密码相同，请重新输入');
            }
            if ($check['status']==0 || $check['status']==2) {
                $this->echojson(201,'已删除、已停用的会员不可修改密码');
            }
            $admin = $this->admin;//管理信息
            $update = Db::table('users')->where('user_id',$data['user_id'])->update(['password'=>$password]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '修改会员密码 ID '.$data['user_id'],
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
        $id = $this->request->get('id');//接收id
        $check = Db::table('users')->field('user_id,username,status')->where('user_id',$id)->find();//判断状态
        if (empty($check) || !isset($check['status'])) {
            $this->error('参数错误');
        }
        if ($check['status']==0 || $check['status']==2) {
            $this->error('已删除、已停用的会员不可修改密码');
        }
        $this->assign('data',$check);
        return $this->fetch();
    }
    //删除
    public function del(){
        if ( $this->request->isPost() ) {
            //接收信息
            $id = $this->request->post('id');
            $check = Db::table('users')->where('user_id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'对象不存在，或参数错误');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态');
            }
            $admin = $this->admin;//管理员信息
            $update = Db::table('users')->where('user_id',$id)->update(['status'=>0]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 4,
                    'content'    => '删除会员 ID '.$id,
                    'before'     => json_encode($check),
                    'create_time'=> date('Y-m-d H:i:s'),
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