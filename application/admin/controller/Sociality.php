<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/21
 * Time: 3:25 PM
 */

namespace app\admin\controller;



use think\Cache;
use think\Db;

class Sociality extends Base
{
    private $saveDir = 'uploads'.DS.'aboards'.DS;
    public function addList()
    {
        #dump(request());
        $rows = Db::table('sociality_type')->field('id,type_name')->where('pid',0)->where('status',1)->select();

        $data = [];

        $where = '';

        foreach($rows as $key=>$item){
            $result = Db::table('sociality_type')->field('id,type_name')->where('status',1)->where('pid',$item['id'])->select();
            if($result){
                $data[] = $result;
            }
        }
        $pageArr = ['0'=>''];
        if(!$where && !Cache::has('abpage')){

            $res = Db::table('sociality_type')->field('id,type_name,icon,ctime,order,pid')->where('status',1)->order('pid','asc')->select();
            $r = Db::table('s_sociality_type')->field('id,type_name,icon,ctime,order,type_id pid')->where('status',1)->order('type_id','asc')->select();
            $res = array_merge($res,$r);
            foreach ($res as $key => $value) {
                $res[$key]['ctime']=date('Y-m-d H:i:s',$value['ctime']);
            }
            Cache::set('count',count($res));
            $pageArr = array_chunk($res,10);
            $pageArr = json_encode($pageArr,JSON_UNESCAPED_UNICODE);
            Cache::set('abpage',$pageArr);
        }


        $pages = $this->request->post('pages')?$this->request->post('pages'):1;

        if($this->request->isAjax()){
            if($this->request->isPost()){
                $post = $this->request->post();
                if(isset($post['curr'])){
                    if(Cache::has('abpage')){
                        $pageArr = Cache::get('abpage');
                        $pageArr = json_decode($pageArr,true);
                    }
                    return json_encode($pageArr[$post['curr']-1],JSON_UNESCAPED_UNICODE);
                }
            }else{
                Cache::rm('abpage');
                Cache::rm('limitp');
            }
        }
        if(Cache::has('abpage')){
            $pageArr = Cache::get('abpage');
            $pageArr = json_decode($pageArr,true);
        }
        if(Cache::has('limitp')){
            $pages = Cache::get('limitp');
        }
        $rows = array_merge($data[0],$data[1]);
        $this->assign('list',$pageArr[$pages-1]);
        $this->assign('type',$rows);
        $this->assign('total',Cache::get('count'));

        return $this->fetch('sociality/index');

    }

    public function add()
    {
        $module = Db::table('sociality_type')->field('id,type_name,icon')->where('pid',0)->select();

        if($this->request->isPost()){
            $post = $this->request->post();
            $file = $this->request->file();

            if($post){
                $post = array_filter($post);
                $table = 'sociality_type';
                if(isset($post['type_id'])){
                    $table = 's_sociality_type';
                    unset($post['pid']);
                }
                $files = '';
                if(1){
                    $path = $this->uploads($file['image']);
                    $files = join(',',$path);
                }
                $post['icon'] = $files;
                $post['ctime'] = time();
                $bool = Db::table($table)->insert($post);
                if($bool){
                    Cache::rm('abpage');
                    Cache::rm('limitp');
                    Cache::rm('count');
                    $this->echojson(200,'成功');
                }
                $this->echojson(201,'失败');
            }
            $this->echojson(201,'失败');
        }
        $this->assign('soc',$module);
        return $this->fetch('sociality/add');

    }

    /**
     * 图片上传
     * @param $fileData
     * @param null $dir
     * @return array|bool
     */
    private function uploads($fileData,$dir = null){
        $path = [];
        if(!$fileData) return false;
        if(strtolower(gettype($fileData)) == strtolower('object')){
            $res = $fileData->validate(['ext'=>'jpg,png,jpeg'])->move(config('uploadPath'));
            if($res){
                $path[] = $this->saveDir.$res->getSaveName();
            }
        }else{
            foreach($fileData as $item){
                $res = $item->validate(['ext'=>'jpg,png,jpeg'])->move(config('uploadPath'));
                if($res){
                    $path[] = $this->saveDir.$res->getSaveName();
                }
            }
        }
        return $path;

    }
    # 分类联动
    public function linkage()
    {
        if($this->request->isPost()){
            $p_id = $this->request->post('id');
            if($p_id){
                $res = Db::table('sociality_type')->field('id,type_name')->where('pid',$p_id)->select();
                if($res){
                    return json_encode($res,JSON_UNESCAPED_UNICODE);
                }
            }

        }
    }

    public function edit()
    {
        $module = Db::table('sociality_type')->field('id,type_name,icon,pid')->where('pid',0)->select();
        $id = $this->request->get('id');
        $pid = $this->request->get('pid');
        $table = 'sociality_type';
        $field = 'id,type_name,icon,`order`,pid';
        $data = [];
        if($pid > 2){
            $table = 's_sociality_type';
            $field = 'id,type_name,icon,`order`,type_id pid';
            $data = Db::table('sociality_type')->field('id,type_name')->where('status',1)->where(' pid=1 or pid=2')->select();
        }
        $row = Db::table($table)->field($field)->where('id',$id)->where('status',1)->find();
        if($this->request->isAjax()){
            if($this->request->isPost()){
                $post = $this->request->post();
                $file = $this->request->file('image');
                if($file){

                    $path = $this->uploads($file);
                    $post['icon'] = join(',',$path);
                }
                if(isset($post['order'])) $post['order'] = 0;
                $table = '';

                if((isset($post['pid'])) || (!isset($post['type_id']) && !isset($post['pid']))){
                    $table = 'sociality_type';
                    $post['pid'] = $this->request->post('pid')?$this->request->post('pid'):0;
                }
                if(isset($post['type_id'])){
                    $table = 's_sociality_type';
                    $post['type_id'] = $this->request->post('type_id');
                }
                $id = '';
                if(isset($post['id'])){
                    $id = $post['id'];
                    unset($post['id']);
                }
                $post['ctime'] = time();
                $bool = Db::table($table)->where('id',$id)->update($post);
                if($bool){
                    Cache::rm('abpage');
                    Cache::rm('limitp');
                    Cache::rm('count');
                    return 200;
                }
                else return 201;
            }
        }
        $this->assign('edit',$row);
        $this->assign('data',$data);
        $this->assign('soc',$module);
        return $this->fetch('sociality/edit');
    }

}