<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 14:15
 */

namespace app\api\controller;

use think\Cache;
use think\Db;
use app\common\error\Error;

class Aboard extends Base
{
    protected $table = '';
    protected $jurisdiction = [];
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    private static $n = 0;
    private static $level = 0;
    private $comment_num = 3;
    public function __construct()
    {
        parent::__construct();
    }




    /**
     * 获取某个评论下所有回复评论
     * @return \app\Basics\Json|\think\Response|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function allComment(){
        $uid         = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $typeName    = $this->request->post('type_name')?$this->request->post('type_name'):false;
        $plays_id    = $this->request->post('plays_id')?$this->request->post('plays_id'):false;
        $pid         = $this->request->post('pid')?$this->request->post('pid'):false;
        if($uid && $typeName && $plays_id && $pid){
            $rows = [];

            if(Cache::has('plays_comment'.$pid)){
                $data = Cache::get('plays_comment'.$pid);
                $rows = json_decode($data,true);

            }else{
                $res = $this->allFree($typeName,$plays_id,0,$pid);
                if(count($res) > $this->comment_num){
                    foreach($res as $key=>$val){
                        if($key > $this->comment_num){
                            $rows[$key] = $val;
                        }
                    }
                    $data = json_encode($rows,JSON_UNESCAPED_UNICODE);
                    Cache::set('plays_comment'.$pid,$data);
                }
            }

            if($rows){
                return $this->json('获取成功',Error::$SUCCESS,$rows);
            }else{
                return $this->json('未获取到数据',Error::$OBTAIN_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }

    /**
     * 单独获取评论数据
     * @return \app\Basics\Json|\think\Response|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function getComment()
    {
        $uid        = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $typeName   = $this->request->post('type_name')?$this->request->post('type_name'):false;
        $plays_id   = $this->request->post('plays_id')?$this->request->post('plays_id'):false;
        $start      = $this->request->post('start')?$this->request->post('start'):1;
        if($uid && $typeName && $plays_id){
            $comment = [];
            $start = $start-1;
            foreach($this->obtainComment($typeName,$plays_id,$start,0) as $item){

                if($item){
                    $count = Db::table('comment_fabulous')
                        ->field('id')
                        ->where('comment_id',$item['comment_id'])
                        ->where('status',1)
                        ->count();
                    $isF = Db::table('comment_fabulous')->field('id')
                        ->where('comment_id',$item['comment_id'])
                        ->where('status',1)
                        ->where('user_id',$uid)
                        ->find();
                    if($isF) $item['is_fabulous'] = 1;else $item['is_fabulous'] = 0; # 获取用户对这条评论是否已经点赞
                    $result = $this->allFree($typeName,$plays_id,$start,$item['comment_id']);
                    if($count)$item['fabulous'] = $count;else $item['fabulous'] = 0;
                    if($result){
                        $son_comment = [];
                        $cache_comment = [];
                        if(count($result) > $this->comment_num){
                            foreach($result as $key=>$val){
                                if($key < $this->comment_num){
                                    $son_comment[$key] = $val;
                                }else{
                                    $cache_comment[$key] = $val;
                                }
                            }
                        }else{
                            $son_comment = $result;
                        }
                        $cache_comment = json_encode($cache_comment,JSON_UNESCAPED_UNICODE);
                        Cache::set('plays_comment'.$item['comment_id'],$cache_comment);
                        $item['son'] = $son_comment;
                        $comment[] = $item;
                    }else{
                        $item['son'] = [];
                        $comment[] = $item;
                    }
                }
            }
            if($comment){
                return $this->json('获取成功',Error::$SUCCESS,$comment,$this->pages($typeName,$plays_id));
            }else{
                return $this->json('未获取到数据',Error::$OBTAIN_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$OBTAIN_ERROR);
        }
    }



    /**
     * 单独获取评论总页数
     * @param $typeName
     * @param $plays_id
     * @return float
     * @throws \think\Exception
     */
    private function pages($typeName,$plays_id){
        $comment = Db::table('comment')->alias('c')
            ->field('c.id')
            ->join('users u','u.user_id=c.user_id','left')
            ->where('c.type_name',$typeName)
            ->where('c.type_id',$plays_id)
            ->where('c.status',1)
            ->where('c.parent_id',0)
            ->count();
        $total = ceil($comment / 10);
        return $total;
    }

