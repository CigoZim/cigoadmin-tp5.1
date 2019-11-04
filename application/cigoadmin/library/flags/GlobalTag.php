<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class GlobalTag 数据标签
 * @package app\cigoadmin\library\flags
 */
class GlobalTag extends Enum
{
    const UNKOWN = 'unkown';

    const DB_SYSTEM_CONFIG_DATA = 'DB_SYSTEM_CONFIG_DATA';
    const DB_SYSTEM_CONFIG_RSA_PUBLIC_KEY = 'RSA_PUBLIC_KEY';
    const DB_SYSTEM_CONFIG_RSA_PRIVATE_KEY = 'RSA_PRIVATE_KEY';


    const RSA_PRIVATE_KEY = 'rsaPrivateKey';
    const RSA_PUBLIC_KEY = 'rsaPublicKey';

    const __default = self::UNKOWN;
}
