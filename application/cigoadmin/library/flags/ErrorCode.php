<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class ErrorCode 错误码
 * @package app\cigoadmin\library\flags
 */
class ErrorCode extends Enum
{
    const NOT_EXIST = '1001';//数据不存在
    const ARGS_WRONG = '1002'; //参数错误
    const PHONE_ALREADY_EXIST = '1003'; //手机号码已存在
    const PHONE_SEND_LIMITED = '1004';//发送短信超限，详情见提示消息msg
    const PHONE_SEND_ERROR_UNKOWN = '1005';//发送短信未知错误
    const PHONE_VERIFY_CODE_ERROR = '1006';//验证码验证失败
    const REQUEST_TYPE_WRONG = '1007';//请求类型错误
    const UNKOWN = '1008';//未知错误
    const USER_NOT_EXIST = '1009';//用户不存在
    const PWD_ERROR = '1010'; //密码错误
    const UPLOAD_FILE_TOOBIG = '1011'; //上传图片过大
    const UPLOAD_FILE_MIME_ERROR = '1012';//文件类型错误
    const UPLOAD_FILE_EXT_ERROR = '1013';//文件类型错误
    const UPLOAD_PATH_NO_WRITABLE = '1014';//上传目录不可写
    const UPLOAD_PATH_MKDIR_ERROR = '1015';//上传目录创建失败
    const UPLOAD_TMP_FILE_ERROR = '1016';//上传临时保存文件错误
    const UPLOAD_FILE_SAVE_ERROR = '1017';//保存上传文件错误
    const UPLOAD_DB_SAVE_ERROR = '1018';//保存上传文件数据库错误
    const UPLOAD_SAVE_FILE_EXIST_ERROR = '1019';//保存上传文件重名
    const DECRYPT_ERROR = '1020';//解密失败
    const UNALLOWED_REQUEST = '1021';//非法访问
    const NEED_RELOGIN = '1022';//需要重新登陆
    const NEWPWD_EQ_OLDPWD = '1023';//新旧密码相同
    const NICKNAME_ALREADY_INUSE = '1024';//昵称已被占用
    const OTHER_ERROR = '10025';//其它错误
    const RETRY = '10026';//请重新尝试
    const NO_CHANGE = '10027';//数据无变化
    const DATA_ALREADY_EXIST = '10028';//数据重复
    const DATA_NUM_LIMIT = '10029';//数据数量超限
    const WEIXIN_BIND_TRUE = '10030';//已绑定微信
    const WEIXIN_BIND_FALSE = '10031';//未绑定微信
    const CART_VERIFY_NOT_IDENT = '10040';//未实名认证
    const CART_VERIFY_IN_AUDIT = '10041';//实名认证审核中
    const CART_VERIFY_NOT_ADOPT = '10042';//实名认证审未通过
    const VERSION_THE_LATEST = '1030'; //已是最新版本

    // 砍价相关
    const BARGAINING_EXISTS = '2001';//当前产品有正在进行中的砍价
    const LESS_REDUCE_TIMES_ERROR = '2002';//total reduce times is less than first n reduce times
    const REDUCE_PERCENT_ERROR = '2003';//reduce percent should in (0, 100]
    const LESS_REDUCE_TOTAL_ERROR = '2004';//total reduce should be larger
    const BARGAINING_STOPED = '2005';//砍价已经结束了
    const BARGAINING_OUT_OF_NUM = '2006';//今天的砍价次数已经用完了
    const BARGAINING_IS_ORDERED = '2007';//今天的砍价次数已经用完了

    const GROUPON_CHANNEL_ERROR = '2008';   //发起拼团渠道错误

    const BARGAIN_CHANNEL_ERROR = '2009';   //发起砍价渠道错误

    const __default = self::UNKOWN;
}