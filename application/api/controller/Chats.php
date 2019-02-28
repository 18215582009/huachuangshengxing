<?php
/**
 * Created by PhpStorm.
 * User: smallseven
 * Date: 2019/1/10
 * Time: 1:35 PM
 */

namespace app\api\controller;


use app\common\error\Error;
use think\Db;


class Chats extends Base
{
    protected $jurisdiction = [];                    # 不需要验证的方法
    protected $beforeActionList = ['limitAction','limitCheck'];  # 每个类中需添加前置操作（除登录注册外）
    private $num = 20;



    /**
     * 获取聊天记录
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function getChats()
    {

        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $fid = $this->request->post('friend_id')?$this->request->post('friend_id'):false;
        $page = $this->request->post('page')?$this->request->post('page'):0;
        if($uid && $fid){
            $bool = Db::table('chats_user')->field('id')
                ->where('user_id',$uid)
                ->where('friend_id',$fid)
                ->find();
            $fr = Db::table('chats_user')->field('id')
                ->where('friend_id',$uid)
                ->where('user_id',$fid)
                ->find();
            if(!$bool && !$fr) return $this->json('没有查询到与此用户建立聊天关系',Error::$OBTAIN_ERROR);
            $hide = Db::table('hide_chats')
                ->field('h_time,status')
                ->where('user_id',$uid)
                ->where('status',2)
                ->find();
            $where = '';
            if($hide){
                $where = ' send_time >'.$hide['h_time'];
            }
            $start = $page * $this->num;
            $ufWhere = '';
            if($bool && $fr){
                $ufWhere = ' user_cid='.$bool['id'].' or user_cid='.$fr['id'];
            }else{
                if($bool){
                    $ufWhere = ' user_cid='.$bool['id'];
                }
                if($fr){
                    $ufWhere = ' user_cid='.$fr['id'];
                }
            }

            $chats = Db::table('chats')->alias('c')->field('c.id chats_id,c.chat_description,c.send_time,cu.user_id,cu.friend_id')
                ->join('chats_user cu','cu.id=c.user_cid','left')
                ->where($where)
                ->where($ufWhere)
                ->limit($start,$this->num)
                ->order('send_time','desc')
                ->select();
            $total = Db::table('chats')->alias('c')->field('id')
                ->join('chats_user cu','cu.id=c.user_cid','left')
                ->where($where)
                ->where($ufWhere)
                ->count();
            $data = [
                'msg' => !empty($chats)?'获取成功':'没有获取到数据',
                'status'=> !empty($chats)?Error::$SUCCESS:Error::$OBTAIN_ERROR,
                'data'  => $chats,
                'total' => !empty($chats)?(ceil($total/$this->num)):'',
                'is_hide'   => empty($where)?1:2   # 1 代表之前没有隐藏的 2 表示有隐藏的
            ];
            return $this->json($data);

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }


    /**
     * 添加 聊天关系接口
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function storageChatUser()
    {
        if($this->request->isPost()){
            $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
            $fid = $this->request->post('friend_id')?$this->request->post('friend_id'):false;
            if((int)$uid && (int)$fid){
                $bool = Db::table('chat_users')->where('user_id',$uid)->where('friend_id',$fid)->find();
                if($bool) return $this->json('已存在聊天关系',Error::$SUCCESS);
                $data = [
                    'user_id'    => $uid,
                    'friend_id'  => $fid
                ];
                $res = Db::table('chat_users')->insert($data);
                if($res) return $this->json('添加聊天关系成功',Error::$SUCCESS);
                else return $this->json('添加失败',Error::$INSERT_ERROR);

            }else{
                return $this->json('参数未获取',Error::$PARAMETER_ERROR);
            }

        }

    }

    /**
     * 设置聊天记录是否隐藏
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     * @throws \think\Exception
     */

