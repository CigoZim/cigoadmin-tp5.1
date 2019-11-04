<?php

namespace app\cigoadmin\controller\backend;

use app\cigoadmin\library\utils\Common;
use app\cigoadmin\model\EditDemo;
use think\facade\Request;

class Demo extends Editor
{
    public function index()
    {
        $this->assign('args', array(
            'keyword' => isset($_GET['keyword']) ? $_GET['keyword'] : '',
            'status' => !isset($_GET['status']) ? '' : (in_array($_GET['status'], array('0', '1')) ? $_GET['status'] : '0'),
            'startDate' => isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', 0),
            'endDate' => isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d', time() + 24 * 3600),
            'orderBy' => isset($_GET['orderBy']) ? $_GET['orderBy'] : 'create_time',
            'order' => isset($_GET['order']) ? $_GET['order'] : '1'
        ));
        $this->assign('label_title', '演示示例');
        return $this->fetch('cigoadmin@demo/index');
    }

    public function getDataList()
    {
        if (!Request::isPost()) {
            $this->error('请求类型错误！');
        }
        $map = array();
        //判断关键词
        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
            $map[] = ['title', 'like', '%' . $_POST['keyword'] . '%'];
        }
        //判断状态
        if (isset($_POST['status'])) {
            if ($_POST['status'] === '0') {
                $map[] = ['status', '=', 0];
            } else if ($_POST['status'] === '1') {
                $map[] = ['status', '=', 1];
            }
            $map[] = ['status', '>', -1];
        }
        //判断时间段
        if (
            isset($_POST['startDate']) && !empty($_POST['startDate']) &&
            isset($_POST['endDate']) && !empty($_POST['endDate'])
        ) {
            $map[] = ['create_time', 'between', strtotime($_POST['startDate']) . ',' . strtotime($_POST['endDate'])];
        }
        //判断排序
        $orderBy = '';
        if (
            isset($_POST['orderBy']) &&
            in_array(
                $_POST['orderBy'],
                array('id', 'create_time')
            ) && isset($_POST['order'])
        ) {
            $orderBy = $_POST['orderBy'] . ' ' . (($_POST['order'] === '0') ? 'asc' : 'desc');
        }

