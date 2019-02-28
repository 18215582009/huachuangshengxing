<?php
namespace app\admin\controller;

use think\Db;
//管理后台-banner管理
class Banner extends Base
{
    //banner列表
    public function index(){
    	//查询banner
    	$page_data = Db::name('banner')->where('status > 0')->paginate(10);
        $data = $page_data->items();
        
        $page['total'] = $page_data->total();
        $page['current'] = $page_data->currentPage();
        $page['last'] = $page_data->lastPage();
        $this->assign('data',$data);
        $this->assign('page_data',$page_data);
    	$this->assign('page',$page);
        return $this->fetch();
    }
    //添加banner
    public function add(){
        $admin = $this->admin;
        //添加提交判断
        if ($this->request->method() == "POST") {
            //图片上传
            if ( isset($_FILES['image']) && $_FILES['image']['error'] == 0 ) {
                $this->uploadimg();
            }
            //接收信息
            $data = $this->request->post();
            //数据验证
            if(!isset($data["type"]) || empty($data["type"])) $this->echojson(201,"请选择类别！");
            if(!isset($data["url"]) || empty($data["url"])) $this->echojson(201,"请上传图片");
            if(!isset($data["status"]) || empty($data["status"])) $this->echojson(201,"请选择状态");
            //自动显示时间判断
            if (isset($data["status"]) && $data["status"]==3) {
                if(empty($data["start_time"])) $this->echojson(201,"请选择开始时间");
                if(empty($data["end_time"])) $this->echojson(201,"请选择结束时间");
                $data["status"] = 2;
                $data["is_auto"] = 1;
                $data["start_time"] = strtotime($data["start_time"]);
                $data["end_time"] = strtotime($data["end_time"]);
            }

            $data['ctime'] = time();
            $new_banner_id = Db::table('banner')->insertGetId($data);
            if ($new_banner_id) {
                //写操作日志
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '添加banner:ID '.$new_banner_id,
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
    //修改banner
    public function edit(){
        //添加提交判断
        if ($this->request->method() == "POST") {
            //图片上传
            if ( isset($_FILES['image']) && $_FILES['image']['error'] == 0 ) {
                $this->uploadimg();
            }
            //管理信息
            $admin = $this->admin;
            //接收信息
            $data = $this->request->post();
            //数据验证
            if(!isset($data['type']) || empty($data['type'])) $this->echojson(201,"请选择类别！");
            if(!isset($data['url']) || empty($data['url'])) $this->echojson(201,"请上传图片");
            if(!isset($data['status']) || empty($data['status'])) $this->echojson(201,"请选择状态");
            $banner_id = $data['banner_id'];
            unset($data['banner_id']);
            $check = Db::table('banner')
                ->field('type,title,url,b_link,remarks,is_auto,start_time,end_time,sort,status')
                ->where('banner_id',$banner_id)
                ->find();
            if (empty($data)) {
                $this->echojson(201,"参数错误");
            }
            if ($data["status"]==3) {
                if(empty($data["start_time"])) $this->echojson(201,"请选择开始时间");
                if(empty($data["end_time"])) $this->echojson(201,"请选择结束时间");
                if ($check["is_auto"] == 1) {
                    $data['status'] = $data['old_status'];
                }else{
                    $data['is_auto'] = 1;
                    $data['status'] = 2;
                }
            }else{
                if ($check["is_auto"] == 1) {
                    $data['start_time'] = null;
                    $data['end_time'] = null;
                    $data['is_auto'] = 0;
                }
            }
            $data["start_time"]=!empty($data["start_time"])?strtotime($data["start_time"]):null;
            $data["end_time"]=!empty($data["end_time"])?strtotime($data["end_time"]):null;
            unset($data['old_status']);
            unset($check['is_auto']);
            if ($data == $check) {
                $this->echojson(201,"未做任何修改");
            }

            $check2 = Db::table('banner')->where('banner_id',$banner_id)->find();
            $update = Db::table('banner')->where('banner_id',$banner_id)->update($data);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '修改banner:ID'.$banner_id,
                    'before'     => json_encode($check2),
                    'create_time'=> date('Y-m-d H:i:s'),
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'修改成功');
            }
            else{
                $this->echojson(201,'修改失败，请重试');
            }
        }
        //管理信息
        $admin = $this->admin;
        $id = $this->request->get('id');
        $data = Db::table('banner')->where('banner_id',$id)->find();
        $data['start_time'] = !is_null($data['start_time'])?date('Y-m-d H:i:s',$data['start_time']):'';
        $data['end_time'] = !is_null($data['end_time'])?date('Y-m-d H:i:s',$data['end_time']):'';
        $this->assign('data',$data);
        return $this->fetch();
    }
    //删除banner
    public function del(){
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $check = Db::table('banner')->where('banner_id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'对象不存在，或参数错误');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态');
            }
            $admin = $this->admin;//管理员信息
            $update = Db::table('banner')->where('banner_id',$id)->update(['status'=>0]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 4,
                    'content'    => '删除banner:ID '.$id,
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
    //隐藏banner
    public function stop(){
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $status = $this->request->post('status');
            $check = Db::table('banner')->field('status')->where('banner_id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['status'] == 2) {
                $this->echojson(201,'已是隐藏状态');
            }
            $admin = $this->admin;//管理员信息
            $update = Db::table('banner')->where('banner_id',$id)->update(['status'=>2]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '隐藏banner ID:'.$id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'隐藏成功');
            }
            else{
                $this->echojson(201,'隐藏失败，请重试');
            }
        }
    }
    //显示banner
    public function start(){
        if ($this->request->method() == "POST") {
            //接收信息
            $id = $this->request->post('id');
            $status = $this->request->post('status');
            $check = Db::table('banner')->field('status')->where('banner_id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($check['status'] == 1) {
                $this->echojson(201,'已是显示状态');
            }
            $admin = $this->admin;//管理员信息
            $update = Db::table('banner')->where('banner_id',$id)->update(['status'=>1]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '显示banner ID:'.$id,
                    'create_time'=> date('Y-m-d H:i:s')
                ];
                $this->writeAdminLog($log_data);
                $this->echojson(200,'显示成功');
            }
            else{
                $this->echojson(201,'显示失败，请重试');
            }
        }
    }
    //图片上传
    private function uploadimg(){
        $data['url'] = $this->UploadToCos('uploads/Banner');

        if($data['url'] && empty($this->error)){
            $this->echojson(200,'图片上传成功',$data);
        }
        $this->echojson(201,'图片上传失败，请重试');
    }
}