    public function hideChats()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        $hide = $this->request->post('hide')?$this->request->post('hide'):false; # 1显示 2 隐藏
        if($uid && $hide){

            $res = Db::table('hide_chats')->field('id')
                ->where('user_id',$uid)
                ->find();
            $data = [
                'h_time'    => time(),
                'status'    => $hide
            ];
            if($res){
                $bool = Db::table('hide_chats')->where('id',$res['id'])->update($data);
                if($bool) return $this->json('设置成功',Error::$SUCCESS);
                else return $this->json('设置失败',Error::$OBTAIN_ERROR);
            }else{
                $bool = Db::table('hide_chats')->insert($data);
                if($bool) return $this->json('设置成功',Error::$SUCCESS);
                else return $this->json('设置失败',Error::$OBTAIN_ERROR);
            }


        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }




    /**
     * 获取用户和那些人聊过天
     *
     * @return \think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    public function usersChat()
    {
        $uid = $this->request->post('user_id')?$this->request->post('user_id'):false;
        if($uid){
            /*$data = [];
            foreach($this->yieGet($uid) as $item){

                if($item == 1) return $this->json('未获取数据',Error::$OBTAIN_ERROR);
                if($item){
                    $res = Db::table('chats')->field('id chats_id,chat_description,send_time')
                        ->where('user_cid',$item['grep_id'])
                        ->order('send_time','desc')
                        ->find();
                    if($res){
                        $item = array_merge($item,$res);
                        $data[] = $item;
                    }else{
                        $item['send_time'] = 1;
                        $data[] = $item;
                    }
                }
            }
            $last_names = array_column($data,'send_time'); # 获取二维数组中某个键值
            array_multisort($last_names,SORT_DESC,$data);  # 以对应的键值进行排序*/

            $data = $this->yieGet($uid);
            if($data) return $this->json('获取成功',Error::$SUCCESS,$data);
            else return $this->json('未获取数据',Error::$PARAMETER_ERROR);

        }else{
            return $this->json('参数未获取',Error::$PARAMETER_ERROR);
        }

    }

    /**
     * 拉取所有用户聊过天的用户信息
     *
     * @param $uid
     * @return \Generator
     */
    private function yieGet($uid)
    {

        if($uid) {
            $block = Db::table('pull_block')->field('pb_user_id')->where('user_id',$uid)->select();
            $pull = [];
            if($block){
                foreach($block as $item){
                    $pull[] = $item['pb_user_id'];
                }

            }
            $where = '';
            if($pull) $where = ' u.user_id not in ( '.join(',',$pull).' )';
            #$sql = "select cu.id,u.user_id,u.head_image,u.nickname from chats_user cu LEFT JOIN users u ON cu.friend_id=u.user_id LEFT JOIN chats c ON c.user_cid=cu.id where cu.user_id={$uid} and cu.friend_id NOT IN ( {$where} )";

            $res = Db::table('chats_user')->alias('cu')
                ->field('cu.id grep_id,u.user_id,u.head_image,u.nickname,u.sex,u.u_vip')
                ->join('users u','cu.friend_id=u.user_id','left')
                ->join('users_secretary us','cu.friend_id=us.user_id','left')
                ->where('cu.user_id',$uid)
                ->where($where)
                ->select();
            $exit = [];
            if($res){
                foreach($res as $item){
                    $exit[] = $item['user_id'];
                }
            };
            if($pull) $exit = array_merge($pull,$exit);
            $where = ' cu.user_id not in ( '.join(',',$exit).' )';
            $rows = Db::table('chats_user')->alias('cu')
                ->field('cu.id grep_id,cu.user_id,u.head_image,u.nickname,u.sex,u.u_vip')
                ->join('users_secretary us','cu.friend_id=us.user_id','left')
                ->join('users u','cu.user_id=u.user_id','left')
                ->where('cu.friend_id',$uid)
                ->where($where)
                ->select();
            $data = [];
            if($rows && $res){
                $data = array_merge($rows,$res);
            }else if($rows || $res){
                if($rows) $data = $rows;
                if($res)  $data = $res;
            }else{
                return 1;
            }
            return $data;

        }else{
            return 1;
        }
    }


}