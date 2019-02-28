<?php
namespace app\admin\controller;

use think\Db;
//app配置 如app名称，金币名称等
class Appconf extends Base{
    //列表
    public function index(){
        $data = Db::table('appconf')->where('id',1)->select();
        // dump($data);exit;
        $this->assign('data',$data);
        return $this->fetch();
    }
    //修改
    public function edit(){
        $admin = $this->admin;//获取当前管理账号信息
        if ($this->request->method() == "POST") {
            //接收信息
            $data = $this->request->post();
            if ( !isset($data['app_name']) || empty($data['app_name']) ) {
                $this->echojson(201,'app名字不能为空');
            }
            if ( !isset($data['id_name']) || empty($data['id_name']) ) {
                $this->echojson(201,'id名字不能为空');
            }
            if ( !isset($data['currency_name']) || empty($data['currency_name']) ) {
                $this->echojson(201,'货币名字不能为空');
            }
            if ( !isset($data['cash_name']) || empty($data['cash_name']) ) {
                $this->echojson(201,'提现货币名字不能为空');
            }
            $id = $data['id'];
            unset($data['id']);
            $old_data = Db::table('appconf')->where('id',$id)->find();
            if ( empty($old_data) ) {
                $this->echojson(201,'未查到信息、参数错误');
            }
            //检查是否有修改
            if ($data['app_name']==$old_data['app_name'] && $data['id_name']==$old_data['id_name'] && $data['currency_name']==$old_data['currency_name'] && $data['cash_name']==$old_data['cash_name']) {
                $this->echojson(201,'无修改');
            }
            $update = Db::table('appconf')->where('id',$id)->update($data);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 2,
                    'content'    => '修改app配置信息',
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
        $data = Db::table('appconf')->where('id',$id)->find();
        if (empty($data)) {
            $this->error('参数错误');
        }
        $this->assign('data',$data);
        return $this->fetch();
    }

}