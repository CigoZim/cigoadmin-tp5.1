<?php

namespace app\cigoadmin\library\flags;

use app\cigoadmin\library\Enum;

/**
 * Class FileType 上传文件类型
 * @package app\cigoadmin\library\flags
 */
class FileType extends Enum
{
    const FILE = 0;
    const IMG = 1;
    const VIDEO = 2;

    const __default = self::FILE;
}
