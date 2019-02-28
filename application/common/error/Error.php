<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13
 * Time: 20:50
 */

namespace app\common\error;


class Error
{
    #  type_name 需要注意，上船 - 兼职 - 打工 ，这个需要对应到不同的数据表中

    public static $NOT_FOUND                = 22 ;   # 登录时未获取到设备id
    public static $NOT_CACHE_FOUND          = 23 ;   # 缓存中未找到设备id



    # 1 -
    public static $SEC_CREATE_ERROR         = 101;   # 小秘书创建失败
    public static $UPDATE_ERROR             = 102;   # 修改数据失败
    public static $OBTAIN_FAIL              = 103;   # 获取数据失败
    public static $DELETE_COURSE_ERROR      = 104;   # 删除课程数据失败
    public static $TIME_ERROR               = 105;   # 时间参数错误
    public static $INSERT_ERROR             = 106;   # 添加数据失败
    public static $FOLLOWS_EXIST            = 107;   # 已经关注
    public static $NOT_FOUND_DEL            = 108;   # 没有数据可删
    public static $LOGIN_OUT_ERROR          = 109;   # 用户退出登陆
    public static $COURSE_TIME_CONFLICT     = 110;   # 课时时间冲突
    public static $REPORT_LOADING           = 111;   # 正在处理反馈
    public static $RELEASE_OFTEN            = 112;   # 发布过于频繁
    public static $PULL_BLOCK_EXIST         = 113;   # 已经拉黑了
    public static $DELETE_ERROR             = 114;   # 删除数据失败

    # 2 -
    public static $SUCCESS                   = 200;  # 正常/成功
    public static $FULL_NUMBER               = 201;  # 活动人数已满
    public static $END_TIME                  = 202;  # 活动报名截止
    public static $ATTEND                    = 203;  # 已经参加过活动


    # 5 -
    public static $TOKEN_VERIFY_ERROR        = 501;  # token验证失败
    public static $PARAMETER_ERROR           = 502;  # 参数未获取
    public static $REAL_VERIFY_ERROR         = 503;  # 实名认证后才能访问
    public static $OBTAIN_ERROR              = 504;  # 没有获取到数据
    public static $TIMES_ERROR               = 505;  # 时间段错误
    public static $PARAMETER_ILLEGAL         = 506;  # 参数不合法/冲突
    public static $SQUEEZE_OFFLINE           = 511;  # 挤下线


    # 3 -
    public static $EXIST_LOG                 = 301;  # 已有设备登录
    public static $OFTEN_CODE_ERROR          = 302;  # 发送短信验证码过于频繁
    public static $CODE_SYS_ERROR            = 303;  # 验证码发送异常
    public static $OTHER_LOGIN               = 304;  # 在其他设备登录
    public static $UPD_PWD_REG               = 305;  # 修改密码，手机未进行验证
    public static $PWD_USER_ERROR            = 306;  # 请确认用户名和密码是否正确
    public static $CODE_VERIFY_ERROR         = 307;  # 验证码错误
    public static $CODE_INVALID              = 308;  # 验证码无效
    public static $OBTAIN_PARAMETER_ERROR    = 309;  # 参数未获取
    public static $LOGIN_TYPE_EXIST          = 310;  # 账号处于登录状态
    public static $LOGIN_ERROR               = 311;  # 登录出现异常
    public static $ILLEGAL_VISIT             = 330;  # 非法访问

    # 4 -
    public static $REG_ERROR                 = 401;  # 注册失败
    public static $REG_PARAMETER             = 402;  # 注册时未获取到设备id
    public static $REG_OFTEN                 = 403;  # 注册过于频繁
    public static $REG_ONT_FOUND             = 404;  # 用户未注册
    public static $REG_EXIST                 = 405;  # 用户已注册
    public static $REG_PHONE_VERIFY          = 406;  # 用户注册，手机未进行验证
    public static $UPD_PWD_ERROR             = 407;  # 修改密码失败

}

/**
 *  log_type   登录状态 -- 单点登录
 */
