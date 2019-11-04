<?php

namespace app\common\model;

use app\cigoadmin\library\CigoPage;
use think\Model;

class Adv extends Model
{
    public function getList($map = [], $orderBy = 'create_time desc',$otherMap = false)
    {
        $page = new CigoPage($this->where($map)->count(), 10);
        $otherThis = $this;
        if($otherMap){
            $otherThis = $this->where($otherMap);
        }
        $data_list = $otherThis
            ->where($map)
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
