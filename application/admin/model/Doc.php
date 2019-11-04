<?php

namespace app\admin\model;

use app\cigoadmin\library\CigoPage;
use think\Model;

class Doc extends Model
{

    public function getList($map = [], $orderBy = 'status desc,create_time desc')
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
