<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class Sex 性别
 * @package app\cigoadmin\library\flags
 */
class Sex extends Enum
{
    const  UNKOWN = 0; //保密
    const  MAN = 1; //男性
    const  WOMEN = 2; //女性
    const __default = self::UNKOWN;
}
