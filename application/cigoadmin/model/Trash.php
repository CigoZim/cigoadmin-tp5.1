<?php

namespace app\cigoadmin\model;

use app\cigoadmin\library\CigoPage;
use think\Model;

class Trash extends Model
{
    //TODO byZim
//    protected $_auto = array(
//        array('create_time', 'time', self::MODEL_INSERT, 'function')
//    );

    public function getList($map = [])
    {
        $page = new CigoPage($this->where($map)->count(), 10);
        $data_list = $this->where($map)
            ->limit($page->firstRow, $page->listRows)
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
