<?php

namespace app\cigoadmin\library\traits;

/**
 * Trait Tree
 * @package app\cigoadmin\library\traits
 * @summary 树形结构处理类
 */
trait Tree
{
    public function convertToTree(&$srcDataList = array(), &$treeList = array(), $pid = 0, $pidKey = 'pid', $checkGroup = true)
    {
        $group = '';
        foreach ($srcDataList as $key => $item) {
            //判断当前层级
            if (isset($item[$pidKey]) && $item[$pidKey] == $pid) {
                //判断分组
                if ($checkGroup && isset($item['group']) && !empty($item['group']) && $group != $item['group']) {
                    $treeList[] = array(
                        'group_flag' => true,
                        'title' => $item['group']
                    );
                    $group = $item['group'];
                }

                $subList = array();
                $this->convertToTree($srcDataList, $subList, $item['id'], $pidKey, $checkGroup);
                if (!empty($subList)) {
                    $item['subList'] = $subList;
                }

                $treeList[] = $item;
                unset($srcDataList[$key]);
            }
        }
    }

    public function getTopTree(&$srcDataList = array(), &$desDataList = array(), $limitNum = 6)
    {
        foreach ($srcDataList as $key => $item) {
            $desDataList[] = $item;
            if (count($desDataList) >= $limitNum) {
                break;
            }
        }
    }
}