        $model = new EditDemo();
        $dataList = $model->getList($map, $orderBy);
        if ($dataList) {
            foreach ($dataList['dataList'] as $index => $item) {
                Common::prepareDateToString($dataList['dataList'][$index], 'Y-m-d', 'build_date');
            }
            $this->success('', '', $dataList);
        } else {
            $this->success('', '', array());
        }
    }

    public function setStatus()
    {
        $this->doSetStatus(new EditDemo());
    }

    protected function getDataInfoForTrash($dataInfo, &$dataId, &$dataModel, &$dataTitle, &$typeTip, &$editUrl)
    {

        $dataId = $dataInfo['id'];
        $dataModel = EditDemo::class;
        $dataTitle = $dataInfo['title'];
        $typeTip = 'CigoAdmin演示数据';
        $editUrl = url('edit', ['id' => $dataInfo['id']]);

        return true;
    }

    public function add()
    {
        $res = $this->doAdd(new EditDemo(), '', 'cigoadmin@demo/add');
        if ($res)
            return $res;
        else
            return '';
    }

    protected function beforeAddDisplay($model)
    {
        $this->assign('label_title', '添加演示数据');
        $this->getDisplayConfigData();
    }

    protected function beforeAdd($model, &$data, &$dataExtra)
    {
        !isset($data['checkbox_landscape']) ? $data['checkbox_landscape'] = '' : Common::prepareMultiDataToJson($data, 'checkbox_landscape');
        !isset($data['checkbox_portrait']) ? $data['checkbox_portrait'] = '' : Common::prepareMultiDataToJson($data, 'checkbox_portrait');
        !isset($data['txt_multi']) ? $data['txt_multi'] = '' : Common::prepareMultiDataToJson($data, 'txt_multi');
        !isset($data['img_multi']) ? $data['img_multi'] = '' : Common::prepareMultiDataToJson($data, 'img_multi');
        !isset($data['img_show']) ? $data['img_show'] = '' : Common::prepareMultiDataToJson($data, 'img_show');
        !isset($data['build_date']) ? $data['build_date'] = 0 : Common::prepareDateToTimeStamp($data, 'build_date', true);
        !isset($data['create_time']) ? $data['create_time'] = 0 : Common::prepareDateToTimeStamp($data, 'create_time', true);
    }

    public function edit()
    {
        $res = $this->doEdit(new EditDemo(), '', 'cigoadmin@demo/edit');
        if ($res)
            return $res;
        else
            return '';
    }

    protected function beforeEditDisplay($model, &$data)
    {
        $this->assign('label_title', '修改演示数据');
        $this->getDisplayConfigData();

        //单图
        $data['img_src'] = getUploadFilePath($data['img'], 'path');
        //多图
        Common::prepareMultiDataToArray($data, 'img_multi');
        if ($data['img_multi']) {
            $img_multi = array();
            foreach ($data['img_multi'] as $key => $item) {
                $img_multi[$key] = array(
                    'img-id' => $item,
                    'img-src' => getUploadFilePath($item, 'path')
                );
            }
            $data['img_multi'] = $img_multi;
        } else {
            $data['img_multi'] = array();
        }
        Common::prepareMultiDataToJson($data, 'img_multi');
        //图片橱窗
        if ($data['img_show']) {
            Common::prepareMultiDataToArray($data, 'img_show');
            $img_show = array();
            foreach ($data['img_show'] as $key => $item) {
                $img_show[$key] = array(
                    'img-id' => $item,
                    'img-src' => getUploadFilePath($item, 'path')
                );
            }
            $data['img_show'] = $img_show;
        } else {
            $data['img_show'] = array();
        }
        Common::prepareMultiDataToJson($data, 'img_show');
        Common::prepareDateToString($data, 'Y-m-d', 'build_date');
        //文件
        $data['file_path'] = getUploadFilePath($data['file_id'], 'path');
    }

    protected function beforeEdit($model, &$data, &$dataExtra)
    {

        !isset($data['checkbox_landscape']) ? $data['checkbox_landscape'] = '' : Common::prepareMultiDataToJson($data, 'checkbox_landscape');
        !isset($data['checkbox_portrait']) ? $data['checkbox_portrait'] = '' : Common::prepareMultiDataToJson($data, 'checkbox_portrait');
        !isset($data['txt_multi']) ? $data['txt_multi'] = '' : Common::prepareMultiDataToJson($data, 'txt_multi');
        !isset($data['img_multi']) ? $data['img_multi'] = '' : Common::prepareMultiDataToJson($data, 'img_multi');
        !isset($data['img_show']) ? $data['img_show'] = '' : Common::prepareMultiDataToJson($data, 'img_show');
        !isset($data['build_date']) ? $data['build_date'] = 0 : Common::prepareDateToTimeStamp($data, 'build_date', true);
        !isset($data['create_time']) ? $data['create_time'] = 0 : Common::prepareDateToTimeStamp($data, 'create_time', true);
    }

    public function editValItem()
    {
        $model = new EditDemo();
        //TODO byZim
//        $model->validate(array(
//            array('sort', 'number', '排序必须为数字！', Model::VALUE_VALIDATE, '', Model::MODEL_BOTH)
//        ));
        $this->doEditValItem($model);
    }

    private function getDisplayConfigData()
    {
        $this->assign('radio_checkbox_options_list', json_encode(array(
            array('id' => '0', 'text' => '多选、单选测试1'),
            array('id' => '1', 'text' => '多选、单选测试2'),
            array('id' => '2', 'text' => '多选、单选测试Disabled', 'disabled' => true)
        )));
        $this->assign('label_class_list', json_encode(array(
            array('id' => 'label-default', 'text' => 'Default-样式'),
            array('id' => 'label-primary', 'text' => 'Primary-样式'),
            array('id' => 'label-success', 'text' => 'Success-样式'),
            array('id' => 'label-info', 'text' => 'Info-样式'),
            array('id' => 'label-warning', 'text' => 'Warning-样式'),
            array('id' => 'label-danger', 'text' => 'Danger-样式')
        )));
        $this->assign('img_list_multi_config', json_encode(array(
            'pc-list' => array('label' => 'PC列表图', 'width' => 200, 'height' => 250),
            'app-list' => array('label' => 'App列表图', 'width' => 200, 'height' => 200),
            'phone-list' => array('label' => 'Phone列表图', 'width' => 200, 'height' => 250)
        )));
    }
}

