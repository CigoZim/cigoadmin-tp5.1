<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class EncryptType 加密类型
 * @package app\cigoadmin\library\flags
 */
class EncryptType extends Enum
{
    const RSA_KEY_TYPE_OPEN = 'open';
    const RSA_KEY_TYPE_CLIENT = 'client';

    const __default = self::RSA_KEY_TYPE_OPEN;
}
