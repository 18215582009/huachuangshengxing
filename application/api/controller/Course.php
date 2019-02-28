<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 17:52
 */

namespace app\api\controller;


use app\common\lib\Fsock;
use think\Db;
use think\Request;
use app\common\error\Error;

class Course extends Base
{

    protected $jurisdiction = [];                    # 不需要token 验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    private $week = 604800;
    private $tables ;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->tables = new \app\common\model\Course();
    }

    /**
     * 获取指定日期课程
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function showCourse()
    {

        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
         $curTime = $this->request->post('times')?$this->request->post('times'):strtotime(date('Y-m-d'));
        if($uid && (int)$curTime){
            if(!$this->is_timestamp($curTime)) return $this->json('不是正确的时间戳',105);
            $cur = Db::table('users')->field('cur_week')->find($uid); # 获取到用户的当前周数
            # $startTime = strtotime(date('Y-m-d',$curTime))-86400;
            $startTime = strtotime(date("Y-m-d",$curTime)." 0:0:1");
            $endTime = strtotime(date("Y-m-d",$curTime)." 23:59:59");

            $rows = Db::table('cour_timer')->alias('cte')
                ->field('cte.up_time,cte.end_time,c.course_name,c.teacher,ct.place,ct.hour_cour,ct.odd_double,cte.id ctime_id,c.id course_id,ct.id cour_id')
                ->join('course_times ct','ct.id=cte.cou_id','left')
                ->join('course c','c.id=ct.course_id','left')
                ->where('cte.up_time','>',$startTime)
                ->where('cte.up_time','<',$endTime)
                ->where('c.user_id',$uid)
                ->where('cte.week',$cur['cur_week'])
                ->where('c.status',1)
                ->where('ct.status',1)
                ->where('cte.status',1)
                ->order('up_time','asc')
                ->select();
            if(!empty($rows)) {
                return $this->json('获取成功', Error::$SUCCESS, $rows);
            }else{
                return $this->json('没有获取到课程数据',Error::$OBTAIN_ERROR);
            }

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }

    /**
     * 获取单个课程详情
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function showOne()
    {
        $courId = $this->request->post('cour_id')?$this->request->post('cour_id'):false;  # 课时id
        $courseId = $this->request->post('course_id')?$this->request->post('course_id'):false;  # 课程id
        $ctimeId = $this->request->post('ctime_id')?$this->request->post('ctime_id'):false;  # 课时时间管理id
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        if($courseId && $courId && $ctimeId && $uid){
            $row = Db::table('course_times')->alias('ct')
                ->field('ct.id cour_id,c.id course_id,cte.id ctime_id,c.course_name,c.teacher,ct.place,ct.hour_cour,ct.cycle,cte.up_time,ct.odd_double,cte.end_time,u.max_week')
                ->join('course c','ct.course_id=c.id','left')
                ->join('users u','u.user_id=c.user_id','left')
                ->join('cour_timer cte','cte.cou_id=ct.id','left')
                ->where('c.user_id',$uid)
                ->where('cte.id',$ctimeId)
                ->where('c.status',1)
                ->where('ct.status',1)
                ->where('cte.status',1)
                ->find($courId);
            if(!empty($row)){
                return $this->json('获取成功',Error::$SUCCESS, $row);
            }else{
                return $this->json('没有获取到数据',Error::$INSERT_ERROR);
            }

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }


    }

    /**
     * 获取当前日期的初始日  星期一的时间戳
     * @return int
     */
    private function weekStart()
    {
        $sdefaultDate = date("Y-m-d");
        $first=1;
        $w=date('w',strtotime($sdefaultDate));
        $weekStart = date('Y-m-d',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days')).' 00:00:00';
        return strtotime($weekStart);
    }

    /**
     * 存储当前 第几周/是否提醒  每次获取会自动判断是否已经到下一周
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function cur()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $cur = $this->request->post('cur_week')?$this->request->post('cur_week'):false;
        $max = $this->request->post('max_week')?$this->request->post('max_week'):false;
        $remind = $this->request->post('remind')?$this->request->post('remind'):false;
        if($uid && ($cur || $remind || $max)){
            $data = [
                'cur_week'  => $cur,
                'remind'    => $remind,
                'max_week'  => $max,
                'utime'     => time(),
                'cur_time'  => $this->weekStart()
            ];
            $data = array_filter($data);
            if($data){
                $bool = Db::table('users')->where('user_id',$uid)->update($data);
                if($bool){
                    return $this->json('添加成功',Error::$SUCCESS);
                }else{
                    return $this->json('添加失败',Error::$UPDATE_ERROR);
                }
            }
        }else{
            if($uid){

                $cur = Db::table('users')->field('user_id,cur_week,remind,max_week,cur_time')->find($uid);
                $time = $this->weekStart() - $cur['cur_time'];


                if($time >= 0){
                    $cur_week = $cur['cur_week']+ceil($time/$this->week);

                    $cur_week = $cur_week==0?1:$cur_week;

                    if($cur_week > $cur['max_week']) $cur_week = $max;

                    $boo = Db::table('users')->where('user_id',$cur['user_id'])
                        ->update(['cur_week'=>$cur_week,'utime'=>time(),'cur_time'=>$this->weekStart()]);
                    if($boo){
                        $cur['cur_week'] = $cur_week;
                    }
                }

                if(!empty($cur)){
                    return $this->json('获取成功',Error::$SUCCESS,$cur);
                }else{
                    return $this->json('获取失败',Error::$OBTAIN_ERROR);
                }
            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }
        }
    }

    /**
     * 创建课程
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addCourse()
    {


        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;

        $course_name = $this->request->post('course_name')?$this->request->post('course_name'):false;

        $teacher = $this->request->post('teacher')?$this->request->post('teacher'):false;

        $course_json = $this->request->post('course_json')?$this->request->post('course_json'):false;  # 接收一个json对象 格式如下
        /**
         *
         * $course_json = {
         *   {
         *      'cycle' => 周数 1,3,5,7,9...,
         *      'hour_cour' => 课节时间 第几节课到第几节课 1-1-2
         *      'place' => 地点,
         *   }
         * ...
         * }
         */
        if($uid && $course_name && $course_json && $teacher){

            /**
             * 提前处理课时节数是否冲突
             */
            $course = json_decode($course_json,true);
            $conflict = [];

            foreach ($course as $k=>$item){
                $item['cycle'] = trim($item['cycle'],',');
                $cs = $this->checkCourse($item['cycle'],$item['hour_cour'],$uid);
                if(!empty($cs)){
                    $cs[0]['id'] = $k;
                    array_unshift($conflict,$cs[0]);
                }
            }
            if(!empty($conflict)) return $this->json('课程时间冲突',Error::$COURSE_TIME_CONFLICT,$conflict);
            $bool = $this->adds($uid,$course_name,$teacher,$course_json);
            if($bool){
            #    return ds('创建课程成功',Error::$SUCCESS);
                return $this->json('创建课程成功',Error::$SUCCESS);
            }else{
                #return ds('创建课程失败',Error::$INSERT_ERROR);
                return $this->json('创建课程失败',Error::$INSERT_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }

    private function yie($uid)
    {
        $res = Db::table('course')->field('id')->where('user_id',$uid)->where('status',1)->select();
        if($res){
            foreach($res as $item)
            {
                yield $item;
            }
        }else{
            yield [];
        }


    }

    /**
     *
     * 一键清除课程
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function clearCour()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;

        if($uid){
            $data = ['status'=>0,'utime'=>time()]; # 软删

            foreach($this->yie($uid) as $item){

                if(!$item) return $this->json('没有课程数据可删',Error::$OBTAIN_ERROR);

                $courId = Db::table('course_times')->alias('ct')->field('ct.id')
                    ->join('course c','c.id=ct.course_id','left')
                    ->where('c.user_id',$uid)

                    ->where('c.status',1)
                    ->where('ct.status',1)
                    ->select();
                $i = false;
                if(!empty($courId)){
                    foreach ($courId as $v){
                        # 删除所有课程课时节数
                        if((int)$v){
                            $bool = Db::table('cour_timer')
                                ->where('cou_id',$v['id'])
                                ->update($data);
                            if($bool){
                                $i = true;
                            }
                        }
                    }
                    if($i){
                        $bool = Db::table('course_times')
                            ->where('course_id',$item['id'])
                            ->update($data);
                        if($bool){
                            $bool = Db::table('course')
                                ->where('user_id',$uid)
                                ->where('id',$item['id'])
                                ->update($data);
                            if($bool){
                                return $this->json('删除课程成功',Error::$SUCCESS);
                            }else{
                                return $this->json('删除课程失败',Error::$DELETE_COURSE_ERROR);
                            }
                        }else{
                            return $this->json('删除课时失败',Error::$DELETE_COURSE_ERROR);
                        }
                    }else{
                        return $this->json('删除节数失败',Error::$DELETE_COURSE_ERROR);
                    }
                }else{
                    return $this->json('没有数据可删',Error::$NOT_FOUND_DEL);
                }
            }

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }
    /**
     * 写入数据库
     * @param $uid
     * @param $course_name
     * @param $teacher
     * @param $course_json
     * @return bool|false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function adds($uid,$course_name,$teacher,$course_json){
        $data = [
            'course_name' => $course_name,
            'user_id' => $uid,
            'teacher' => $teacher,
            'ctime'       => time()
        ];
        $cur = Db::table('users')->field('cur_week')->find($uid); # 获取到用户的当前周数
        $course_id = $this->tables->insertGetId($data); # 获取自增课程id
        if(!$course_id) return $this->json('创建课程失败',Error::$INSERT_ERROR);
        $jsonArr = json_decode($course_json,true);
        $i = false;
        foreach ($jsonArr as $k => $iem) {
            $iem['cycle'] = trim($iem['cycle'],',');
            $iem['course_id'] = $course_id;  # 课程自增 id
            $iem['odd_double'] = $this->oddDob($iem['cycle']);
            $resId = Db::table('course_times')->insertGetId($iem);
            if(!$resId) return $this->json('创建课程失败',Error::$INSERT_ERROR);
            $res = $this->handle($iem['cycle'], $cur['cur_week'], $iem['hour_cour'], $resId);
            if($res) {
                $bool = Db::table('cour_timer')->insertAll($res);
                if ($bool) {
                    $i = true;
                }
            }
        }
        return $i;
    }

    /**
     * 处理课时 的开始时间结束时间  待优化
     * @param $cycle
     * @param $cur
     * @param $hour
     * @param $odd  课时唯一id
     * @return array
     */
    private function handle($cycle ,$cur ,$hour ,$odd)
    {
        $i = 60*60*24*7; # 一周的时间戳
        $arr = explode('-',$hour);
        $num = $arr[1];
        # 需要上课的课长时间
        $leng = ($arr[2]-$arr[1]?($arr[2]-$arr[1])+1:1)*45*60+(($arr[2]-$arr[1])*10*60);

        $upTime = $this->timer($num); # 记录课时开始时间

        #$start = date("Y-m-d H:i:s",mktime(0, 0 ,0,date("m"),date("d")-date("w")+1,date("Y")));       # 每周开始时间
        $start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+$arr[0],date("Y")));

        #$time = strtotime(date('Y-m-d',strtotime($start)).' '.$upTime)+$leng;
        #echo date('Y-m-d',strtotim
        #e($start)).' '.$upTime,'<br>'; # 课时开始时间
        #echo date('Y-m-d H:i:s',$time),'<br>';                   # 课时结束时间

        $data = [];
        foreach (explode(',',$cycle) as $k=>$v){
            if($v > $cur){
                $time = date('Y-m-d',strtotime($start)+($i*($v - $cur))).' '.$upTime;
                $data[$k] = [
                    'cou_id'       => $odd,
                    'week'      => $v,
                    'up_time'   => strtotime($time),
                    'end_time'  => strtotime($time)+$leng
                ];
            }
            if($v < $cur){
                $time = date('Y-m-d',strtotime($start)-($i*($cur - $v))).' '.$upTime;
                $data[$k] = [
                    'cou_id'       => $odd,
                    'week'      => $v,
                    'up_time'   => strtotime($time),
                    'end_time'  => strtotime($time)+$leng
                ];
            }
            if($v == $cur){
                $time = date('Y-m-d',strtotime($start)).' '.$upTime;
                $data[$k] = [
                    'cou_id'       => $odd,
                    'week'      => $v,
                    'up_time'   => strtotime($time),
                    'end_time'  => strtotime($time)+$leng
                ];
            }
        }
        //$res = Db::table('cour_timer')->insertAll($data);
        return $data;
    }

    /**
     * 转换每节课的开始上课时间
     * @param $num
     * @return string
     */
    private function timer($num)
    {
        if(stripos('-',$num)){
            $a = explode('-',$num);
            $num = $a[1];
        }
        $upTime = '';
        switch ($num){
            case 1:
                $upTime = '8:00';
                break;
            case 2:
                $upTime = '8:55';
                break;
            case 3:
                $upTime = '9:50';
                break;
            case 4:
                $upTime = '10:45';
                break;
            case 5:
                $upTime = '14:15';
                break;
            case 6:
                $upTime = '15:10';
                break;
            case 7:
                $upTime = '16:05';
                break;
            case 8:
                $upTime = '16:55';
                break;
            case 9:
                $upTime = '18:10';
                break;
            case 10:
                $upTime = '19:05';
                break;
            case 11:
                $upTime = '19:55';
                break;
            case 12:
                $upTime = '20:50';
                break;
        }
        return $upTime;
    }
    /**
     * 判断是单周还是双周 还是全/混合
     * @param $cycle
     * @return int
     */
    private function oddDob($cycle)
    {
        $odd = $double = [];
        foreach (explode(',',$cycle) as $v){
            if((int)$v&1){
                $odd[] = $v;
            }else{
                $double[] = $v;
            }
        }
        if(!empty($odd) && empty($double)) return 1;   # 单
        if(empty($odd) && !empty($double)) return 0;   # 双
        if(!empty($odd) && !empty($double)) return 2;  # 全周
    }


    /**
     * 修改课程   未完 未做课程冲突判断
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editCour()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;  # 用户id
        $cour_id = $this->request->post('cour_id')?$this->request->post('cour_id'):false; # 课时id
        $course_id = $this->request->post('course_id')?$this->request->post('course_id'):false; # 课程id
        $course_name = $this->request->post('course_name')?$this->request->post('course_name'):false;
        $teacher = $this->request->post('teacher')?$this->request->post('teacher'):false;
        $course_json = $this->request->post('course_json')?$this->request->post('course_json'):false;  # 接收一个json对象 格式如下
        /**
         * $course_json = {
         *      'cycle' => 周数 1,2,3,
         *      'hour_cour' => 课节时间 第几节课到第几节课 1-1-2
         *      'place' => 地点,
         * }
         */
        if($uid && $course_name && $course_json && $teacher && $cour_id && $course_id){
            $item = json_decode($course_json,true);
            $cs = $this->checkCourse($item['cycle'],$item['hour_cour'],$uid,$cour_id);
            if($cs) return $this->json('课程时间冲突',Error::$COURSE_TIME_CONFLICT,$cs);
            $i = true;
            if($course_id && ($course_name||$teacher)){
                $arr = [
                    'course_name' => $course_name,
                    'teacher'     => $teacher,
                    'utime'       => time()
                ];
                $arr = array_filter($arr);
                $bool = Db::table('course')->where('id',$course_id)
                    ->update($arr);
                if(!$bool){
                    return $this->json('修改失败',Error::$UPDATE_ERROR);
                }
            }
            if($cour_id && $course_json) {

                $data = json_decode($course_json,true);

                if (!empty($data)) {
                    $data['utime'] = time();
                    $data['cycle'] = trim($data['cycle'],',');
                    $data['odd_double'] = $this->oddDob($data['cycle']);
                    $bool = Db::table('course_times')->where('id', $cour_id)
                        ->update($data);
                    if ($bool) {
                        $bool = Db::table('cour_timer')->where('cou_id',$cour_id)->delete();
                        #if($bool){
                            $cur = Db::table('users')->field('cur_week')->find($uid);
                            $res = $this->handle($data['cycle'], $cur['cur_week'], $data['hour_cour'], $cour_id);
                            $bool = Db::table('cour_timer')->insertAll($res);
                            if($bool) return $this->json('修改成功', Error::$SUCCESS);
                            else $this->json('修改失败', Error::$UPDATE_ERROR);
                        #}
                    } else {
                        return $this->json('修改失败', Error::$UPDATE_ERROR);
                    }
                } else {
                    return $this->json('修改失败', Error::$UPDATE_ERROR);
                }
            }

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }

    /**
     * 删除课程-课时-节数
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delCour()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        # 课时id $cour_id
        $cour_id = $this->request->post('cour_id')?$this->request->post('cour_id'):false;
        # 课程id  $cour_id
        $course_id = $this->request->post('course_id')?$this->request->post('course_id'):false;
        # 课时 时间 id $ctime_id
        $ctime_id = $this->request->post('ctime_id')?$this->request->post('ctime_id'):false;
        # 周  存在则删除这一周 $week
        $week = $this->request->post('week')?$this->request->post('week'):false;
        if($uid && ($ctime_id || $week || $cour_id || $course_id)){
            $data = ['status'=>0]; # 软删
            if($course_id){ # 删除整个课程包括课程下的所有课时
                $courId = Db::table('course_times')->alias('ct')->field('ct.id')
                    ->join('course c','c.id=ct.course_id','left')
                    ->where('c.user_id',$uid)
                    ->where('ct.course_id',$course_id)
                    ->where('c.status',1)
                    ->where('ct.status',1)
                    ->select();
                $i = false;
                if(!empty($courId)){
                    foreach ($courId as $v){
                        # 删除所有课程课时节数
                        if((int)$v){
                            $bool = Db::table('cour_timer')
                                ->where('cou_id',$v['id'])
                                ->update($data);
                            if($bool){
                                $i = true;
                            }
                        }
                    }
                    if($i){
                        $bool = Db::table('course_times')
                            ->where('course_id',$course_id)
                            ->update($data);
                        if($bool){
                            $bool = Db::table('course')
                                ->where('user_id',$uid)
                                ->where('id',$course_id)
                                ->update($data);
                            if($bool){
                                return $this->json('删除课程成功',Error::$SUCCESS);
                            }else{
                                return $this->json('删除课程失败',Error::$DELETE_COURSE_ERROR);
                            }
                        }else{
                            return $this->json('删除课时失败',Error::$DELETE_COURSE_ERROR);
                        }
                    }else{
                        return $this->json('删除节数失败',Error::$DELETE_COURSE_ERROR);
                    }
                }else{
                    return $this->json('没有数据可删',Error::$NOT_FOUND_DEL);
                }
            }
            if($cour_id){ # 删除课时节数 有week 则删除 week这周的，否则，删除所有周这个课时节数
                $where = '';
                if($week){
                    $where = 'week='.$week;
                }
                $row = Db::table('course_times')->field('id,cycle')
                    ->where('status',1)
                    ->find($cour_id);
                if(empty($row)) return $this->json('没有课时可删',Error::$NOT_FOUND_DEL);
                $res = Db::table('cour_timer')->where('cou_id',$cour_id)
                    ->where($where)
                    ->where('status',1)
                    ->select();
                if(empty($res)) return $this->json('没有课时节数可删',Error::$NOT_FOUND_DEL);
                $bool = Db::table('cour_timer')->where('cou_id',$cour_id)
                    ->where($where)
                    ->update($data);
                if($bool){
                    if(!empty($where)){  # 如果week 存在则需要将其从课时中去掉
                        $cycle = explode(',',$row['cycle']);
                        foreach ( $cycle as $k=>$v){
                            if($v == $week){
                                unset($cycle[$k]);
                            }
                        }
                        $data = [
                            'cycle' => join(',',$cycle)
                        ];
                    }
                    $bool = Db::table('course_times')->where('id',$cour_id)
                        ->update($data);
                    if($bool){
                        return $this->json('删除课时成功',Error::$SUCCESS);
                    }else{
                        return $this->json('删除课时失败',Error::$DELETE_COURSE_ERROR);
                    }
                }else{
                    return $this->json('删除节数失败',Error::$DELETE_COURSE_ERROR);
                }

            }
            if($ctime_id){  # 只删除这一节课时
                $row = Db::table('cour_timer')->field('id')
                    ->where('status',1)
                    ->find($ctime_id);
                if(empty($row)) return $this->json('没有数据可删',Error::$NOT_FOUND_DEL);
                $bool = Db::table('cour_timer')->where('id',$ctime_id)->update($data);
                if($bool){
                    return $this->json('删除节数成功',Error::$SUCCESS);
                }else{
                    return $this->json('删除节数失败',Error::$DELETE_COURSE_ERROR);
                }
            }

        }

    }

    /**
     * 判断有没有冲突的周，有的话就去进行 课节数冲突 判断  没有冲突返回0
     * @param string $cycle
     * @param int $uid
     * @param string $hour_cour
     * @return array|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkCourse($cycle ,$hour_cour  ,$uid,$cour_id=null)
    {
        $where = '';
        if($cour_id){
            $where = 'ct.id != '.$cour_id;
        }
        $cour = Db::table('course_times')->alias('ct')
            ->field('ct.id,cycle,hour_cour')
            ->join('course c','c.id=ct.course_id','left')
            ->where('c.user_id',$uid)
            ->where('ct.status',1)
            ->where($where)
            ->select();
        $data = [];  # 收集冲突的周数 节数
        $cycle = explode(',',$cycle);
        foreach ($cour as $k=>$v){
            $a = array_intersect($cycle,explode(',',$v['cycle']));
            if(!empty($a)){
                $res = $this->conflict($v['hour_cour'],$hour_cour);
                if($res){
                    $data[$k]['id'] = $v['id']; # $课时id
                    $data[$k]['hour'] = $res;
                }
            }
        }
        if(!empty($data)){
            rsort($data);
            return $data;
        }else{
            return 0;
        }

    }

    /**
     * 判断有没有课时冲突
     * @param $exist
     * @param $hour
     * @return array|bool
     */
    private function conflict($exist,$hour)
    {
        $h = explode('-',$hour);
        if($exist == $hour){
            $an = [];
            for($i = $h[1];$i<=$h[2];$i++){
                $an[] = (int)$i;
            }
            return ['week'=>$h[0],'cour'=>join(',',$an),'exist'=>$exist];
        };
        $exi = [];
        $hou = [];
        $a = explode('-',$exist);
        if($a[0] == $h[0]){
            for($i = $a[1];$i<=$a[2];$i++){
                $exi[] = (int)$i;
            }
            for($i = $h[1];$i<=$h[2];$i++){
                $hou[] = (int)$i;
            }
            $res = array_intersect($exi,$hou);
            if(!empty($res)){
                return ['week'=>$a[0],'cour'=>join(',',$res),'exist'=>join('-',$a)];
            }
        }
        return 0;   # 返回false表示课程时间上没有冲突
    }




}
