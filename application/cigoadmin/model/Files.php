<?php

namespace app\cigoadmin\model;

use think\Model;

class Files extends Model
{

    protected $connection = 'db_v1';

    //    protected $table = 'silversea_test.cg_files';
    protected $auto = [];

    protected $insert = ['status' => 1, 'create_ip' => 0];

    protected $update = [];

    protected $append = ['path_url', 'thumb_small_url', 'thumb_middle_url'];

    public function getPathUrlAttr()
    {
        return getUploadFileUrl(trim($this->getAttr('path'), '.'));
    }

    public function getThumbSmallUrlAttr()
    {
        return getUploadFileUrl(trim($this->getAttr('thumb_small'), '.'));
    }

    public function getThumbMiddleUrlAttr()
    {
        return getUploadFileUrl(trim($this->getAttr('thumb_middle'), ''));
    }

}
