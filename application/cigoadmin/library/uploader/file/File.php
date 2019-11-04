<?php

namespace app\cigoadmin\library\uploader\file;

use app\cigoadmin\library\flags\FileType;
use app\cigoadmin\library\uploader\Uploader;

/**
 * 文件上传接口
 */
class File extends Uploader
{

    protected function getConfigFileLimit($configs)
    {
        return $configs['fileLimit']['file'];
    }

    protected function getFileType()
    {
        return FileType::FILE;
    }
}

