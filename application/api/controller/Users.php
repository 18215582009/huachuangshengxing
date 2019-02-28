<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 14:05
 */

namespace app\api\controller;


use app\common\error\Error;
use think\Db;
use think\Paginator;

class Users extends Base
{
    protected $jurisdiction = ['editInfo','showInfo'];                    # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    private $tables ;
    private $uid = null;
    public function __construct()
    {
        parent::__construct();
        $this->tables = new \app\common\model\Users();
    }

    /**
     * 个人用户基本信息
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function showInfo()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        if((int)$uid){
            $info = $this->tables->alias('u')
                ->field('u.user_id,username,nickname,kjmessage_id,s.sname school,grade,u.sex,u.head_image,u.background_img,u.signature,user_birth,u.uuid')
                ->join('users_secretary us', 'us.user_id=u.user_id','left')
                ->join('school s','u.school=s.id','left')
                ->find($uid);
            if($info){

                # 获取关注和粉丝
                $rows = Db::table('follows')->field('count(id) follows')->where('f_user_id',$uid)->find();
                if($rows) $info['follows'] = $rows['follows'];else $info['follows'] = 0;
                if($rows) $info['fans'] = $rows['follows'];else $info['fans'] = 0;
                # 获取点赞数量
                $rows = Db::table('comment_fabulous')->alias('cf')
                    ->field('u.username')
                    ->join('comment c','cf.comment_id=c.id','left')
                    ->join('users u','u.user_id=c.user_id','left')
                    ->where('u.user_id',$uid)
                    ->where('cf.status',1)
                    ->count();
                $pla_fabu = Db::table('activity_fabulous')->field('id')->where('release_id',$uid)
                    ->count();

                if($rows || $pla_fabu) $info['fabulous'] = ($rows+$pla_fabu);else $info['fabulous'] = 0;

                $res = Db::table('medal')->alias('m')
                    ->field('m.id medal_id,ms.name,ms.url')
                    ->join('medal_sys ms','m.md_id=ms.id','left')
                    ->where('user_id',$uid)
                    ->select();
                if(!empty($res)){
                    $info['medal'] = $res;
                }else{
                    $info['medal'] = 0;
                }


                return $this->json('获取成功', Error::$SUCCESS , $info);
            }else{
                return $this->json('异常，没有用户数据',Error::$OBTAIN_ERROR);

            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }

    /**
     * 修改个人信息
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function editInfo()
    {
        $uid  = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $post = $this->request->post();
        $data = $this->filterField($post);

        if($uid && !empty($post)){
            /*$data = [];
            $file = $this->request->file('image')?$this->request->file('image'):false;

            $bfile = $this->request->file('b_image')?$this->request->file('b_image'):false;

            $head_img = $back_img = '';
            if(isset($post['user_birth'])) $data['user_birth'] = $post['user_birth'];
            if(isset($post['nickname'])) $data['nickname'] = $post['nickname'];
            if(isset($post['signature'])) $data['signature'] = $post['signature'];
            if(isset($post['sex'])) $data['sex'] = $post['sex'];

            if(!$data && !empty($this->request->file())) return $this->json('未获取到修改数据',Error::$PARAMETER_ERROR);
            if(!empty($file)){
                $delImg = Db::table('users')->field('head_image')->find($uid);
                # public/uploads/aboards/20190109/6667346cfb050094b0a7a3bd840f5c32.jpeg
                $res = $this->uploads($file);
                if($res){
                    $head_img = join(',',$res);
                    if($delImg){
                        $file = ROOT_PATH.$delImg['head_image'];
                        if(is_file($file)){
                            unlink($file);
                        }
                    }
                }
            }
            if(!empty($bfile)){
                $delImg = Db::table('users')->field('background_img')->find($uid);
                # public/uploads/aboards/20190109/7acc949a977106a0054fb630fa88682a.jpeg
                $res = $this->uploads($bfile);
                if($res){
                    $back_img = join(',',$res);
                    if($delImg){
                        $file = ROOT_PATH.$delImg['background_img'];
                        if(is_file($file)){
                            unlink($file);
                        }
                    }
                }
            }
            if($head_img) $data['head_image'] = $head_img;
            if($head_img) $data['background_img'] = $back_img;*/
            $data['utime'] = time();
            $bool = Db::table('users')->where('user_id',$uid)->update($data);
            if($bool) return $this->json('修改成功',Error::$SUCCESS);
            else return $this->json('修改失败',Error::$UPDATE_ERROR);
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

