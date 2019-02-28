<?php
namespace app\admin\controller;

use think\Db;
//敏感字管理
class Badword extends Base{
    //敏感字显示、修改
    public function index(){
        //查询
        $data=Db::table('badword')->where('id',1)->value('word');
        if( $this->request->method() == "POST" ) {
            //接收信息
            $word = $this->request->post('word');
            if (empty($word)) {
            	$this->echojson(201,'敏感字不能为空');
            }
            if ($word == $data) {
            	$this->echojson(201,'无修改');
            }
            $admin = $this->admin;
            $old_data = Db::table('badword')->where('id',1)->find();
            $update = Db::table('badword')->where('id',1)->update(['word'=>$word]);
            if ($update) {
                $log_data = [
                    'admin_id'   => $admin['id'],
                    'type'       => 3,
                    'content'    => '修改敏感字',
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
        $this->assign('data',$data);
        return $this->fetch();
    }
}