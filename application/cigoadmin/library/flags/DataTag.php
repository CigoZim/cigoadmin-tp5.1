<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class DataTag 数据标签
 * @package app\cigoadmin\library\flags
 */
class DataTag extends Enum
{
    const STATUS = 'status';
    const MSG = 'msg';
    const DATA = 'data';
    const ERROR_CODE = 'errorCode';
    const __default = self::STATUS;
}
