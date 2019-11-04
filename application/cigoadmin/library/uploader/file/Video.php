<?php

namespace app\cigoadmin\library\uploader\file;

use app\cigoadmin\library\flags\FileType;
use app\cigoadmin\library\uploader\Uploader;

/**
 * 视频上传接口
 */
class Video extends Uploader
{
    protected function getConfigFileLimit($configs)
    {
        return $configs['fileLimit']['video'];
    }

    protected function getFileType()
    {
        return FileType::VIDEO;
    }
}

