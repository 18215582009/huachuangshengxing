<?php
namespace app\admin\controller;

use think\Db;
//操作日志管理
class Log extends Base{
    //日志列表
    public function index(){
        $admin = $this->admin;
        //查询角色权限
        $functions = Db::table('admin_roles')->where('id',$admin['roles_id'])->value('functions');
        //拼接搜索条件
        if ($functions == 'all') {
            $where = "a.id > 0";
        }
        else{
            $where = "a.admin_id = ".$admin['id'];
        }
        $request = $this->request->get();
        if ( isset($request['datemin']) && $request['datemin'] !='' ) {
            $where .= " and a.create_time > '".$request['datemin']."'";
        }
        if ( isset($request['datemax']) && $request['datemax'] !='' ) {
            $where .= " and a.create_time < '".$request['datemax']."'";
        }
        if ( isset($request['content']) && $request['content'] !='' ) {
            $where .= " and a.content like '%".$request['content']."%'";
        }
    	//查询
    	$page_data = Db::name('admin_log')
            ->field('a.*,b.name')
            ->alias('a')
            ->join('admin b','a.admin_id=b.id','left')
            ->where($where)
            ->where('a.status = 1')
            ->order('create_time desc')
            ->paginate(10,false,['query' => request()->param()]);
        $data = $page_data->items();

        $page['total'] = $page_data->total();
        $page['current'] = $page_data->currentPage();
        $page['last'] = $page_data->lastPage();
        $this->assign('data',$data);
        $this->assign('page_data',$page_data);
    	$this->assign('page',$page);
        $this->assign('datemin',isset($request['datemin'])?$request['datemin']:'');
        $this->assign('datemax',isset($request['datemax'])?$request['datemax']:'');
        $this->assign('name',isset($request['name'])?$request['name']:'');
        return $this->fetch();
    }
    //删除日志
    public function del(){
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $check = Db::table('admin_log')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'对象不存在，或参数错误');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态');
            }

            $update = Db::table('admin_log')->where('id',$id)->update(['status'=>0]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 4,
                    'content'    => '删除操作日志:ID '.$id,
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