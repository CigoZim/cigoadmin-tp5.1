<?php

namespace app\cigoadmin\controller;

use app\cigoadmin\library\flags\GlobalTag;
use app\cigoadmin\middleware\SystemConfig;
use think\Controller;
use think\facade\Cache;

/**
 * Class CigoAdmin
 * @package app\cigoadmin\controller
 * @summary 西谷后台基类
 */
class CigoAdmin extends Controller
{
    protected $middleware = [
//        TrimArgs::class,
        SystemConfig::class
    ];

    protected function initialize()
    {
        parent::initialize();
    }

    protected function clearSystemConfigCache()
    {
        Cache::set(GlobalTag::DB_SYSTEM_CONFIG_DATA, false);
    }
}
