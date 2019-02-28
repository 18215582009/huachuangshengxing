<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/8
 * Time: 9:42 AM
 */

namespace app\api\controller;

# 搜索接口

use app\common\error\Error;
use think\Cache;
use think\Db;

class Search extends Base
{
    protected $jurisdiction = [];                    # 不需要token 验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）

    /**
     * 活动搜索接口，以uuid模糊搜索
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function aboardSearch()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $uuid = $this->request->post('uuid')?$this->request->post('uuid'):false;

        if($uuid){
            $where = ' uuid like "%'.$uuid.'%"';
            $id = Db::table('users')->field('user_id id')->where($where)->find();

            $plays = Db::table('plays')->alias('p')
                ->field('p.event_title,p.plays_id,p.ask,p.p_start,p.launch,sst.id s_type_id,sst.type_name,u.head_image,us.grade,u.sex,p.number,u.u_vip')
                ->join('users u','u.user_id=p.user_id','left')
                ->join('s_sociality_type sst','sst.id=p.about_type_id','left')
                ->join('sociality_type st','st.id=sst.type_id','left')
                ->join('users_secretary us','u.user_id=us.user_id','left')
                ->where('p.user_id',$id['id'])
                ->where('p_start','>',time())
                ->order('p_start','asc')
                ->select();
            $soc = Db::table('s_serve')->alias('p')
                ->field('p.event_title,p.plays_id,p.ask,p.funds,p.p_start,sst.id s_type_id,sst.type_name,u.head_image,us.grade,u.sex,p.number,u.u_vip')
                ->join('users u','u.user_id=p.user_id','left')
                ->join('s_sociality_type sst','sst.id=p.about_type_id','left')
                ->join('sociality_type st','st.id=sst.type_id','left')
                ->join('users_secretary us','u.user_id=us.user_id','left')
                ->where('p.user_id',$id['id'])
                ->where('p.end_times','>',time())
                ->order('p.end_times','asc')
                ->select();

            if($plays){
                foreach($plays as $k=>$v){
                    $plays[$k]['badge'] = '社交';
                }
            }
            if($soc){
                foreach($soc as $k=>$v){
                    $soc[$k]['badge'] = '服务';
                }
            }

            $data = [];
            if($plays && $soc){
                $data = array_merge($plays,$soc);
            }elseif($plays && !$soc){
                $data = $plays;
            }else{
                $data = $soc;
            }
            if($data){
                return $this->json('获取成功',Error::$SUCCESS,$data);
            }else{
                return $this->json('没有获取到数据',Error::$OBTAIN_ERROR);
            }
        }else{
            return $this->json('未获取到参数',Error::$PARAMETER_ERROR);
        }


    }

}