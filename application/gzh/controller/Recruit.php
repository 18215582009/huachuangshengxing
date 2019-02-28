<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/2/18
 * Time: 9:51 AM
 */

namespace app\gzh\controller;


use think\Cache;
use think\Controller;
use think\Db;

class Recruit extends Com
{
    private $len =8;
    public function index()
    {
        $banner = Db::table('banner')->where('type',1)->select();

        $page = $this->request->post('page')?$this->request->post('page'):1;
        $uid = $this->request->param('u')?$this->request->param('u'):Cache::get('uid');
        $start = ($page-1) * $this->len;
        /*$rows = Db::table('recruit')->alias('r')
            ->field('r.id,r.rec_address,rt.recruit_name,r.settlement,r.requirement,r.recruit_pay,r.area,r.number,r.recruit_times,e.enterprise_name,e.enter_logo,r.start_time')
            ->join('recruit_type rt','r.rec_type_id=rt.id','left')
            ->join('enterprise e','e.id=r.enter_id','left')
            ->where('start_time','>',time())
            ->order('id','asc')
            ->limit($start,$this->len)
            ->select();*/
        $time = time();
        $sql = " select r.id,r.rec_address,rt.recruit_name,r.settlement,r.requirement,r.recruit_pay,r.area,r.number,r.recruit_times,e.enterprise_name,e.enter_logo,r.start_time from recruit as r LEFT JOIN recruit_type as rt ON r.rec_type_id=rt.id LEFT JOIN enterprise as e ON e.id=r.enter_id where r.id not in (select rn.recruit_id from recruit_enroll rn where rn.user_id={$uid}) and r.start_time>{$time} ORDER BY r.id DESC";

        $rows = Db::query($sql);

        foreach($rows as $k=>$v){
            $time = $v['start_time'] - time();
            $days = intval($time/86400);
            $remain = $time%86400;
            $rows[$k]['start_time'] = $days.'.'.intval($remain/3600).'天';
        }


        $number = mt_rand(500,1000);
        $this->assign('list',$rows);
        $this->assign('number',$number);
        $this->assign('banner',$banner);
        return view();
    }

    /**
     * 兼职详情
     * @return \think\response\View
     */
    public function des()
    {
        $rid = $this->request->get('r')?$this->request->get('r'):false;
        $uid = $this->request->get('u')?$this->request->get('u'):false;
        $rows = Db::table('recruit')->alias('r')
            ->field('r.id,r.rec_address,rt.recruit_name,r.settlement,r.requirement,r.recruit_pay,r.area,r.number,r.recruit_times,e.enterprise_name,e.enter_logo,r.start_time,r.city,r.big_time,r.description,e.phone,e.id eid')
            ->join('recruit_type rt','r.rec_type_id=rt.id','left')
            ->join('enterprise e','e.id=r.enter_id','left')
            ->where('r.id',$rid)
            ->find();

        $rows['start_time'] = date('Y-m-d H:i:s',$rows['start_time']);
        $sex = Db::table('users')->where('sex')->where('user_id',$uid)->find();
        $res = Db::table('recruit_enroll')->field('id')->where('user_id',$uid)
            ->where('recruit_id',$rid)->find();
        $rows['is_enroll'] = $res?1:2;  # 已经报名 2 未报名
        $rows['user_id'] = $uid;
        $rows['sex'] = $sex['sex']==1?'男':'女';  # 已经报名 2 未报名

        $this->assign('dec',$rows);
        return view();
    }

    /**
     * 公司信息
     * @return \think\response\View
     */
    public function info()
    {
        $eid = $this->request->get('e')?$this->request->get('e'):false;
        $row = Db::table('enterprise')->alias('e')
            ->field('e.enterprise_name,e.enter_logo,e.p_from,e.scale,e.financing,e.phone,e.enter_dis,e.enter_type,stationed,e.real_enter,e.area,e.city,e.info_address,e.id')
            ->where('e.id',$eid)
            ->find();


        $this->assign('info',$row);
        return view();
    }

    /**
     * 兼职报名
     * @return int
     */
    public function enrollRecruit()
    {
        if($this->request->isAjax()){
            if($this->request->isPost()){

                $post = $this->request->post();
                $enid = $post['enter_id'];
                unset($post['enter_id']);
                $uid = $post['u'];
                unset($post['u']);

                $res = Db::table('recruit_enroll')->field('id')->where('user_id',$uid)
                    ->where('recruit_id',$post['recruit_id'])->find();
                if(!$res){
                    $post['user_id'] = $uid;
                    $post['ctime'] = time();
                    $bool = Db::table('recruit_enroll')->insert($post);
                    if($bool){
                        $bool = $this->sendResume($uid,$enid);
                        if(!$bool) return 220;
                        return 200;
                    }else{
                        return 201;
                    }
                }else{
                    return 201;
                }

            }
        }
    }

