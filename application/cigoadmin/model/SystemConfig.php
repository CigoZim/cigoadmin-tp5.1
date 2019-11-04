<?php

namespace app\cigoadmin\model;

use think\Model;

class SystemConfig extends Model
{
    public function getCacheList()
    {
        $dataList = $this->where(['cache_flag' => 1])
            ->order(['sort' => 'desc', 'create_time' => 'desc'])
            ->column('concat(config_file, "_", flag), config_file, flag, config, value');
        return $dataList;
    }
}
