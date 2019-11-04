<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class AuthResult 权限检查返回值
 * @package app\cigoadmin\library\flags
 */
class AuthResult extends Enum
{
    const   NEED_LOGIN = 0;
    const   AUTH_CHECK_FAIL = 1;
    const   AUTH_CHECK_SUCCESS = 2;
    const   AUTH_CHECK_NO_NEED_LOGIN = 3;
    const   AUTH_CHECK_NO_NEED_AUTH = 4;
    const   AUTH_CHECK_ROUTE_FORBIDDEN = 5;
    const __default = self::NEED_LOGIN;
}