    /**
     * 制作微简历发给公司
     *
     * @param $uid
     * @param $enid
     * @return bool
     */
    private function sendResume($uid,$enid)
    {
        if($uid){
            $row = Db::table('u_resume')->alias('ur')
                ->field('ur.*,u.email,u.user_id uid,u.nickname,u.sex,u.username phone')
                ->where('ur.user_id',$uid)
                ->join('users u','u.user_id=ur.user_id','left')
                ->find();
            $enter = Db::table('enterprise')->field('email,id,enterprise_name')->where('id',$enid)->find();

            if($row){
                $str = '<h1 style="margin-left:100px;">微简历</h1>';
                $gongshi = array_filter(explode(',', $row['experience'])); # 工作经历
                $type = array_filter(explode('%', $row['wanted']));
                $sex = $row['sex']==1?'男':'女';
                $str .='<h2>个人信息</h2><span style="margin-left: 30px;">姓名: </span><span style="font-size:18px;">'.$row['nickname'].'&nbsp;&nbsp;</span><span>性别: </span><span>'.$sex.'&nbsp;&nbsp;</span><span>联系电话: </span><span>'.$row['phone'].'</span><br/><span style="margin-left: 30px;">学校: </span><span>'.$row['attend'].'&nbsp;&nbsp;</span><span>电子邮箱: </span><span>'.$row['email'].'</span>';
                $str .='<h2>个人简介</h2><p style="margin-left: 30px;text-indent:1em;">'.$row['info_content'].'</p>';
                $str .='<h2>求职意向</h2>';
                foreach ($type as $k => $v) {
                    $arr = Db::table('recruit_type')->field('recruit_name')->where('id', $v)->find();
                    $str .= '<span style="margin-left: 30px;">岗位: </span><span>'.$arr['recruit_name'].'&nbsp;&nbsp;</span>';
                }
                $str .='<h2>工作经历</h2>';
                foreach ($gongshi as $k => $v) {
                    $arr = array_filter(explode('%', $v));
                    $str .='<span style="margin-left: 30px;">公司名:</span><span>'.$arr[0].'&nbsp;&nbsp;</span><span>职位:</span><span>'.$arr[1].'</span>';
                }

                $smtp = new Smtp();
                $res = $smtp->stmp($str,$enter['email'],$row['nickname']);
                if($res == 1){
                    return true;
                }else{
                    return false;
                }

            }



        }




    }

    /**
     * 搜索兼职
     * @return string
     */
    public function search()
    {

        if($this->request->isAjax()){
            if($this->request->isPost()){

                $page = $this->request->post('page')?$this->request->post('page'):1;
                $search = $this->request->post('search')?$this->request->post('search'):false;
                $uid = $this->request->post('u')?$this->request->post('u'):false;
                //dump($this->request->post());

                $where = '';
                $rdid = Db::table('recruit_enroll')->field('recruit_id')->where('user_id',$uid)->select();
                if($rdid){
                    $str = '';
                    foreach($rdid as $k=>$v){
                        $str .= $v['recruit_id'].',';
                    }
                    $where = ' r.id not in ('.trim($str,',').') ';
                }

                if($search){
                    $where .= " and rt.recruit_name like '%{$search}%' ";
                    Cache::set('where_'.$uid,$search);
                }

                $start = ($page-1) * $this->len;

                $rows = Db::table('recruit_type')->alias('rt')
                    ->field('r.id,r.rec_address,rt.recruit_name,r.settlement,r.requirement,r.recruit_pay,r.area,r.number,r.recruit_times,e.enterprise_name,e.enter_logo,r.start_time')
                    ->join('recruit r','r.rec_type_id=rt.id','left')
                    ->join('enterprise e','e.id=r.enter_id','left')
                    ->where('start_time','>',time())
                    ->where($where)
                    ->order('id','desc')
                    #->limit($start,$this->len)
                    ->select();
                foreach($rows as $k=>$v){
                    $time = $v['start_time'] - time();
                    $days = intval($time/86400);
                    $remain = $time%86400;
                    $rows[$k]['start_time'] = $days.'.'.intval($remain/3600).'天';
                }

                $page = $start/$this->len;
                if(!empty($rows)){
                    return json_encode(['status'=>200,'data'=>$rows,'pages'=>$page,'where'=>$search],JSON_UNESCAPED_UNICODE);
                }else{

                    return json_encode(['status'=>201,'data'=>[],'pages'=>($page),'where'=>$search],JSON_UNESCAPED_UNICODE);
                }
            }
        }

    }


}