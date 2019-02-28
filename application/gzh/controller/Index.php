<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/2/19
 * Time: 2:21 PM
 */

namespace app\gzh\controller;
use think\Cache;
use think\Db;

class Index extends Com
{
    public function index()
    {
        $uid = $this->request->get('u')?$this->request->get('u'):false;
        $info = [];
        if(!Cache::has('user_'.$uid)){
            $info = Db::table('users')->alias('u')
                ->field('u.user_id,u.sex,u.email,u.nickname,u.phone,u.user_id,u.head_image,s.id sid,s.sname school_name,u.college')
                ->join('school s','u.school=s.id','left')
                ->where('u.user_id',$uid)
                ->find();
            Cache::set('user_'.$uid,json_encode($info,JSON_UNESCAPED_UNICODE));
        }else{
            $info = json_decode(Cache::get('user_'.$uid),true);
        }
        $this->assign('u_info',$info);
        return view();
    }


    /**
     * 添加简历
     * @return string
     * @throws \think\Exception
     */
    public function addResume()
    {
        if($this->request->isAjax()){
            if($this->request->isPost()){
                $post = $this->request->post();
                $uid = $post['u'];
                unset($post['u']);
                $row = Db::table('u_resume')->where('user_id',$uid)->find();
                $post['user_id'] = intval($uid);
                $post = array_filter($post);
                $post['ctime'] = time();
                if(empty($row)){

                    $bool = Db::table('u_resume')->insert($post);
                    if(!$bool){
                        return json_encode(['status'=>210],JSON_UNESCAPED_UNICODE);
                    }else{
                        Cache::rm('preview_'.$uid);
                        return json_encode(['status'=>200,'id'=>$uid],JSON_UNESCAPED_UNICODE);
                    }

                }else{
                    $bool = Db::table('u_resume')->where('user_id',$uid)->update($post);
                    if(!$bool){
                        return json_encode(['status'=>210],JSON_UNESCAPED_UNICODE);
                    }else{
                        Cache::rm('preview_'.$uid);
                        return json_encode(['status'=>200,'id'=>$uid],JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        }
    }

    /**
     * 中转我的简历
     * @return int
     */
    public function select_u()
    {
        $uid = $this->request->post('u')?$this->request->post('u'):false;
        $row = Db::table('u_resume')->where('user_id',$uid)->find();
        if($row){
            return 200;
        }else{
            return 201;
        }

    }


    /**
     * 简历
     * @return \think\response\View
     */
    public function resume()
    {
        $uid = $this->request->get('u')?$this->request->get('u'):false;
        $type = Db::table('recruit_type')->field('id,recruit_name')->select();

        $info = Cache::get('user_'.$uid);
        $info = json_decode($info,true);
        if($info){
            $info['sex'] = $info['sex'] == 1 ?'男':'女';
        }
        $info['is_empty'] = 1;
        $this->assign('u_info',$info);
        $this->assign('type',$type);
        return view();
    }

    /**
     * 预览简历
     * @return \think\response\View
     */
    public function preview()
    {
        $uid = $this->request->get('u');
        if($uid){
            $info = Db::table('users')->alias('u')
                ->field('u.user_id,u.sex,u.email,u.nickname,u.phone,u.user_id,u.head_image,s.id sid,s.sname school_name,u.college')
                ->join('school s','u.school=s.id','left')
                ->join('u_resume ur','ur.user_id=u.user_id','left')
                ->where('u.user_id',$uid)
                ->find();
            $data = [];
            if($info){
                $info['sex'] = $info['sex'] == 1 ?'男':'女';
                $edit = Db::table('u_resume')->field('*')
                    ->where('user_id',$uid)
                    ->find();
                if(Cache::has('preview_'.$uid)){
                    $edit = Cache::get('preview_'.$uid);
                    #$edit['flag'] = empty($edit)?0:1;
                    if($info && $edit){
                        $data = array_merge($edit,$info);
                    }
                }else{
                    if($edit){
                        $gongshi = array_filter(explode(',',$edit['experience']));
                        $type = array_filter(explode('%',$edit['wanted']));
                        $experience = [];
                        foreach($gongshi as $k=>$v){
                            $arr = array_filter(explode('%',$v));
                            $experience[$k]['name'] = $arr[0];
                            $experience[$k]['gangwei'] = $arr[1];
                        }
                        $type_name = [];
                        foreach($type as $k=>$v){
                            $arr = Db::table('recruit_type')->field('recruit_name')->where('id',$v)->find();
                            $type_name[$k]['tid'] = $v;
                            $type_name[$k]['tname'] = $arr['recruit_name'];
                        }
                        $edit['experience'] = $experience;
                        $edit['wanted'] = $type_name;
                        $edit['flag'] = 1;

                        $data = array_merge($info,$edit);
                        Cache::set('preview_'.$uid,$data);
                    }
                }

            }
            if(empty($data['experience'])){
                $data['is_empty'] = '1';
            }else{
                $data['is_empty'] = '2';
            }
            $this->assign('u_info',$data);
        }

        return view();
    }



    public function edit()
    {

        $uid = $this->request->get('u')?$this->request->get('u'):false;

        $info = Db::table('users')->alias('u')
            ->field('u.user_id,u.sex,u.email,u.nickname,u.phone,u.user_id,u.head_image,s.id sid,s.sname school_name,u.college')
            ->join('school s','u.school=s.id','left')
            ->join('u_resume ur','ur.user_id=u.user_id','left')
            ->where('u.user_id',$uid)
            ->find();
        if($info){
            $info['sex'] = $info['sex'] == 1 ?'男':'女';
        }
        $type = Db::table('recruit_type')->field('id,recruit_name')->select();
        $data = [];
        if(Cache::has('preview_'.$uid)){
            $edit = Cache::get('preview_'.$uid);
            #$edit['flag'] = empty($edit)?0:1;
            if($info && $edit){
                $data = array_merge($edit,$info);
            }

        }else{

            $edit = Db::table('u_resume')->field('*')
                ->where('user_id',$uid)
                ->find();
            if($edit) {
                $gongshi = array_filter(explode(',', $edit['experience']));
                $type = array_filter(explode('%', $edit['wanted']));
                $experience = [];
                foreach ($gongshi as $k => $v) {
                    $arr = array_filter(explode('%', $v));
                    $experience[$k]['name'] = $arr[0];
                    $experience[$k]['gangwei'] = $arr[1];
                }
                $type_name = [];
                foreach ($type as $k => $v) {
                    $arr = Db::table('recruit_type')->field('recruit_name')->where('id', $v)->find();
                    $type_name[$k]['tid'] = $v;
                    $type_name[$k]['tname'] = $arr['recruit_name'];
                }
                $edit['experience'] = $experience;
                $edit['wanted'] = $type_name;

                $data = array_merge($info, $edit);
            }
        }
        if(empty($data['wanted'])){
            $data['is_wanted'] = '1';
        }else{
            $data['is_wanted'] = '2';
        }
        if(empty($data['experience'])){
            $data['is_empty'] = '1';
        }else{
            $data['is_empty'] = '2';
        }
        $this->assign('u_info',$data);
        $this->assign('type',$type);
        $type = Db::table('recruit_type')->field('id,recruit_name')->select();
        $this->assign('type',$type);
        return view();

    }


    /**
     * 中转预览
     * @return mixed
     */
    public function showPreview()
    {
        if($this->request->isAjax()){

            $post = $this->request->post();
            if($post){
                $type = array_filter(explode('%',$post['wanted']));
                $gongshi = array_filter(explode(',',$post['experience']));
                $data = [];
                foreach($gongshi as $k=>$v){
                    $arr = array_filter(explode('%',$v));
                    $data[$k]['name'] = $arr[0];
                    $data[$k]['gangwei'] = $arr[1];
                }
                $type_name = [];
                foreach($type as $k=>$v){
                    $arr = Db::table('recruit_type')->field('recruit_name')->where('id',$v)->find();
                    $type_name[$k]['tid'] = $v;
                    $type_name[$k]['tname'] = $arr['recruit_name'];
                }
                $edit = Db::table('u_resume')->field('id')
                    ->where('user_id',$post['u'])
                    ->find();
                if($edit){
                    $post['flag'] = 1;
                }else{
                    $post['flag'] = 0;
                }
                $post['wanted'] = $type_name;
                $post['experience'] = $data;
                Cache::set('preview_'.$post['u'],$post,300);
                return $post['u'];
            }

        }


    }

    /**
     * 我的兼职
     *
     * @return \think\response\View
     */
    public function meresume()
    {
        $uid = $this->request->get('u')?$this->request->get('u'):false;
        $rows = $res = Db::table('recruit_enroll')->alias('re')
            ->field('re.id reid,r.id,r.rec_address,rt.recruit_name,r.settlement,r.requirement,r.recruit_pay,r.area,r.number,r.recruit_times,e.enterprise_name,e.enter_logo,r.start_time')
            ->join('recruit r','r.id=re.recruit_id','left')
            ->join('recruit_type rt','r.rec_type_id=rt.id','left')
            ->join('enterprise e','e.id=r.enter_id','left')
            ->where('re.user_id',$uid)
            ->select();
        foreach($rows as $k=>$v){
            $time = $v['start_time'] - time();
            $days = intval($time/86400);
            $remain = $time%86400;
            $rows[$k]['start_time'] = $days.'.'.intval($remain/3600).'天';
            $rows[$k]['is_enroll'] = 1;
        }
        $this->assign('info',$rows);
        return view();
    }


    /**
     * 显示修改资料页面
     * @return \think\response\View
     */
    public function editinfo()
    {
        $u = $this->request->get('u')?$this->request->get('u'):false;
        $res = Db::table('users')->alias('u')
            ->field('u.user_id,u.username,u.email,u.head_image,u.nickname,u.college,u.sex,u.phone,u.college,u.student,u.email,u.zhuanye,u.user_birth,s.id sid,s.sname school_name')
            ->join('school s','s.id=u.school','left')
            ->where('u.user_id',$u)->find();
        $res['sex'] = $res['sex'] == 1?'男':'女';
        $res['user_birth'] = date('Y-m-d',$res['user_birth']);
        $this->assign('info',$res);
        return view();
    }

    /**
     * 修改资料
     * @return int
     * @throws \think\Exception
     */
    public function upEdit(){
        if($this->request->isAjax()){
            if($this->request->isPost()){
                $post = $this->request->post();

                $school = Db::table('school')->where('sname',$post['school'])->where('id',$post['sid'])->find();
                if(!$school){
                    $id = Db::table('school')->insertGetId(['sname'=>$post['school']]);
                    $post['school'] = $id;
                }else{
                    $post['school'] = $post['sid'];
                }
                $file = $this->request->file('head');
                if($file){
                    $head = $this->uploads($file);
                    $post['head_image'] = $head[0];
                }
                $post['utime'] = time();
                $post['user_birth'] = strtotime($post['user_birth']);

                $post = array_filter($post);
                $uid = $post['u'];
                unset($post['u']);
                unset($post['sid']);
                $bool = Db::table('users')->where('user_id',$uid)->update($post);
                if($bool){
                    Cache::rm('preview_'.$uid);
                    Cache::rm('user_'.$uid);
                    return json_encode(['status'=>200,'uid'=>$uid]);
                }else{
                    return json_encode(['status'=>201]);
                }

            }
        }
    }


    /**
     * 退出登陆
     * @return int
     */
    public function loginOut()
    {
        $uid = $this->request->post('u')?$this->request->post('u'):false;
        Cache::rm('user_'.$uid);
        Cache::rm('uid');
        Cache::rm('preview'.$uid);
        return 200;

    }


    public function test()
    {

        [
            [
                [
                    'cid'=>1,   # 当前 题目id
                    'tid'=>2,   # 绑定题目的id
                    'condition'=>'' # 这里是一个条件模块的第一个条件，根据后面有没有条件自动变化
                ],  # 这个相当于绑定的一个题目
                [
                    'cid'=>1,
                    'tid'=>2,
                    'condition'=>'或'
                ],
                [],
            ], # 这一层数组相当于一个模块条件
            [],
            [],
        ];


    }


}