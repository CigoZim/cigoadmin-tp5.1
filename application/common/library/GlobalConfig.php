<?php

namespace app\common\library;


class GlobalConfig
{
    //终端类型
    const CLIEANT_TYPE_PC = 1; //PC
    const CLIEANT_TYPE_ANDROID = 2;//Android
    const CLIEANT_TYPE_IPHONE = 3;//IOS
    const CLIEANT_TYPE_WX = 4;//微信
    const CLIEANT_TYPE_WEB = 5;//手机网页

    //临时登录密码有效时间
    const LOGIN_USER_BY_ADMIN_TIME = 1800; //半小时1800

    //用户人员类型
    const COMMON_USER = 0;//正常用户
    const ROBOT_USER  = 1;//机器人
    const XI_USER  = 2;//熙小姐
    const TEST_USER  = 3;//测试用户
    const STAFF_USER  = 4;//公司员工

}
