<?php
namespace app\admin\controller;

use think\Db;
//勋章管理
class Medal extends Base{
    //列表
    public function index(){
    	//查询
    	$page_data = Db::name('medal_sys')->where('status = 1')->paginate(10);
        $data = $page_data->items();
        
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
        $admin = $this->admin;
        //添加提交判断
        if ($this->request->method() == 'POST') {
            //图片上传
            if ( isset($_FILES['image']) && $_FILES['image']['error'] == 0 ) {
                $this->uploadimg();
            }
            //接收信息
            $data = $this->request->post();
            //数据验证
            if(!isset($data['name']) || empty($data['name'])) $this->echojson(201,'请输入名称');
            if(!isset($data['grade']) || empty($data['grade'])) $this->echojson(201,'请输入等级');
            if(!isset($data['url']) || empty($data['url'])) $this->echojson(201,'请上传图片');
            $data['create_time'] = date('Y-m-d H:i:s');
            $new_id = Db::table('medal_sys')->insertGetId($data);
            if ($new_id) {
                //写操作日志
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '添加勋章:ID '.$new_id,
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
    //修改
    public function edit(){
        //添加提交判断
        if ($this->request->method() == 'POST') {
            //图片上传
            if ( isset($_FILES['image']) && $_FILES['image']['error'] == 0 ) {
                $this->uploadimg();
            }
            //管理信息
            $admin = $this->admin;
            //接收信息
            $data = $this->request->post();
            //数据验证
            if(!isset($data['id']) || empty($data['id'])) $this->echojson(201,'缺少参数');
            if(!isset($data['name']) || empty($data['name'])) $this->echojson(201,'请输入名称');
            if(!isset($data['grade']) || empty($data['grade'])) $this->echojson(201,'请输入等级');
            if(!isset($data['url']) || empty($data['url'])) $this->echojson(201,'请上传图片');
            $id = $data['id'];
            $check = Db::table('medal_sys')->field('id,name,url,grade,sort')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'参数错误');
            }
            if ($data == $check) {
                $this->echojson(201,'未做任何修改');
            }
            unset($data['id']);
            $old_data=Db::table('medal_sys')->where('id',$id)->find();
            $update = Db::table('medal_sys')->where('id',$id)->update($data);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '修改勋章:ID '.$id,
                    'before'     => json_encode($old_data),
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
        $data = Db::table('medal_sys')->where('id',$id)->find();
        $this->assign('data',$data);
        return $this->fetch();
    }
    //删除
    public function del(){
        $admin = $this->admin;
        if ($this->request->method() == 'POST') {
            //接收信息
            $id = $this->request->post('id');
            $check = Db::table('medal_sys')->where('id',$id)->find();
            if (empty($check)) {
                $this->echojson(201,'对象不存在，或参数错误');
            }
            if ($check['status'] == 0) {
                $this->echojson(201,'已是删除状态');
            }

            $update = Db::table('medal_sys')->where('id',$id)->update(['status'=>0]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 4,
                    'content'    => '删除勋章:ID '.$id,
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
    //图片上传
    private function uploadimg(){
        $data['url'] = $this->Upload('uploads'. DS .'medal');

        if($data['url'] && empty($this->error)){
            $this->echojson(200,'图片上传成功',$data);
        }
        $this->echojson(201,'图片上传失败，请重试');
    }
}