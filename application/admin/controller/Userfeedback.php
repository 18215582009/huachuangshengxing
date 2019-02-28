<?php
namespace app\admin\controller;

use think\Db;
//用户 意见反馈
class Userfeedback extends Base{
    //列表
    public function index(){
    	//管理员信息
    	$admin = $this->admin;
        $where = "a.status >= 1";
        $request = $this->request->get();
        if ( isset($request['status']) && $request['status'] !='' ) {
            $where = "a.status = ".$request['status'];
        }
        if ( isset($request['datemin']) && $request['datemin'] !='' ) {
            $where .= " and a.create_time >= '".$request['datemin']."'";
        }
        if ( isset($request['datemax']) && $request['datemax'] !='' ) {
            $where .= " and a.create_time < '".$request['datemax']."'";
        }
        if ( isset($request['content']) && $request['content'] !='' ) {
            $where .= " and a.content like '%".$request['content']."%'";
        }
    	//查询角色名
    	$page_data = Db::name('user_feedback')
            ->field('a.*,b.username,b.nickname')
            ->alias('a')
            ->join('users b','a.user_id = b.user_id','left')
            ->where($where)
            ->order('a.status asc')
            ->paginate(10,false,['query' => request()->param()]);
        $data = $page_data->items();
        //将评论图片json_decode、状态转汉字
        $status = ['1'=>'待审核','2'=>'审核中','3'=>'未完成','4'=>'已完成'];
        foreach ($data as $k => $v) {
            if (!empty($v['urls'])) {
                $data[$k]['urls'] = json_decode($v['urls'],true);
            }else{
                $data[$k]['urls'] = [];
            }
            $data[$k]['status_name'] = $status[$v['status']];
        }
        // dump($data);
        // exit;
        $page['total'] = $page_data->total();
        $page['current'] = $page_data->currentPage();
        $page['last'] = $page_data->lastPage();
        $this->assign('data',$data);
        $this->assign('page_data',$page_data);
    	$this->assign('page',$page);
        $this->assign('status',isset($request['status'])?$request['status']:'');
        $this->assign('datemin',isset($request['datemin'])?$request['datemin']:'');
        $this->assign('datemax',isset($request['datemax'])?$request['datemax']:'');
        $this->assign('content',isset($request['content'])?$request['content']:'');
        return $this->fetch();
    }
    //删除
    public function del(){
        //管理信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $check = Db::table('user_feedback')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态');
            }
            $update = Db::table('user_feedback')->where('id',$id)->update(['status'=>0]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 4,
                    'content'    => '删除意见反馈:ID '.$id,
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
    //审核
    public function shenhe(){
        //管理信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            if (!isset($id) || $id < 1) $this->echojson(201,'id错误');
            $status = $this->request->post('status');
            if (!isset($status) || !in_array($status, [2,3,4])) $this->echojson(201,'status错误');
            $check = Db::table('user_feedback')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'未查到信息');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已删除，不可操作');
            }
            if ($check['status'] == $status) {
                $this->echojson(201,'status未改变');
            }
            $update = Db::table('user_feedback')->where('id',$id)->update(['status'=>$status]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '审核意见反馈:ID '.$id,
                    'before'     => json_encode($check),
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
}