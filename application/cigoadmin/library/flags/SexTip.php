<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class SexTip 性别文字
 * @package app\cigoadmin\library\flags
 */
class SexTip extends Enum
{
    const  UNKOWN = '保密'; //保密
    const  MAN = '男性'; //男性
    const  WOMEN = '女性'; //女性
    const __default = self::UNKOWN;
}
