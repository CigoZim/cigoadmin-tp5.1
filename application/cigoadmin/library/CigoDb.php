<?php

namespace app\cigoadmin\library;

use think\facade\Request;
use think\Model;

class CigoDb extends Model
{
    public function table($db1Table, $db2Table)
    {
        return parent::table($this->convertDbTable($db1Table, $db2Table));
    }


    public function leftJoin($db1Table, $db2Table, $condition = null, $bind = [])
    {
        halt($db1Table);



        return parent::leftJoin($this->convertDbTable($db1Table, $db2Table), $condition, $bind);
    }

    private function convertDbTable($db1Table, $db2Table)
    {
        $db_name = '';

        if (!empty($db1Table)) {
            $db1_tables = explode(',', $db1Table);
            foreach ($db1_tables as $db1_table) {
                $db_name .= trim($db1_table).',';
            }
        }
        if (!empty($db2Table)) {
            $db2_tables = explode(',', $db2Table);
            foreach ($db2_tables as $db2_table) {
                $db_name .= trim($db2_table).',';
            }
        }
        return substr($db_name, 0, -1);
    }
}

