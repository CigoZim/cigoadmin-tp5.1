<?php

namespace app\cigoadmin\controller\backend;

use app\cigoadmin\library\traits\Crud;
use app\cigoadmin\library\traits\Upload;

/**
 * Class Editor
 * @package app\cigoadmin\controller\Backend
 * @summary 主要负责后台数据编辑
 */
class Editor extends SessionAuthCheck
{
    //TODO byZim 划重点
    use Crud;
    use Upload;

    public function initialize()
    {
        parent::initialize();
    }
}
