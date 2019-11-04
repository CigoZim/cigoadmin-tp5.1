<?php

namespace app\cigoadmin\model;

use app\cigoadmin\library\CigoPage;
use think\Model;

class Manager extends Model
{
    //TODO byzim
//    /* 用户模型自动验证 */
//    protected $_validate = array(
//        array('username', 'require', '用户名不能为空!', Model::MUST_VALIDATE, 'regex', Model::MODEL_BOTH),
//        array('username', '6,20', '用户名长度为6-20个字符', Model::EXISTS_VALIDATE, 'length'),
//
//        array('nickname', 'require', '昵称不能为空!', Model::MUST_VALIDATE, 'regex', Model::MODEL_BOTH),
//        array('nickname', '1,16', '昵称长度为1-16个字符', Model::EXISTS_VALIDATE, 'length')
//    );

    public function getList($map = [['status', '>', -1]], $orderBy = 'create_time desc')
    {
        $page = new CigoPage($this->where($map)->count(), 10);
        $data_list = $this->where($map)
            ->limit($page->firstRow, $page->listRows)
            ->order($orderBy)
            ->select();
        if ($data_list) {
            return array(
                'showPage' => $page->show(),
                'dataList' => $data_list
            );
        } else {
            return false;
        }
    }
}
