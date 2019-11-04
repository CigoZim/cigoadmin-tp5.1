<?php

namespace app\cigoadmin\middleware;

use app\cigoadmin\library\flags\GlobalTag;
use Closure;
use think\facade\Cache;
use think\facade\Config;

class SystemConfig
{
    public function handle($request, Closure $next)
    {
        $this->checkSystemConfigFromDb();
        return $next($request);
    }

    /**
     * 检查系统配置（存储于数据库的配置）
     */
    private function checkSystemConfigFromDb()
    {
        //检测配置缓存是否存在
        $config = Cache::get(GlobalTag::DB_SYSTEM_CONFIG_DATA, false);
        //追加到当前配置中，有则覆盖文件配置，并直接缓存
        if ($config && !empty($config)) {
            foreach ($config as $key => $item) {
                Config::set($key, $item);
            }
            return;
        }
        //读取数据库配置项
        $model = new \app\cigoadmin\model\SystemConfig();
        $configDb = $model->getCacheList();
        if ($configDb) {
            $config = [];
            foreach ($configDb as $key => $item) {
                unset($item['concat(config_file,"_",flag)']);
                $config[$item['config_file'] . '.' . $item['flag']] = $item;
                Config::set($item['config_file'] . '.' . $item['flag'], $item);
            }
            //将读到的配置保存在缓存中
            Cache::set(GlobalTag::DB_SYSTEM_CONFIG_DATA, $config);
        }
    }
}
