<?php

namespace app\cigoadmin\controller\backend;

use app\cigoadmin\library\utils\Common;
use app\cigoadmin\model\Trash;
use think\facade\Request;
use think\Model;

class TrashMg extends Editor
{
    public function index()
    {
        $this->assign('args', array(
            'keyword' => isset($_GET['keyword']) ? $_GET['keyword'] : '',
            'startDate' => isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', 0),
            'endDate' => isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d', time() + 24 * 3600)
        ));

        $this->assign('label_title', '数据回收站');
        return $this->fetch('cigoadmin@trash_mg/index');
    }

    public function getDataList()
    {
        if (!Request::isPost()) {
            $this->error('请求类型错误！');
        }
        $map = array();
        //判断关键词
        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
            $map[] = [
                'title|type_tip', 'like', '%' . $_POST['keyword'] . '%'
            ];
        }
        //判断时间段
        if (
            isset($_POST['startDate']) && !empty($_POST['startDate']) &&
            isset($_POST['endDate']) && !empty($_POST['endDate'])
        ) {
            $map[] = ['create_time', 'between', strtotime($_POST['startDate']) . ',' . strtotime($_POST['endDate'])];
        }
        //设置管理当前module下功能节点
        $map[] = ['module', '=', config('cigo.MODULE_LIST')[Request::module()]];

        $model = new Trash();
        $dataList = $model->getList($map);
        if ($dataList) {
            foreach ($dataList['dataList'] as $index => $item) {
                $dataList['dataList'][$index]['module_tip'] = config('cigo.MODULE_LIST_TIP')[$item['module']];
            }
            $this->success('', '', $dataList);
        } else {
            $this->success('', '', array());
        }
    }

    public function revertData()
    {
        $dataId = input('dataId');
        $module = input('module');

        if (empty($dataId) || empty($module)) {
            $this->error('参数错误!');
        }

        $trashModel = new Trash();
        $trashData = $trashModel->where([
            ['data_id', '=', $dataId],
            ['module', '=', $module],
        ])->find();
        if (empty($trashData)) {
            $this->error('数据不在回收站中!');
        }

        $dataModel = $this->getDataModel($trashData['model']);
        $res = $dataModel
            ->save([
                'status' => 0
            ], ['id' => $dataId]);
        if ($res) {
            Common::deleteFromTrash($dataId, $module);
            $this->success('恢复成功!');
        } else {
            $this->error('恢复失败!');
        }
    }

    /**
     * @param $modelClass
     * @return Model
     */
    private function getDataModel($modelClass)
    {
        return new $modelClass;
    }

    public function removeData()
    {
        $dataId = input('dataId');
        $module = input('module');

        if (empty($dataId) || empty($module)) {
            $this->error('参数错误!');
        }

        $trashModel = new Trash();
        $trashData = $trashModel->where([
            ['data_id', '=', $dataId],
            ['module', '=', $module],
        ])->find();
        if (empty($trashData)) {
            $this->error('数据不在回收站中!');
        }

        $dataModel = $this->getDataModel($trashData['model']);
        $res = $dataModel
            ->save([
                'status' => -2
            ], ['id' => $dataId]);

        if ($res) {
            Common::deleteFromTrash($dataId, $module);
            $this->success('删除成功!');
        } else {
            $this->error('删除失败!');
        }
    }
}
