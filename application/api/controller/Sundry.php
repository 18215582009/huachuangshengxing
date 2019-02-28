<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/9
 * Time: 10:30 AM
 */

# 杂项 -- 回复评论，评论点赞，提交举报

namespace app\api\controller;


use app\common\error\Error;
use app\common\model\Comment;
use app\common\model\Report;
use think\Db;

class Sundry extends Base
{

    protected $jurisdiction = [];                    # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）

    /**
     * 回复评论
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function replyComment()
    {

        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $typeName    = $this->request->post('type_name')?$this->request->post('type_name'):false; #评论类型标识 上传 or 兼职
        $plays_id    = $this->request->post('plays_id')?$this->request->post('plays_id'):false; # 上船 or 兼职 id
        $pid = $this->request->post('pid')?$this->request->post('pid'):0;  # 0 为顶级评论-直接评论活动，其他为所回复的评论id
        $content = $this->request->post('content')?$this->request->post('content'):false;
        if($uid && $typeName && $plays_id && $content){
            /**
             *  这里后续做判断评论的内容数据是否存在
             */
            $table = '';
            switch($typeName){
                case '社交':
                    $table = 'plays';
                    break;
                case '服务':
                    $table = 's_serve';
                    break;
                case '兼职':
                    break;
            }
            $exist = Db::table($table)->field('plays_id')->find($plays_id);
            if(!$exist) return $this->json('你评论的内容已不存在',Error::$OBTAIN_ERROR);
            $data = [
                'user_id'       => (int)$uid,
                'type_name'     => $typeName,
                'type_id'       => $plays_id,
                'parent_id'     => $pid,
                'content'       => $content,
                'ctime'         => time()
            ];

            $res = Comment::create($data);
            if($res) return $this->json('评论成功',Error::$SUCCESS);
            else return $this->json('评论失败',Error::$INSERT_ERROR);


        }else{
            return $this->json('未获取参数',Error::$PARAMETER_ERROR);
        }


    }

    /**
     * 活动点赞接口
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function activityFabulous()
    {
        if($this->request->isPost()){
            $uid         = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $type_name   = $this->request->post('type_name')?$this->request->post('type_name'):false;
            $plays_id    = $this->request->post('plays_id')?$this->request->post('plays_id'):false;
            $release_id    = $this->request->post('release_id')?$this->request->post('release_id'):false;

            if((int)$uid && $type_name && (int)$plays_id && $release_id){

                $res = Db::table('activity_fabulous')->field('status,id')->where('type_name',$type_name)
                    ->where('type_id',$plays_id)->where('user_id',$uid)
                    ->where('release_id',$release_id)
                    ->find();
                if($res){
                    if($res['status'] == 1){
                        $data = [
                            'status'    => 0,
                            'ctime'     => time()
                        ];
                        $bool = Db::table('activity_fabulous')->where('id',$res['id'])->update($data);
                        if($bool) return $this->json('取消点赞成功',Error::$SUCCESS);
                        else return $this->json('取消点赞失败',Error::$UPDATE_ERROR);
                    }else{
                        $data = [
                            'status'    => 1,
                            'ctime'     => time()
                        ];
                        $bool = Db::table('activity_fabulous')->where('id',$res['id'])->update($data);
                        if($bool) return $this->json('点赞成功',Error::$SUCCESS);
                        else return $this->json('点赞失败',Error::$UPDATE_ERROR);
                    }
                }else{
                    $data = [
                        'status'    => 1,
                        'type_name' => $type_name,
                        'ctime'     => time(),
                        'user_id'   => $uid,
                        'release_id'   => $release_id,
                        'type_id'   => $plays_id
                    ];
                    $bool = Db::table('activity_fabulous')->insert($data);
                    if($bool) return $this->json('点赞成功',Error::$SUCCESS);
                    else return $this->json('点赞失败',Error::$UPDATE_ERROR);
                }

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }




        }
    }


    /**
     * 取消点赞和点赞
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function commentFabulous()
    {
        $uid         = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $comment_id  = $this->request->post('comment_id')?$this->request->post('comment_id'):false;
        if($uid && $comment_id){
            $res = Db::table('comment_fabulous')->field('status')
                ->where('user_id',$uid)->where('comment_id',$comment_id)
                ->find();
            if(!empty($res)){
                if($res['status'] == 1){
                    $bool = Db::table('comment_fabulous')->where('user_id',$uid)->where('comment_id',$comment_id)
                        ->update(['status'=>0]);
                    if($bool) return $this->json('取消点赞成功',Error::$SUCCESS);
                    else return $this->json('取消点赞失败',Error::$INSERT_ERROR);
                }else{
                    $bool = Db::table('comment_fabulous')->where('user_id',$uid)->where('comment_id',$comment_id)
                        ->update(['status'=>1]);
                    if($bool) return $this->json('点赞成功',Error::$SUCCESS);
                    else return $this->json('点赞失败',Error::$INSERT_ERROR);
                }

            }else{
                $data = [
                    'user_id'   => $uid,
                    'comment_id'=> $comment_id
                ];
                $bool = Db::table('comment_fabulous')->insert($data);
                if($bool) return $this->json('点赞成功',Error::$SUCCESS);
                else return $this->json('点赞失败',Error::$INSERT_ERROR);
            }

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

    /**
     * 提交举报内容
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function reportPush()
    {
        $uid         = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $reason  = $this->request->post('reason')?$this->request->post('reason'):false;
        $otherReason  = $this->request->post('other_reason')?$this->request->post('other_reason'):false;
        $typeName = $this->request->post('type_name')?$this->request->post('type_name'):false; # 举报标识 用户 or 活动 or 兼职
        $onlyId = $this->request->post('only_id')?$this->request->post('only_id'):false; # 对应 用户id or 上船活动id or 兼职id

        if($uid && ($reason || $otherReason) && $typeName && $onlyId){
            /**
             *  可以处理举报的限制
             */
            $bool = Report::get(['user_id'=>$uid,'type_name'=>$typeName,'only_id'=>$onlyId]);

            if($bool) return $this->json('你已经进行过举报，正在处理中',Error::$REPORT_LOADING);

            $data = [
                'user_id'       => $uid,
                'reason'        => $reason,
                'other_reason'   => $otherReason,
                'type_name'     => $typeName,
                'only_id'       => $onlyId,
                'ctime'         => time()
            ];
            $res = Report::create($data);
            if($res) return $this->json('举报成功',Error::$SUCCESS);
            else return $this->json('举报失败',Error::$INSERT_ERROR);

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

    /**
     * 拉黑
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function pullBlock()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $fid = $this->request->post('friend_id')?$this->request->post('friend_id'):false;
        $is = $this->request->post('is_l')?$this->request->post('is_l'):1;
        if($uid && $fid){
            $bool = Db::where('user_id',$uid)->where('friend_id',$fid)->find();
            if($bool) return $this->json('已经拉黑了',Error::$PULL_BLOCK_EXIST);
            /**
             * 拉黑前置逻辑处理--- 待续
             */

            $data = [
                'user_id'   => $uid,
                'friend_id' => $fid,
                'ctime'     => time(),
                'status'    => $is
            ];
            $bool = Db::table('pull_block')->insert($data);
            if($data) return $this->json('拉黑处理成功',Error::$SUCCESS);
            else return $this->json('拉黑处理失败',Error::$INSERT_ERROR);


        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }
}