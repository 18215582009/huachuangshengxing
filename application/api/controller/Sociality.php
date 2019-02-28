<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/14
 * Time: 11:52 AM
 */

namespace app\api\controller;


use app\common\error\Error;

use think\Cache;

use think\Db;
use think\Paginator;

class Sociality extends Base
{

    protected $jurisdiction = [];                    # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    private $num = 10;
    private $len = 3;



    /**
     * 获取上船的板块及用户经常查询的活动项
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function getModule()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            if($uid){
                $res = Db::table('sociality_type')->field('type_name s_type_name,id pid,icon')
                    ->where('pid',0)
                    ->select();

                $data = Cache::get('st'.$uid);
                $p = [];
                if($data){
                    $data = json_decode($data,true);
                    arsort($data);

                    if($this->len >= count($data)) $this->len = $this->len - count($data);

                    else $this->len = 0;

                    $keys = array_keys($data);
                    $rows = [];
                    foreach ($keys as $k=>$item) {
                        if($k<3){
                            $key = explode('w',$item);
                            $p_id  = $key[1];
                            $p[] = (int)$p_id;
                            $result = Db::table('s_sociality_type')->alias('sst')
                                ->field('sst.type_name s_type_name,sst.id s_type_id,sst.icon,st.pid,st.id type_id,st.type_name')
                                ->join('sociality_type st','st.id=sst.type_id','left')
                                ->find($p_id);
                            $rows[] = $result;
                        }
                    }
                    $res = array_merge($res,$rows);
                }
                # 如果访问不足三条进行补充
                if($this->len){
                   $where = '';
                   if($p){
                       $where = ' sst.id not in ('. join(',',$p) .')';
                   }
                    $rows = Db::table('s_sociality_type')->alias('sst')
                        ->field('sst.type_name s_type_name,sst.id s_type_id,sst.icon,st.pid,st.id type_id,st.type_name')
                        # t_id 二级 pid 顶级 s_type_id 三级
                        ->join('sociality_type st','st.id=sst.type_id','left')
                        ->where('sst.status',1)
                        ->where($where)
                        ->limit(0,$this->len)
                        ->select();
                   $res = array_merge($res,$rows);
                }

                if($res){
                    return $this->json('获取成功',Error::$SUCCESS,$res);

                }else{
                    return $this->json('获取失败',Error::$OBTAIN_ERROR);
                }


            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }

        }
    }

    /**
     * 获取社交活动详情
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function detailsSoc()
    {
        if($this->request->isPost()){

            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $p_id = $this->request->post('plays_id')?$this->request->post('plays_id'):false; # 活动唯一id
            #$type_id = $this->request->post('s_type_id')?$this->request->post('s_type_id'):false; # 第三级分类id

            if((int)$uid && (int)$p_id){

                /*if($this->redis->get('playDetails'.$p_id)){

                    $arr = $this->redis->get('playDetails'.$p_id);
                    $data = json_decode($arr,true);
                    return $this->json('获取成功', Error::$SUCCESS, $data);
                }*/
                $sid = $this->schoolLimit($uid);
                $data = Db::table('plays')->alias('p')
                    ->field("p.event_title,p.p_start,p.ask,p.launch,p.aggregate p_address,p.description k_description,p.number,u.u_vip,u.user_id,u.nickname,p.plays_id,p.end_times p_end,st.id p_id,sst.id stid,p.images")
                    # stid 三级分类id # p_id 二级分类id
                    ->join('s_sociality_type sst','sst.id=p.about_type_id','left')
                    ->join('sociality_type st','st.id=sst.type_id','left')
                    ->join('users u','u.user_id=p.user_id')
                    ->where('u.school',$sid)
                    ->where('p.status',1)
                    ->find($p_id);

