<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class VerifyCodeFlag 验证码模块标识
 * @package app\cigoadmin\library\flags
 */
class VerifyCodeFlag extends Enum
{
    const ADMIN = 'admin';
    const INDEX = 'index';
    const __default = self::INDEX;
}