    /**
     * 绑定极光推送 id
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */
    public function kjmessage_id()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $kjmessage_id = $this->request->post('kjmessage_id')?$this->request->post('kjmessage_id'):false;
            if($uid && $kjmessage_id){
                $data = ['kjmessage_id'=>$kjmessage_id,'utime'=>time()];
                $res = Db::table('users')->where('user_id',$uid)->update($data);
                if($res)return $this->json('账号绑定成功',Error::$SUCCESS);
                else return $this->json('绑定失败',Error::$UPDATE_ERROR);

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }



        }
    }

    /**
     * 为上一个方法过滤下不需要的参数
     * @param array $post
     * @return array
     */
    private function filterField(array $post)
    {
        if(isset($post['user_id'])) unset($post['user_id']);
        if(isset($post['tk'])) unset($post['tk']);
        if(isset($post['app_os'])) unset($post['app_os']);
        if(isset($post['app_version'])) unset($post['app_version']);
        if(isset($post['cid'])) unset($post['cid']);
        return $post;

    }


    /**
     * 修改小秘书资料
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function editSec()
    {
        $call = $this->request->post('call_name')?$this->request->post('call_name'):false;
        $uid  = $this->request->post('user_id')?$this->request->post('user_id'):false;
        if($call && $uid){
            $bool = Db::table('users_secretary')->where('user_id',$uid)
                ->update(['sec_call'=>$call]);
            if($bool){
                return $this->json('修改成功',Error::$SUCCESS);
            }else{
                return $this->json('修改失败',Error::$UPDATE_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

    /**
     * 获取小秘书信息
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function showSec()
    {
        $uid  = $this->request->post('user_id')?$this->request->post('user_id'):false;
        if($uid){
            $row = Db::table('users_secretary')
                ->field('id sec_id,user_id,sec_name,sec_sex,sec_call,images,intimate,charm,sec_level,attack,fatigue,spirit,hunger,grade')
                ->where('user_id',$uid)
                ->find();
            if(!empty($row)){
                return $this->json('获取成功',Error::$SUCCESS, $row);
            }else{
                return $this->json('未获取到数据',Error::$OBTAIN_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }

    /**
     * 查看其它用户的卡片信息
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cardUsers()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;   # 用户本身
        $newUid = $this->request->post('other_id')?$this->request->post('other_id'):false;  # 需要查看的用户id
        # if($uid == $newUid) return $this->json('参数有误',Error::$PARAMETER_ILLEGAL);
        if(($uid && $newUid) || $uid){
            # count(f.f_user_id) follows,count(f.f_user_id) fans,count(fa_user_id) fabulous

            $info = $this->tables->alias('u')
                ->field('u.user_id other_id,nickname,s.sname school,us.grade,us.id sec_id,u.description k_description,u.sex,u.head_image,u.background_img,u.uuid')
                ->join('follows f','f.f_user_id=u.user_id','left')
                
                ->join('fabulous_u fu','fu.fa_user_id=u.user_id','left')
                ->join('users_secretary us', 'us.user_id=u.user_id','left')
                ->join('school s','u.school=s.id','left')
                ->find($newUid?$newUid:$uid);
            if(empty($info)) return $this->json('没有获取到该用户的信息',Error::$SUCCESS);
            # 获取关注和粉丝
            $rows = Db::table('follows')->field('count(id) follows')->where('f_user_id',$newUid)->find();
            if($rows) $info['follows'] = $rows['follows'];else $info['follows'] = 0;
            if($rows) $info['fans'] = $rows['follows'];else $info['fans'] = 0;
            # 获取点赞数量
            $rows = Db::table('comment_fabulous')->alias('cf')
                ->field('u.username')
                ->join('comment c','cf.comment_id=c.id','left')
                ->join('users u','u.user_id=c.user_id','left')
                ->where('u.user_id',$newUid?$newUid:$uid)
                ->where('cf.status',1)
                ->count();  # 评论点赞
            $pla_fabu = Db::table('activity_fabulous')->field('id')->where('release_id',$uid)
                ->count();   # 活动点赞
            if($rows || $pla_fabu) $info['fabulous'] = ($rows+$pla_fabu);else $info['fabulous'] = 0;
            # 获取勋章
            /*$medal = Db::table('medal')->field('id medal_id,medal_name,medal_icon')->where('user_id',$newUid?$newUid:$uid)->select();*/
            $medal = Db::table('medal')->alias('m')
                ->field('m.id medal_id,ms.name,ms.url')
                ->join('medal_sys ms','m.md_id=ms.id','left')
                ->where('user_id',$newUid?$newUid:$uid)
                ->select();
            if($newUid && ($uid != $newUid)){ # 如果是查看自己就没有下面这些信息
                $where = "(user_id={$uid} or friend_id={$uid}) and (user_id={$newUid} or friend_id={$newUid})";
                # 是否是好友
                $friend = Db::table('friend')->field('status')->where($where)->where('is_del',0)->find();
                # 是否已关注
                $follows = Db::table('follows')->field('ctime')->where('user_id',$uid)->where('f_user_id',$newUid)->find();

                $fabulous = Db::table('fabulous_u')->field('ctime')->where('user_id',$uid)->where('fa_user_id',$newUid)->find();  # 是否已点赞
                if(empty($follows)) $info['is_follows'] = 0; else $info['is_follows'] = 1;
                # 是否已经点赞
                if(empty($fabulous)) $info['is_fabulous'] = 0; else $info['is_fabulous'] = 1;
                # 是否是好友
                if(empty($friend)) $info['is_friend'] = 0; else $info['is_friend'] = 1;
            }

            # 勋章没有为空
            if(empty($medal)) $info['medal'] = ''; else $info['medal'] = $medal;
            # 是否已经关注

            if(!empty($info)){
                return $this->json('获取成功',Error::$SUCCESS,$info);
            }else{
                return $this->json('未获取到数据',Error::$OBTAIN_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }


    /**
     * 点赞或关注用户
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fabulous()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;   # 用户本身
        $newUid = $this->request->post('other_id')?$this->request->post('other_id'):false;  # 需要关注的用户id
        $type = $this->request->post('type')?$this->request->post('type'):false;            # 关注 1 or 点赞 2
        if($uid && $newUid && $type){
            $data = [];
            $table = $msg = $where ='';
            switch ($type){
                case 1:
                    $table = 'follows';
                    $msg = '关注';
                    $data = [
                        'user_id'    => $uid,
                        'f_user_id'  => $newUid,
                    ];
                    $where = " user_id={$uid} and f_user_id={$newUid}";
                    break;
                case 2:
                    $table = 'fabulous_u';
                    $msg = '点赞';
                    $data = [
                        'user_id'    => $uid,
                        'fa_user_id'  => $newUid,
                    ];
                    $where = " user_id={$uid} and f_user_id={$newUid}";
                    break;
            }
            if($uid == $newUid) return $this->json('不能进行自己'.$msg.'自己',Error::$OBTAIN_ERROR);

            $follows = Db::table($table)->field('ctime')->where($where)->find();

            if(!empty($follows)){
                $bool = Db::table($table)->where($where)->delete();
                if($bool){
                    return $this->json('取消'.$msg.'成功',Error::$SUCCESS);
                }else{
                    return $this->json('取消'.$msg.'失败',Error::$INSERT_ERROR);
                }
            };
            $res = Db::table($table)->insert($data);
            if($res){
                return $this->json($msg.'成功',Error::$SUCCESS);
            }else{
                return $this->json($msg.'失败',Error::$INSERT_ERROR);
            }
        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }
    }


}
