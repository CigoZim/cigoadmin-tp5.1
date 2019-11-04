<?php

namespace app\cigoadmin\model;

use app\cigoadmin\library\traits\Tree;
use app\cigoadmin\library\utils\Common;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;
use think\Model;

class AuthRule extends Model
{
    //TODO byZim
//    protected $_validate = array(
//        array('title', 'require', '标题不能为空!', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('pid', 'require', '父级编号不能为空!', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('sort', 'number', '排序必须为数字！', self::VALUE_VALIDATE, '', self::MODEL_BOTH)
//    );
//
//    protected $_auto = array(
//        array('status', 0, self::MODEL_INSERT, 'string'),
//        array('status', 1, self::MODEL_BOTH, 'string'),
//    );

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
            ->order('pid asc, group_sort desc, group asc, sort desc, id asc')
            ->select();
        if ($dataList) {
            foreach ($dataList as $key => $item) {
                $dataList[$key]['url'] = empty($item['url']) ? '' : Common::get_menu_url($item['url']);
            }
            return $dataList;
        } else {
            return false;
        }
    }

    /**
     * @param array $map
     * @return array|bool|\PDOStatement|string|Collection
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @summary 获取顶部菜单
     */
    public function getTopList($map = [['status', '>', -1]])
    {
        $map[] = ['module', '=', config('cigo.MODULE_LIST')[Request::module()]];//菜单数据划分模块
        $dataList = $this->where($map)
            ->order('pid asc, opt_rate desc, id asc')
            ->select();
        if ($dataList) {
            foreach ($dataList as $key => $item) {
                $dataList[$key]['url'] = empty($item['url']) ? '' : Common::get_menu_url($item['url']);
            }
            return $dataList;
        } else {
            return false;
        }
    }
}