                if($data) {

                    $attr = Db::table('plays_attach')->where('plays_id', $data['plays_id'])
                        ->count();  # 获取已报名人数

                    $is_attr = Db::table('plays_attach')->where('plays_id',$data['plays_id'])
                        ->where('user_id',$uid)->find();
                    /*if ($data['number'] != 0) {
                        $stock = $redis->lLen('plays' . $data['plays_id']); # 剩余可参与人数

                        if ($stock != ($data['number'] - $attr) && ($data['number'] != $attr)) { # 缓存中和实际参与人员不对等

                            if ($stock < ($data['number'] - $attr)) {

                                $sto = ($stock - ($data['number'] - $attr));
                                $sto = abs($sto);

                                for ($i = 0; $i < $sto; $i++) {

                                    $redis->lPush('plays' . $data['plays_id'], 1);

                                }
                            } else {
                                $sto = (($data['number'] - $attr) - $stock);
                                $sto = abs($sto);
                                for ($i = 0; $i < $sto; $i++) {
                                    $redis->lPop('plays' . $data['plays_id']);
                                }
                            }
                        }

                    } */

                    $this->statistics($uid, $data['stid'], 'plays');  # 记录用户点击量
                    $activity_fabulous = Db::table('activity_fabulous')->field('status')
                        ->where('user_id',$uid)
                        ->where('type_name', '社交')->where('type_id', $p_id)->find();
                    $data['is_fabulous'] = $activity_fabulous['status'] ? $activity_fabulous['status'] : 0;
                    $data['attach'] = $attr;
                    $data['is_attach'] = $is_attr?1:2;  # 当前用户是否参加活动 1 以参加 2 未参加
                    #$arr = json_encode($data,JSON_UNESCAPED_UNICODE);
                    #$this->redis->set('playDetails'.$p_id,$arr);
                    return $this->json('获取成功', Error::$SUCCESS, $data);
                }else return $this->json('未获取到数据',Error::$OBTAIN_ERROR);

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }


        }
    }


    /**
     * 获取参加社交活动的人员
     *
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function attach()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $pid = $this->request->post('plays_id')?$this->request->post('plays_id'):false; # 活动唯一id
            if($uid && $pid){

                $res = Db::table('plays_attach')->alias('pa')
                    ->field('u.nickname,u.u_vip,u.user_id')
                    ->join('users u','u.user_id=pa.user_id','left')
                    ->where('pa.plays_id',$pid)
                    ->select();

                if($res){
                    return $this->json('获取成功',Error::$SUCCESS,$res);
                }
                else return $this->json('没有获取到数据',Error::$OBTAIN_ERROR);

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }

        }

    }

    /**
     * 获取服务详情
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */

    public function detailsServe()

    {
        if($this->request->isPost()){

            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $p_id = $this->request->post('serve_id')?$this->request->post('serve_id'):false; # 活动唯一id

            #$type_id = $this->request->post('s_type_id')?$this->request->post('s_type_id'):false; # 第三级分类id

            if((int)$uid && (int)$p_id){
                $sid = $this->schoolLimit($uid);

                $data = Db::table('s_serve')->alias('ss')
                    ->field("ss.event_title,ss.p_start,ss.voice_time,ss.images,ss.ask,ss.end_times p_end ,ss.charging,ss.funds,ss.voice,ss.description k_description,u.user_id,u.nickname,ss.plays_id serve_id,u.u_vip,u.sex,st.id p_id,sst.id stid,ss.images,u.head_image")
                    ->join('s_sociality_type sst','sst.id=ss.about_type_id','left')
                    ->join('sociality_type st','st.id=sst.type_id','left')
                    ->join('users u','u.user_id=ss.user_id')
                    ->where('u.school',$sid)
                    ->where('ss.status',1)
                    ->find($p_id);

                if($data){
                    # 获取销售次数
                    $sales = Db::table('s_order')->field('sum(second) second')
                        ->where('serve_id',$p_id)
                        ->find();
                    $activity_fabulous = Db::table('activity_fabulous')->field('status')
                        ->where('type_name', '服务')->where('type_id', $p_id)
                        ->where('user_id',$uid)
                        ->find();
                    $data['is_fabulous'] = $activity_fabulous['status'] ? $activity_fabulous['status'] : 0;
                    $this->statistics($uid,$data['stid'],'s_serve');  # 记录用户点击的二级分类次数
                    $data['sales'] = !empty($sales['second'])?$sales['second']:0;
                    return $this->json('获取成功',Error::$SUCCESS,$data);
                }else return $this->json('未获取到数据',Error::$OBTAIN_ERROR);

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }

        }

    }


    /**
     * 记录用户查看详情的分类次数
     *
     * @param $uid
     * @param $pid
     */
    private function statistics($uid, $pid, $plays)
    {
        $i = 1;
        $sales = Cache::get('st'.$uid);
        $key = $plays.'w'.$pid;
        $data = [];
        if($sales){
            $arr = json_decode($sales,true);
            if(isset($arr[$key])){
                $data[$key] =  $arr[$key]+$i;
            }else{
                $data[$key] =  $i;
            }
            $data = array_merge($arr,$data);

        }else{
            $data[$key] = $i;
        }
        $json = json_encode($data);
        Cache::set('st'.$uid,$json);

    }

    /**
     * 暂时没有使用
     *
     * @param $uid
     * @param $p_id
     * @param $table
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */



    private function commonAb($uid, $p_id,$table)
    {
        $sid = $this->schoolLimit($uid);

        $filed = $alias = '';
        switch($table){
            case 'plays':
                $filed = "p.event_title,p.p_start,p.ask,p.launch,p.p_address,p.description k_description,p.number,u.u_vip,u.user_id,u.nickname,p.plays_id,p.funds,p.p_end";
                $alias = 'p';
                break;
            case 's_serve':
                $filed = "ss.event_title,ss.p_start,ss.ask,ss.p_end,ss.charging,ss.funds,ss.voice,ss.description k_description,u.user_id,u.nickname,ss.plays_id";
                $alias = 'ss';
                break;
        }

        $res = Db::table($table)->alias($alias)
            ->field($filed)
            ->join('s_sociality_type sst','sst.id=p.about_type_id','left')
            ->join('users u','u.user_id=p.user_id')
            ->where('u.school',$sid)
            ->where('p.status',1)
            ->find($p_id);

        if($res){
            /*$attr = Db::table('plays_attach')->where('plays_id',$res['plays_id'])
                ->count();
            $res['attach'] = $attr;*/

            return $res;

        }else{
            return false;
        }

    }


    /**
     * 获取服务板块数据接口  以顶级分类取10条数据
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function serveObtain()
    {
        if($this->request->isPost()){

            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $tid = $this->request->post('type_id')?$this->request->post('type_id'):1; # 类型id 顶级类型 1 or 2
            $pages = $this->request->post('pages')?$this->request->post('pages'):1;
            if((int)$uid && (int)$pages && (int)$tid){
                $tid =1 ; $pages = 1;
                $rows = $this->obtain($uid,$tid,$pages);
                if(!$rows)return $this->json('未获取到数据',Error::$OBTAIN_ERROR);
                #$total = ceil($rows['count'] / $this->num);
                return $this->json('获取成功',Error::$SUCCESS,$rows['data']);
            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }

        }
    }

    /**
     * 获取社交活动接口 以第二级分类获取社交活动数据
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function playsObtain()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $tid = $this->request->post('type_id')?$this->request->post('type_id'):1; # 类型id 二级分类
        if($uid && $tid){
            $sid = $this->schoolLimit($uid);
            $tid = 1;
            $data = [];
            foreach($this->type($uid, $tid) as $key=>$item){
                if($item){
                    $where = ' p.about_type_id in ( '.$item['sid'].' )';
                    if($sid) $where .= " and u.school={$sid}";
                    $rows = Db::table('plays')->alias('p')
                        ->field('p.plays_id,p.event_title,sst.type_name,u.head_image,p.user_id,u.nickname,us.grade,p.launch,p.funds,p.images,us.grade,p.p_start,p.end_times,u.sex,p.p_address,p.number,p.ask')
                        ->join('s_sociality_type sst','p.about_type_id=sst.id','left')
                        ->join('sociality_type st','st.id=sst.type_id','left')
                        ->join('users u','p.user_id=u.user_id','left')
                        ->join('users_secretary us','u.user_id=us.user_id','left')
                        ->where($where)
                        ->where('p_start','>',time())
                        ->where('p.status',1)
                        ->order('p.launch','desc')
                        ->limit(0,3)
                        ->select();

                    if($rows){

                        foreach($rows as $k=>$v){
                            $attr = Db::table('plays_attach')->where('plays_id',$v['plays_id'])
                                ->count();
                            $game_name = '';
                            if(stripos($item['type_name'],'游戏')) $game_name = $v['type_name'];
                            $rows[$k]['attach'] = $attr;
                            $rows[$k]['game_name'] = $game_name;
                            $rows[$k]['badge'] = '社交';

                        }
                        $data[] = [
                            'type_id'   => $item['id'],
                            'type_name' => $item['type_name'],
                            'data' => $rows?$rows:[],
                        ];

                    }


                }
            }

            if($data) return $this->json('获取成功',Error::$SUCCESS,$data);
            else return $this->json('未获取到数据',Error::$OBTAIN_ERROR);

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

    /**
     * 获取顶级模块下的所有下级分类
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function obtainType()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $tid = $this->request->post('type_id')?$this->request->post('type_id'):false; # 类型id 顶级
            if($uid && $tid){
                $data = [];
                foreach($this->topLevel($uid, $tid) as $item){
                    $res = Db::table('s_sociality_type')->field('type_name,id s_type_id,icon')
                        ->where('type_id',$item['id'])
                        ->select();
                    $data[] = [
                        'type_id'       => $item['id'],
                        'p_type_name'   => $item['type_name'],
                        'p_icon'        => $item['icon'],
                        'son'           => $res?$res:[]
                    ];
                }
                if($data) return $this->json('获取成功',Error::$SUCCESS,$data);
                else return $this->json('没有获取到数据',Error::$OBTAIN_ERROR);
            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }
        }
    }

    /**
     * 返回上级分类的id等信息，方便重组数据格式
     *
     * @param $uid
     * @param $tid
     * @return \Generator
     */
    private function topLevel($uid, $tid)
    {
        $res = Db::table('sociality_type')->alias('st')
            ->field('st.id,st.type_name,icon')
            ->where('st.pid',$tid)
            ->where('st.status',1)
            ->select();

        if($res){
            foreach($res as $item){
                yield $item;
            }
        }

    }



    /**
     * 社交 -- 查看更多 以二级分类获取 筛选条件
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function allPlaysAb()
    {

        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $tid = $this->request->post('type_id')?$this->request->post('type_id'):false; # 类型id sociality_type
            $mark = $this->request->post('marks')?$this->request->post('marks'):2; # 类型标识 2 社交
            $pages = $this->request->post('pages')?$this->request->post('pages'):1; #

            # 筛选条件
            $ask = $this->request->post('ask')?$this->request->post('ask'):false; # 1 or 2
            $stid = $this->request->post('s_type_id')?$this->request->post('s_type_id'):false;
            $arr = [
                'ask'       => $ask,
                's_type_id' => $stid
            ];
            $arr = array_filter($arr);

            if($uid && $tid && $pages && $mark){

                $sid = $this->schoolLimit($uid);
                $where = '';
                if($sid) $where .= ' school='.$sid;
                if($ask || $stid){
                    if($ask){
                        $where .= " and p.ask={$ask}";
                    }
                    if($stid){
                        $where .= " and sst.id={$stid}";
                    }
                }


                $start = ($pages - 1) * $this->num;
                $res = Db::table('plays')->alias('p')
                    ->field("p.event_title,p.plays_id,p.ask,p.funds,p.p_start,p.launch,sst.id s_type_id,sst.type_name,u.head_image,us.grade,u.sex,p.number,u.u_vip")
                    ->join('s_sociality_type sst','sst.id=p.about_type_id','left')
                    ->join('sociality_type st','sst.type_id=st.id','left')
                    ->join('users u','p.user_id=u.user_id','left')
                    ->join('users_secretary us','u.user_id=us.user_id','left')
                    ->where('sst.type_id',$tid)
                    ->where($where)
                    ->where('p_start','>',time())
                    ->where('p.status',1)
                    #->order('p.launch','desc')
                    ->order('p.plays_id','desc')
                    ->limit($start,$this->num)
                    ->select();

                $total = '';
                if($res){
                    $count = Db::table('plays')->alias('p')
                        ->field("p.plays_id")
                        ->join('s_sociality_type sst','sst.id=p.about_type_id','left')
                        ->join('sociality_type st','sst.type_id=st.id','left')
                        ->join('users u','p.user_id=u.user_id','left')
                        ->join('users_secretary us','u.user_id=us.user_id','left')
                        ->where('sst.type_id',$tid)
                        ->where($where)
                        ->where('p_start','>',time())
                        ->where('p.status',1)
                        ->count();
                    $total = ceil($count / $this->num);
                }

                foreach($res as $k=>$v){
                    $attr = Db::table('plays_attach')->where('plays_id',$v['plays_id'])
                        ->count();
                    $res[$k]['attach'] = $attr;
                    $res[$k]['badge'] = '社交';

                }

                if($res) return $this->json('获取成功',Error::$SUCCESS,$res,$total);
                else return $this->json('没有获取到数据',Error::$OBTAIN_ERROR);

            }else{
                return $this->json('未获取参数',Error::$PARAMETER_ERROR);
            }

        }

    }

    /**
     * 服务 二级分类  查看更多
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function moreServe()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $tid = $this->request->post('type_id')?$this->request->post('type_id'):false;
            $page = $this->request->post('pages')?$this->request->post('pages'):1;

            # 筛选条件
            $ask = $this->request->post('ask')?$this->request->post('ask'):false; # 1 or 2
            $stid = $this->request->post('s_type_id')?$this->request->post('s_type_id'):false;
            $arr = [
                'ask'       => $ask,
                's_type_id' => $stid
            ];
            $arr = array_filter($arr);
            $recommend = '';
            if(!$tid && !$stid) $recommend = 1;

            if($uid && ( $tid || $recommend || $stid) && $page){


                $sid = $this->schoolLimit($uid);
                $where = '';
                if($sid) $where = ' u.school='.$sid; # 获取当前用户下的学校内数据
                $start = $this->num * ($page-1);

                if($ask || $stid || $recommend || $tid){

                    if($ask){
                        $where .=" and ask={$ask}";
                    }
                    if($stid){
                        $where .=" and ss.about_type_id={$stid}";
                    }
                    if($recommend) $where .= ' and recommend='.$recommend;

                    if($tid) $where .= ' and sst.type_id='.$tid;
                }
                $rows = Db::table('s_serve')->alias('ss')
                    ->field("ss.event_title,u.nickname,ss.plays_id serve_id,ss.event_title,st.id type_id,st.type_name,u.head_image,us.grade,ss.launch,ss.charging,ss.funds,ss.images,sst.id s_type_id,sst.type_name s_type_name")
                    ->join('users u','ss.user_id=u.user_id','left')
                    ->join('users_secretary us','u.user_id=us.user_id','left')
                    ->join('s_sociality_type sst','sst.id=ss.about_type_id','left')
                    ->join('sociality_type st','sst.type_id=st.id','left')
                    #->where('sst.type_id',$tid)
                    ->where($where)
                    ->where('ss.end_times','>',time())
                    ->where('ss.status',1)
                    #->order('ss.launch','desc')
                    ->order('ss.plays_id','desc')
                    ->limit($start,$this->num)
                    ->select();


                if($rows){
                    $count = Db::table('s_serve')->alias('ss')
                        ->field('ss.plays_id')
                        ->join('users u','ss.user_id=u.user_id','left')
                        ->join('users_secretary us','u.user_id=us.user_id','left')
                        ->join('s_sociality_type sst','sst.id=ss.about_type_id','left')
                        ->join('sociality_type st','sst.type_id=st.id','left')
                        #->where('sst.type_id',$tid)
                        ->where($where)
                        ->where('end_times','>',time())
                        ->where('ss.status',1)
                        ->count();
                    $total = ceil($count / $this->num);

                    $s_type_name = "";
                    foreach($rows as $key=>$item){
                        $rows[$key]['badge'] = '服务';
                        $s_type_name = $item['s_type_name'];
                        if(!$stid){
                            $s_type_name = $item['type_name'];
                        }
                        unset($rows[$key]['s_type_name']);
                    }
                    if(!$stid && !$tid){
                        $s_type_name ='服务';
                    }
                    $res = [
                        's_type_name' => $s_type_name,
                        'data' => $rows
                    ];
                    $data = [
                        'status' => Error::$SUCCESS,
                        'msg'    => '获取成功',
                        'data'   => $res,
                        'total'  => $total,
                        'scree'  => $arr
                    ];
                    return $this->json($data);
                }else{
                    $data = [
                        'status' => Error::$OBTAIN_ERROR,
                        'msg'    => '没有获取到数据',
                        'scree'  => $arr
                    ];
                    return $this->json($data);
                }

            }else{
                return $this->json('未获取参数',Error::$PARAMETER_ERROR);
            }
        }

    }



    /**
     * 单独获取父级id信息
     *
     * @param $uid
     * @param $tid
     * @return \Generator
     */
    private function type($uid, $tid, $pid = 2)
    {

        if((int)$uid && (int)$tid){

            $rows = Db::table('sociality_type')->alias('st')
                ->field('st.id,st.type_name')
                ->where('pid',$pid)
                ->select();
            if($rows){
                $did = [];
                foreach($rows as $item){
                    $res = [];
                    $row = Db::table('s_sociality_type')->field('id')->where('type_id',$item['id'])
                        ->select();
                    if($row){
                        foreach($row as $i){
                            $res[] = $i['id'];
                        }
                    }
                    $did[$item['id']]['id'] = $item['id'];
                    $did[$item['id']]['type_name'] = $item['type_name'];
                    $did[$item['id']]['sid'] = join(',',$res)?join(',',$res):'';
                }
                foreach($did as $item){
                    yield $item;
                }
            }
        }

    }



    /**
     * 获取数据
     * @param $uid
     * @param $tid
     * @param $pages
     * @return array
     * @throws \think\Exception
     */

    private function obtain($uid,$tid,$pages=1)
    {
        $sid = $this->schoolLimit($uid);
        $where = '';
        if($sid) $where = ' u.school='.$sid; # 获取当前用户下的学校内数据
        $start = $this->num * ($pages-1);

        $field = "sst.id type_id,st.type_name p_name,ss.plays_id serve_id,event_title,sst.type_name,u.head_image,u.nickname,us.grade,ss.user_id,ss.launch,ss.charging,ss.funds,ss.images";

        $table = 's_serve';

        $rows = Db::table($table)->alias('ss')
            ->field($field)
            ->join('s_sociality_type sst','ss.about_type_id=sst.id','left')
            ->join('sociality_type st','st.id=sst.type_id','left')
            ->join('users u','ss.user_id=u.user_id','left')
            ->join('users_secretary us','u.user_id=us.user_id','left')
            # ->where('ss.user_id',$uid)
            ->where('st.pid',$tid)
            ->where($where)
            ->where('end_times','>',time())
            ->where('ss.status',1)
            ->order('ss.launch','desc')
            ->limit($start,$this->num)
            ->select();
        if(!$rows) return [];
        /*$count = Db::table('s_serve')->alias('ss')
            ->field('ss.plays_id')
            ->join('s_sociality_type sst','ss.about_type_id=sst.id','left')
            ->join('sociality_type st','st.id=sst.type_id','left')
            ->join('users u','ss.user_id=u.user_id','left')
            ->join('users_secretary us','u.user_id=us.user_id','left')
            # ->where('ss.user_id',$uid)
            ->where('st.pid',$tid)
            ->where($where)
            ->where('ss.status',1)
            ->count();
        $data = ['data'=>$rows,'count'=>$count];
        if($rows && $count) return $data;
        else return [];*/
        foreach($rows as $key=>$item){
            $rows[$key]['badge'] = '服务';
        }
        return ['data'=>$rows];

    }

    /**
     * 服务
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function obtainSerType()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $tid = $this->request->post('type_id')?$this->request->post('type_id'):1;
            $stid = $this->request->post('s_type_id')?$this->request->post('s_type_id'):false;
            if($uid && $tid){
                $where = " pid={$tid}";
                $table = "sociality_type";
                if($stid){
                    $where = " type_id={$stid}";
                    $table = "s_sociality_type";
                }
                $res = Db::table($table)->field('id type_id,type_name')->where($where)->select();
                if($res){
                    if(!$stid){
                        $data = [['type_id'=>0,'type_name'=>'推荐']];
                        $res = array_merge($data,$res);
                    }
                    return $this->json('获取成功',Error::$SUCCESS,$res);
                }else{
                    return $this->json('没有获取到数据',Error::$OBTAIN_ERROR);
                }
            }


        }

    }


    /**
     * 活动的学校限制，，默认只能查看到自己学校发布的活动
     *
     * @param $uid
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     */
    private function schoolLimit($uid)
    {
        $sid = '';
        switch($this->_school){
            case 1:
                $sid = Db::table('users')->field('school')->find($uid);
                if($sid){
                    $sid = $sid['school'];
                }
                break;
            case 2:
                break;
            case 3:
                break;
            default:
                $sid = Db::table('users')->field('school')->find($uid);
                if($sid){
                    $sid = $sid['school'];
                }
                break;
        }
        return $sid;
    }

}
