<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 12:44
 */

namespace app\api\controller;


use app\common\model\Tasks;
use app\common\error\Error;
use think\Db;

class Trips extends Base
{
    protected $table = '';
    protected $jurisdiction = [];
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    protected $tables;
    public function __construct()
    {
        parent::__construct();
        $this->table = new Tasks();

    }

    /**
     * 获取当日/指定日期的行程
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function showTrips()
    {
        $times = $this->request->post('times')? $this->request->post('times'):strtotime(date('Y-m-d'));
        $uid = $this->request->post('user_id')? $this->request->post('user_id'):false;
        $number = $this->request->post('number')? $this->request->post('number'):-1;
        if($times && $uid){
            if(!$this->is_timestamp($times)) return $this->json('不是正确的时间戳',103);
            $startTime = strtotime(date("Y-m-d",$times)." 0:0:0");
            $endTime = strtotime(date("Y-m-d",$times)." 23:59:59");
            $lend = '';
            if($number != '-1'){
                $lend = $number;
            }
            $rows = $this->table->alias('t')
                ->field('t.id tasks_id,t.type,t.ctime,t.content,t.start_time,t.end_time,t.title,t.plays_id')
                ->where('t.start_time','>',$startTime)
                ->where('t.start_time','<',$endTime)
                ->where('t.user_id',$uid)
                ->where('t.status',0)
                ->limit(0,$lend)
                ->order('t.start_time','desc')
                ->select();
            if($rows){
                $list = $this->statusType($rows);
                return $this->json('获取成功',Error::$SUCCESS,$list);
            }else {
                return $this->json('没有获取到数据', Error::$OBTAIN_ERROR);
            }

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }




    /**
     * 获取将来所有行程
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function showList()
    {
        $pages = $this->request->post('pages')?$this->request->post('pages'):1;
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $number = $this->request->post('number')?$this->request->post('number'):10;
        if($pages && $uid && $number){
            $endTime = strtotime(date("Y-m-d",time())." 23:59:59");
            $start = ($pages-1) * $number;
            $rows = $this->table->alias('t')
                ->field('t.id tasks_id,t.type,t.ctime,t.content,t.start_time,t.end_time,t.title,t.plays_id,t.type_name')
                ->where('t.start_time','>',$endTime)
                ->where('t.user_id',$uid)
                ->where('t.status',0)
                ->limit($start,$number)
                ->order('t.start_time','desc')
                ->select();
            if(!empty($rows)){
                $list = $this->statusType($rows);
                $count = $this->table->alias('t')
                    ->field('t.id tasks_id')
                    ->where('t.start_time','>',$endTime)
                    ->where('t.user_id',$uid)
                    ->where('t.status',0)
                    ->count();
                $total = ceil($count/$number);
                return $this->json('获取到数据',Error::$SUCCESS, $list,(int)$total);
            }else{
                return $this->json('未获取到数据', Error::$OBTAIN_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }

    /**
     * 查看 任务 行程 详情  ------  不需要
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function showInfoTr()
    {
        # 任务唯一 id
        $tasks_id = $this->request->post('tasks_id')?$this->request->post('tasks_id'):false;
        $type_name = $this->request->post('type_name')?$this->request->post('type_name'):false;   # 接收类型的名字
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        if($tasks_id && $uid && $type_name){
            $row = $this->tripsMerge(['uid'=>$uid,'tasks_id'=>$tasks_id],$type_name);
            if(!empty($row)){
                return $this->json('获取成功',Error::$SUCCESS, $row);
            }else{
                return $this->json('未获取到数据', Error::$OBTAIN_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }




    /**
     * 分类获取详情    待更新
     * @param $arr
     * @param $type
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function tripsMerge($arr, $type){
        $rows = [];
        switch ($type){
            case '社交':
                $rows = $this->table->alias('t')
                    ->field('t.id tasks_id,t.type,t.ctime,p.description k_description,p.user_id,p.images,p.total_times,p.aggregate,p.p_address,p.number,p.p_start,p.p_end')
                    ->join('plays p','t.plays_id=p.plays_id','left')
                    ->where('t.user_id',$arr['uid'])
                    ->find($arr['tasks_id']);
                break;
            case '服务':
                $rows = $this->table->alias('t')
                    ->field('t.id tasks_id,t.type,t.ctime,p.description k_description,p.user_id,p.images,p.aggregate,p.p_start,p.p_end,p.funds,p.charging')
                    ->join('s_serve p','t.plays_id=p.plays_id','left')
                    ->where('t.user_id',$arr['uid'])
                    ->find($arr['tasks_id']);
                break;
            case '兼职':
                $rows = $this->table->alias('t')
                    ->field('t.id tasks_id,t.type,t.ctime,r.description k_description,r.user_id,r.start_time,r.recruit_times,r.rec_address')
                    ->join('recruit r','r.id=t.plays_id','left')
                    ->where('t.user_id',$arr['uid'])
                    ->find($arr['tasks_id']);
                break;
        }
        return $rows;
    }

    /**
     * 处理任务状态
     * @param array $list
     * @return array
     */
    private function statusType(Array $list)
    {
        foreach ($list as $k=>$item){
            if((time() >= $item['start_time']) && (time()<=$item['end_time'])){
                $list[$k]['type'] = 2;
            }else if( (time() >= $item['end_time']) ){
                $list[$k]['type'] = 3;
            }
        }
        return $list;

    }

    /**
     * 任务唯一标识
     * @param $typeName
     * @return string
     */
    private function typeName($typeName)
    {
        switch ($typeName){
            case '打工':
                $typeName = '打工';
                break;
            case '兼职':
                $typeName = '兼职';
                break;
            case '服务':
                $typeName = '服务';
                break;
            case '社交':
                $typeName = '社交';
                break;
            default:
                $typeName = '服务';
                break;
        }
        return $typeName;
    }

}
