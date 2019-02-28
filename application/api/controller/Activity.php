<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/16
 * Time: 10:30 AM
 */

namespace app\api\controller;

# 上船-》活动服务板块下活动创建

use app\common\error\Error;
use think\Cache;
use think\Db;

class Activity extends Base
{
    protected $jurisdiction = [];                    # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    private $num = 10;


    /**
     * 服务板块活动发布
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function releaseActivity()
    {
        if($this->request->isPost()){


            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $actPath = $this->request->post('paths')?$this->request->post('paths'):false; # 活动图片地址
            $stid = $this->request->post('s_type_id')?$this->request->post('s_type_id'):false; # 三级分类id
            $eventTitle = $this->request->post('event_title')?$this->request->post('event_title'):false; # 活动名称 二级分类-三级分类
            $start = $this->request->post('p_start')?$this->request->post('p_start'):false; # 开始时间
            $end = $this->request->post('p_end')?$this->request->post('p_end'):false; # 结束时间
            $ask = $this->request->post('ask')?$this->request->post('ask'):false; # 性别要求 1 男 2 女
            $voice = $this->request->post('voice')?$this->request->post('voice'):false; # 语音文件
            $voice_time = $this->request->post('voice_time')?$this->request->post('voice_time'):false; # 语音时长
            $funds = $this->request->post('funds')?$this->request->post('funds'):false; # 经费
            $charging = $this->request->post('charging')?$this->request->post('charging'):false; # 计费单位
            $des = $this->request->post('k_des')?$this->request->post('k_des'):false; # 备注

            if($uid && $ask && $stid && $eventTitle && $start && $end && $funds && $charging){

                if(Cache::has('serve_id'.$uid)) return $this->json('发布同类型活动太频繁',Error::$RELEASE_OFTEN);

                $data = [
                    'user_id'       => $uid,
                    'images'        => $actPath,
                    'about_type_id' => $stid,
                    'launch'        => time(),
                    'event_title'   => $eventTitle,
                    'p_start'       => $start,
                    'end_times'     => $end,
                    'ask'           => $ask,
                    'voice'         => $voice,
                    'voice_time'    => $voice_time,
                    'funds'         => $funds,
                    'charging'      => $charging,
                    'description'   => $des
                ];
                $data = array_filter($data);

                $bool = Db::table('s_serve')->insert($data);
                if($bool){
                    Cache::set('serve_id'.$uid,$stid,60);

                    return $this->json('发布成功',Error::$SUCCESS);
                }else{
                    return $this->json('发布失败',Error::$INSERT_ERROR);
                }

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }


        }

    }

    /**
     * 发布社交活动
     *
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function releasePlays()
    {

        if($this->request->isPost()){

            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $actPath = $this->request->post('paths')?$this->request->post('paths'):false; # 活动图片地址
            #$tid = $this->request->post('type_id')?$this->request->post('type_id'):false; # 二级分类id
            $stid = $this->request->post('s_type_id')?$this->request->post('s_type_id'):false; # 三级分类id
            $eventTitle = $this->request->post('event_title')?$this->request->post('event_title'):false; # 活动名称 二级分类-三级分类
            $start = $this->request->post('p_start')?$this->request->post('p_start'):false; # 开始时间
            $end = $this->request->post('p_end')?$this->request->post('p_end'):false; # 结束时间
            $ask = $this->request->post('ask')?$this->request->post('ask'):false; # 性别要求 1 男 2 女
            $number = $this->request->post('number')?$this->request->post('number'):false; # 活动人数限制 -1 没有限制
            $aggregate = $this->request->post('aggregate')?$this->request->post('aggregate'):false; # 集合地点
            $des = $this->request->post('k_des')?$this->request->post('k_des'):false; # 备注


            if($uid && $ask && $stid && $eventTitle && $start && $end && $number && $aggregate){


                if(Cache::has('serve_id'.$uid)) return $this->json('发布同类型活动太频繁',Error::$RELEASE_OFTEN);

                $data = [
                    'user_id'       => intval($uid),
                    'images'        => $actPath,
                    'about_type_id' => intval($stid),
                    'launch'        => time(),
                    'event_title'   => $eventTitle,
                    'p_start'       => intval($start),
                    'end_times'     => intval($end),
                    'ask'           => intval($ask),
                    'number'        => intval($number),
                    'aggregate'     => $aggregate,
                    'description'   => $des
                ];

                $data = array_filter($data);
                $bool = Db::table('plays')->insertGetId($data);
                if($bool){

                    Cache::set('serve_id'.$uid,$stid,60);

                    return $this->json('发布成功',Error::$SUCCESS);
                }else{
                    return $this->json('发布失败',Error::$INSERT_ERROR);
                }

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }


        }

    }

    /**
     * 参加社交活动
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function enrollPlays()
    {
        if($this->request->isPost()){

            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;

            $pid = $this->request->post('plays_id')?$this->request->post('plays_id'):false;
            $type = $this->request->post('type')?$this->request->post('type'):false;  # 是否参加活动
            if($uid && $pid && ($type == 2)){

                $plays = Db::table('plays')->field('p_start,number')->where('status',1)->find($pid);

                if(!$plays) return $this->json('该活动未找到或已关闭',Error::$OBTAIN_ERROR);
                if($plays['p_start'] <= time()) return $this->json('活动已开始，不能参加',Error::$INSERT_ERROR);

                $aid = Db::table('plays_attach')->field('plays_attach_id')
                    ->where('user_id',$uid)->where('plays_id',$pid)
                    ->find();
                if($aid) return $this->json('已参加活动',Error::$INSERT_ERROR);

                $count = Db::table('plays_attach')->field('plays_attach_id')->where('plays_id')->count();

                if(($count+1) > $plays['number'] && $plays['number'] != 0) return $this->json('活动已经满员');

                $data = [
                    'user_id'   => $uid,
                    'plays_id'  => $pid
                ];

                $bool = Db::table('plays_attach')->insert($data);
                if($bool){
                    if($this->addTrips($uid,$pid)){
                        #$this->redis->del('playDetails'.$pid); # 清除缓存
                        return $this->json('参加活动成功',Error::$SUCCESS);
                    }else
                        return $this->json('生成行程任务失败',Error::$INSERT_ERROR);
                }else{
                    return $this->json('参加活动失败',Error::$INSERT_ERROR);
                }


            }else if($uid && $pid && ($type == 1)){

                $bool = Db::table('plays_attach')
                    ->where('user_id',$uid)->where('plays_id',$pid)
                    ->delete();
                if($bool){
                    $bool = Db::table('tasks')->where('type_name','社交')
                        ->where('user_id',$uid)->where('plays_id',$pid)
                        ->delete();
                    if($bool){
                        return $this->json('退出活动成功',Error::$SUCCESS);
                    }else{
                        return $this->json('删除行程失败',Error::$DELETE_ERROR);
                    }
                }else{
                    return $this->json('退出活动失败',Error::$DELETE_ERROR);
                }

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }

        }

    }


    /**
     * 报名成功并生成 任务行程
     * @param $uid
     * @param $pid
     * @return int|string
     */
    private function addTrips($uid,$pid)
    {
        $res = Db::table('plays')->field('p_start,end_times,description,event_title')->find($pid);
        if($res){
            $data = [
                'user_id'        => $uid,
                'plays_id'       => $pid,
                'title'          => $res['event_title'],
                'type_name'      => "社交",
                'content'        => $res['description'],
                'start_time'     => $res['p_start'],
                'end_time'       => $res['end_times'],
                'ctime'          => time()
            ];
            $bool = Db::table('tasks')->insert($data);
            return $bool;
        }
    }


    /**
     * 服务下单
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function serveOrder()
    {
        if($this->request->isPost()){

            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $sid = $this->request->post('serve_id')?$this->request->post('serve_id'):false;
            $order = $this->request->post('second')?$this->request->post('second'):false;
            $des = $this->request->post('k_des')?$this->request->post('k_des'):false;
            if($uid && $sid && $order){

                $row = Db::table('s_serve')->field('end_times')->where('status',1)->find($sid);
                if(!$row) return $this->json('未获取活动详情',Error::$OBTAIN_ERROR);

                if($row['end_times'] < time()) return $this->json('服务-活动已结束，不能下单',Error::$INSERT_ERROR);

                $data = [
                    'second'        => $order,
                    'user_id'       => $uid,
                    'serve_id'      => $sid,
                    'description'   => $des,
                    'ctime'         => time()
                ];
                $data = array_filter($data);
                $bool = Db::table('s_order')->insert($data);
                if($bool) return $this->json('参加下单成功',Error::$SUCCESS);
                else return $this->json('下单失败',Error::$INSERT_ERROR);

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }


        }
    }

}