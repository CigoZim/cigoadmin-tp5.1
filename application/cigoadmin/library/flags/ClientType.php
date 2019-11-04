<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class ClientType 终端类型
 * @package app\cigoadmin\library\flags
 */
class ClientType extends Enum
{
    const PC = 0;
    const IPHONE = 1;
    const ANDROID = 2;

    const __default = self::PC;
}
