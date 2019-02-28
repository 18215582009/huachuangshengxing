<?php
namespace app\admin\controller;

use think\Controller;
use think\Session;
use think\Db;
use Qcloud\Cos\Client;
//管理后台--公共文件
class Base extends Controller{
    protected $admin = [];   # 管理员账号信息

    public function __construct(){
        parent::__construct();
        $this->admin = Session::get('admin');
        self::check_login();//检查登录
        self::check_power();//自动判断权限
    }
    //验证登录
    protected function check_login(){
        $admin = $this->admin;
        if(empty($admin)){
            $this->success('请登录', '/admin/login/login.html');
        }
        //这里判断管理信息查询时间，每一分钟更新一次
        if ($admin['life_time'] < time()) {
            //查询最新的管理账号信息
            $new_admin = Db::table('admin')->where('id',$admin['id'])->find();
            //释放密码
            unset($new_admin['password']);
            //加入当前查询时间、Base.php中做每1分钟更新一次
            $new_admin['life_time'] = time()+60;
            //写入session缓存用
            Session::set('admin',$new_admin);
        }
    }
    //权限判断
    protected function check_power(){
        $url = '/'.$this->request->pathinfo();
        if ($url == '/admin') {
            $url = '/admin/index/index.html';
        }
        if (substr($url,-2-3) != '.html') {
            exit('URL错误');
        }
        $default = $this->defaultpath();//公共方法、不判断
        if (in_array($url, $default)) {
            return true;
        }
        $this->is_func_del($url);//判断菜单是否被删除
        $power = $this->getRolesPower();//获取当前账号角色权限
        if (!is_array($power) && $power == true) {
            return true;
        }
        elseif (!is_array($power) && $power == false) {
            exit('权限不足');
        }
        else{
            $roles_id = Db::table('admin_func')
                ->where('url',$url)
                ->where('status',1)
                ->cache(60)//60秒缓存
                ->value('id');
            if (!empty($roles_id) && in_array($roles_id, $power)) {
                return true;
            }
            else{
                exit('权限不足');
            }
        }
    }
    //输出json数据、方便API接口调用
    protected function echojson($status=200,$msg='',$data=[]){
        $datas = [
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data
        ];
        header('Content-type:text/json'); 
        echo json_encode($datas);
        exit;
    }
    //写管理员操作日志
    protected function writeAdminLog($data=[]){
        if (empty($data)) {
            return 0;
        }else{
            return Db::table('admin_log')->insertGetId($data);
        }
    }
    //获取所有菜单、添加角色用
    protected function getAllMenus(){
        $menus = Db::table('admin_func')
            ->field('id,level,pid,name,url')
            ->where('status',1)
            ->order('order_num asc')
            ->cache(60)//60秒缓存
            ->select();
        $data = [];
        //一级菜单
        foreach ($menus as $k => $v) {
            if ($v['level'] == 1) {
                $data[$v['id']]['name'] = $v['name'];
                $data[$v['id']]['id'] = $v['id'];
                $data[$v['id']]['pid'] = $v['pid'];
                $data[$v['id']]['child'] = [];
                unset($menus[$k]);
            }
        }
        //三级菜单
        $data3 = [];
        foreach ($menus as $k => $v) {
            if ($v['level'] == 3) {
                $data3[$v['pid']][$v['id']]['name'] = $v['name'];
                $data3[$v['pid']][$v['id']]['id'] = $v['id'];
                $data3[$v['pid']][$v['id']]['pid'] = $v['pid'];
                $data3[$v['pid']][$v['id']]['url'] = $v['url'];
                unset($menus[$k]);
            }
        }
        //二级菜单
        foreach ($menus as $k => $v) {
            if ($v['level'] == 2) {
                $data[$v['pid']]['child'][$v['id']]['name'] = $v['name'];
                $data[$v['pid']]['child'][$v['id']]['id'] = $v['id'];
                $data[$v['pid']]['child'][$v['id']]['pid'] = $v['pid'];
                $data[$v['pid']]['child'][$v['id']]['url'] = $v['url'];
                $data[$v['pid']]['child'][$v['id']]['child'] = [];
                unset($menus[$k]);
                foreach ($data3 as $k3 => $v3) {
                    if ($k3 == $v['id']) {
                        $data[$v['pid']]['child'][$v['id']]['child'] = $v3;
                        unset($data3[$k3]);
                    }
                }
            }
        }
        return $data;
    }
    //将角色权限处理成一维数组，方便其他地方调用
    protected function getRolesPower(){
        $admin = $this->admin;
        $functions = Db::table('admin_roles')
            ->where('id',$admin['roles_id'])
            ->where('status',1)
            ->cache(60)//60秒缓存
            ->value('functions');
        if ($functions == 'all') {
            $power = true;
        }
        elseif($functions == ''){
            $power = false;
        }
        else{
            $power = [];
            $functions = json_decode($functions,true);
            foreach ($functions as $k => $v) {
                $power[] = $v[0];
                if (isset($v['child'])) {
                    foreach ($v['child'] as $k1 => $v1) {
                        $power[] = $v1[0];
                        if (isset($v1['child'])) {
                            foreach ($v1['child'] as $k2 => $v2) {
                                $power[] = $v2[0];
                            }
                        }
                    }
                }
            }
        }
        return $power;
    }
    //单张图片上传
    protected function Upload($dir="uploads",$width=0,$height=0){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        if(empty($file)){return false;}
        $Path =  'public' . DS . $dir ;

        $info = $file->validate(['size'=>5242880,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . $Path  ,true,false);
        if($info){
            $imgpath2 = $info->getSaveName();
            if($width!=0 && $height!=0){
                $image = \think\Image::open($imgpath2);
                //将图片裁剪为300x300并保存为crop.png
                $image->crop($width,$height)->save($imgpath2);
            }
            return $dir. DS .$imgpath2;
        }else{
            // 上传失败获取错误信息
            $this->error = $file->getError();
            return  $file->getError();
        }
    }
    //上传图片到cos
    protected function UploadToCos($dir="uploads/test"){
        $config = [
            'timeout' => 60,
            'region' => 'ap-chengdu', #地域，如ap-guangzhou,ap-beijing-1
            'credentials' =>[
                'appId' => '1258260532',
                'secretId' => 'AKIDNR6Y0H1ilbR7kDFDVZg5f2W2ueR7AvnP',
                'secretKey' => 'aZdTUav1q6nFExhIkwFSPW8lHm2Teibd',
            ],
        ];
        $cosClient = new Client($config);
        $file = request()->file('image');
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));//格式
        if ( !in_array($ext, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf']) ) {
            $this->echojson(201,'图片格式错误，仅支持gif,jpg,jpeg,bmp,png,swf');
        }
        if ($file->getsize() > 5242880) {//图片大小、最大5M
            $this->echojson(201,'图片不能大于5M');
        }
        if ($file) {
            try {
                $result = $cosClient->putObject(
                    [
                        'Bucket' => 'ischool-1258260532',
                        'Key' => $dir.'/'.date("Ymd").'/'.md5(microtime()).'.'.$ext,
                        'Body' => fopen($file->getInfo()['tmp_name'], 'rb'),
                    ]
                );
                $url = str_replace('https://ischool-1258260532.cos.ap-chengdu.myqcloud.com/', '', $result['ObjectURL']);
                $url = str_replace('http://ischool-1258260532.cos.ap-chengdu.myqcloud.com/', '', $result['ObjectURL']);
                $url = urldecode($url);
                $this->echojson(200,'上传成功',['url'=>$url]);
            } catch (\Exception $e) {
                $this->echojson(201,'上传失败，请重试，失败原因：'.$e);
            }
        }
    }
    //判断菜单是否被删除、防止管理员直接访问菜单url
    protected function is_func_del($url){
        $check = Db::table('admin_func')->where('url',$url)->cache(60)->value('status');
        if (is_null($check)) {
            exit('url不存在');
        }
        if ($check==0) {
            exit('url已被禁用');
        }
    }
    //三级权限判断、html页面里面调用
    public function is_power($url){
        if (substr($url,-2-3) != '.html') {
            exit('URL错误');
        }
        $check = Db::table('admin_func')->where('url',$url)->cache(60)->value('status');
        if (is_null($check)) {
            exit('url不存在');
        }
        if ($check==0) {
            return false;
        }
        $power = $this->getRolesPower();//获取当前账号角色权限
        if (!is_array($power) && $power == true) {
            return true;
        }
        elseif (!is_array($power) && $power == false) {
            return false;
        }
        else{
            $roles_id = Db::table('admin_func')
                ->where('url',$url)
                ->where('status',1)
                ->cache(60)//60秒缓存
                ->value('id');
            if (!empty($roles_id) && in_array($roles_id, $power)) {
                return true;
            }
            else{
                return false;
            }
        }
    }
    //权限管理、默认urlpath、比如首页、欢迎页
    protected function defaultpath(){
        return [
            '/admin/index/index.html',
            '/admin/index/welcome.html',
            '/admin/index/changepw.html',
            '/admin/sociality/linkage.html'
        ];
    }
}