    /**
     * 取到当前评论下的下级评论
     * @param $typeName
     * @param $plays_id
     * @param int $start
     * @param int $pid
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function obtainFree($typeName,$plays_id,$start=0,$pid=0){

        $comment = [];
        foreach ($this->obtainComment($typeName,$plays_id,$start,$pid) as $item){
            # if(!$item) return $this->json('没有获取到数据',Error::$OBTAIN_ERROR);
            if($item){
                $count = Db::table('comment_fabulous')
                    ->field('id')
                    ->where('comment_id',$item['comment_id'])->count();
                $result = $this->frees($typeName,$plays_id,$start,$item['comment_id']);
                if($count)$item['fabulous'] = $count;else $item['fabulous'] = 0;
                if($result){
                    $item['son'] = $result;
                    $comment[] = $item;
                }else{
                    $item['son'] = [];
                    $comment[] = $item;
                }
            }
        }
        return $comment;
    }

    /**
     * 递归查询父级评论下的所有评论  组装数据格式不同
     * @param $typeName
     * @param $plays_id
     * @param $start
     * @param $pid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function allFree($typeName,$plays_id,$start,$pid){

        $free = [];

        $res = Db::table('comment')->alias('c')
            ->field('c.id comment_id,c.parent_id,c.content,c.user_id,c.ctime,u.nickname,u.head_image,c.ctime')
            ->join('users u','u.user_id=c.user_id','left')
            ->where('c.type_name',$typeName)
            ->where('c.type_id',$plays_id)
            ->where('c.status',1)
            ->where('c.parent_id',$pid)
            ->order('c.ctime','desc')
            ->select();

        if($res){
            foreach ($res as $key=>$item){
                $to = Db::table('comment')->alias('c')
                    ->field('c.user_id to_user_id,u.nickname to_nickname')
                    ->join('users u','u.user_id=c.user_id','left')
                    ->where('c.id',$item['parent_id'])
                    ->find();
                $res[$key]['to_nickname'] = $to['to_nickname'];
                $res[$key]['to_user_id'] = $to['to_user_id'];

                $a = Db::table('comment')->alias('c')
                    ->field('c.id comment_id,c.parent_id,c.content,c.user_id,c.ctime,u.nickname,u.head_image,c.ctime')
                    ->join('users u','u.user_id=c.user_id','left')
                    ->where('c.type_name',$typeName)
                    ->where('c.type_id',$plays_id)
                    ->where('c.status',1)
                    ->where('c.parent_id',$item['comment_id'])
                    ->order('c.ctime','desc')
                    ->select();
                if($a){
                    $free = $this->allFree($typeName,$plays_id,$start,$item['comment_id']);
                }
            }

            $free = array_merge($free,$res);

            return $free;
        }else{
            return false;
        }
    }




    /**
     * 递归查询父级评论下的所有评论  组装数据格式不同
     * @param $typeName
     * @param $plays_id
     * @param $start
     * @param $pid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function frees($typeName,$plays_id,$start,$pid,$len = 5){
        $free = [];
        #self::$level = $len;
        #if(empty($len)){ $len = ''; }else $start = $start*$len;
        $res = Db::table('comment')->alias('c')
            ->field('c.id comment_id,c.parent_id,c.content,c.user_id,c.ctime,u.nickname,u.head_image,c.ctime')
            ->join('users u','u.user_id=c.user_id','left')
            ->where('c.type_name',$typeName)
            ->where('c.type_id',$plays_id)
            ->where('c.status',1)
            ->where('c.parent_id',$pid)
            #->limit($start,$len)
            ->order('c.ctime','desc')
            ->select();
        if($res){
            foreach ($res as $key=>$item){
                $to = Db::table('comment')->alias('c')
                    ->field('c.user_id to_user_id,u.nickname to_nickname')
                    ->join('users u','u.user_id=c.user_id','left')
                    ->where('c.id',$item['parent_id'])
                    ->find();
                $res[$key]['to_nickname'] = $to['to_nickname'];
                $res[$key]['to_user_id'] = $to['to_user_id'];
                #if(count($res) < self::$level){
                if(1){

                    #self::$n += count($res);

                    #$v = $len - self::$n;

                    #if($v < 0) $v = '';
                    $a = Db::table('comment')->alias('c')
                        ->field('c.id comment_id,c.parent_id,c.content,c.user_id,c.ctime,u.nickname,u.head_image,c.ctime')
                        ->join('users u','u.user_id=c.user_id','left')
                        ->where('c.type_name',$typeName)
                        ->where('c.type_id',$plays_id)
                        ->where('c.status',1)
                        ->where('c.parent_id',$item['comment_id'])
                        #->limit($start,$v)
                        ->order('c.ctime','desc')
                        ->select();
                    #self::$level = $v;

                    if($a /*&& self::$n < $len*/){
                        $free = $this->frees($typeName,$plays_id,$start,$item['comment_id'],self::$level);
                    }
                }
            }

            $free = array_merge($free,$res);

            return $free;
        }else{
            return false;
        }
    }



    /**
     * 获取评论 顶级 id
     * @param $typeName
     * @param $plays_id
     * @param int $start
     * @param int $pid
     * @return \Generator
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     */

    private function obtainComment($typeName,$plays_id,$start=0,$pid=0){
        $comment = Db::table('comment')->alias('c')
            ->field('c.id comment_id,c.parent_id,c.content,c.user_id,c.ctime,u.nickname,u.head_image,c.ctime')
            ->join('users u','u.user_id=c.user_id','left')
            ->where('c.type_name',$typeName)
            ->where('c.type_id',$plays_id)
            ->where('c.status',1)
            ->where('c.parent_id',$pid)
            #->limit(($start*10),10)
            ->order('c.ctime','desc')
            ->select();

        if(!empty($comment)){
            foreach ($comment as $item){
                yield $item;
            }
        }else{
            yield [];
        }

    }


}
