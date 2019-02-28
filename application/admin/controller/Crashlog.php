<?php
namespace app\admin\controller;

use think\Db;
//用户 app崩溃日志
class Crashlog extends Base{
    //列表
    public function index(){
        $where = "a.id >= 1";//拼接搜索语句
        $request = $this->request->get();
        $fanye = ['comefrom'=>'','content'=>'','phone_model'=>'','status'=>''];
        if ( isset($request['comefrom']) && $request['comefrom'] !='' ) {
            $where = " a.comefrom = '".$request['comefrom']."'";
            $fanye['comefrom']=$request['comefrom'];
        }
        if ( isset($request['content']) && $request['content'] !='' ) {
            $where .= " and a.content like '%".$request['content']."%'";
            $fanye['content']=$request['content'];
        }
        if ( isset($request['phone_model']) && $request['phone_model'] !='' ) {
            $where .= " and a.phone_model = '".$request['phone_model']."'";
            $fanye['phone_model']=$request['phone_model'];
        }
        if ( isset($request['status']) && $request['status'] !='' ) {
            $where .= " and a.status = ".$request['status'];
            $fanye['status']=$request['status'];
        }
        //查询已有机型
        $phone_models = Db::table('crash_log')->distinct(true)->field('phone_model')->cache(60)->select();
    	//查询角色名
    	$page_data = Db::name('crash_log')
            ->field('a.*,b.username,b.nickname')
            ->alias('a')
            ->join('users b','a.user_id = b.user_id','left')
            ->where($where)
            ->order('a.status asc')
            ->paginate(10,false,['query' => request()->param()]);
        $data = $page_data->items();
        //将评论图片json_decode、状态转汉字
        $status = ['1'=>'待解决','2'=>'解决中','3'=>'未解决','4'=>'已解决'];
        foreach ($data as $k => $v) {
            $data[$k]['status_name'] = $status[$v['status']];
            $data[$k]['content'] = mb_substr($v['content'], 0,100,'UTF-8');
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
        $this->assign('phone_models',$phone_models);
        $this->assign('status',$status);
        return $this->fetch('log/crashlog');
    }
    //显示单个日志内容
    public function show(){
        $id = $this->request->get('id');
        $check = Db::table('crash_log')->where('id',$id)->cache(60)->value('content');
        if (empty($check)) {
            $this->echojson(201,'参数错误');
        }
        $this->assign('check',$check);
        return $this->fetch('log/crashlogshowone');
    }
    //解决
    public function shenhe(){
        //管理信息
        $admin = $this->admin;
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            if (!isset($id) || $id < 1) $this->echojson(201,'id错误');
            $status = $this->request->post('status');
            if (!isset($status) || !in_array($status, [2,3,4])) $this->echojson(201,'status错误');
            $check = Db::table('crash_log')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'未查到信息');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已删除，不可操作');
            }
            if ($check['status'] == $status) {
                $this->echojson(201,'status未改变');
            }
            $update = Db::table('crash_log')->where('id',$id)->update(['status'=>$status]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '解决app崩溃日志:ID '.$id,
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