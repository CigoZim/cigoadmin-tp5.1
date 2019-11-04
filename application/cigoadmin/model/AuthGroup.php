<?php

namespace app\cigoadmin\model;

use app\cigoadmin\library\traits\Tree;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;
use think\Model;

class AuthGroup extends Model
{
    //TODO byZim
    use Tree;

    /**
     * @summary 获取菜单列表
     * @param array $map
     * @return array|bool|\PDOStatement|string|Collection
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getList($map = [['status', '>', -1]])
    {
        $map[] = ['module', '=', config('cigo.MODULE_LIST')[Request::module()]];//菜单数据划分模块
        $dataList = $this->where($map)
            ->order('pid asc, id asc')
            ->select();
        if ($dataList) {
            return $dataList;
        } else {
            return false;
        }
    }
}
