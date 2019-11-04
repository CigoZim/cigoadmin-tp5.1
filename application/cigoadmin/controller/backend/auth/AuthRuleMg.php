<?php

namespace app\cigoadmin\controller\backend\auth;

use app\cigoadmin\controller\backend\Editor;
use app\cigoadmin\model\AuthRule;
use think\facade\Request;
use think\facade\Session;

class AuthRuleMg extends Editor
{
    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $this->assign('label_title', '菜单、功能节点');
        return $this->fetch('cigoadmin@auth_rule:index');
    }

    public function getDataList()
    {
        $model = new AuthRule();
        $dataList = $model->getList();
        $treeList = array();
        $model->convertToTree($dataList, $treeList, 0, 'pid', false);
        $this->success('', '', $treeList);
    }

    public function setStatus()
    {
        $this->doSetStatus(new AuthRule());
    }

    protected function afterSetStatus($key, $status, $dataInfo, $model)
    {
        //TODO 待解决树形结构状态问题
    }

    protected function getDataInfoForTrash($dataInfo, &$dataId, &$dataModel, &$dataTitle, &$typeTip, &$editUrl)
    {
        $dataId = $dataInfo['id'];
        $dataModel = AuthRule::class;
        $dataTitle = $dataInfo['title'];
        $typeTip = '系统菜单/权限节点';
        $editUrl = url('edit', ['id' => $dataInfo['id']]);

        return true;
    }

    public function add()
    {
        $res = $this->doAdd(new AuthRule(), '', 'cigoadmin@auth_rule:add');
        if ($res)
            return $res;
        else
            return '';
    }

    private function getParentDataList()
    {
        $model = new AuthRule();
        $dataList = $model->field('id, title text, path path')
            ->where([
                ['module', '=', config('cigo.MODULE_LIST')[Request::module()]],//菜单数据划分模块
                ['status', '>', -1]
            ])
            ->order('path asc, sort desc, id asc')
            ->select();
        $this->assign('pList', json_encode($dataList ? $dataList : array()));
    }

    protected function beforeAddDisplay($model)
    {
        $this->assign('label_title', '添加菜单');
        $this->assign('pid', Request::route('pid', 0));
        $this->assign('target_list', json_encode($this->getLinkTargetList()));
        $this->assign('label_class_list', json_encode($this->getLabelClassList()));
        $this->getParentDataList();
    }

    protected function beforeAddCreateData(&$data, &$dataExtra)
    {
        isset($data['menu_flag'])
            ? $data['menu_flag'] = 1
            : $data['menu_flag'] = 0;
        $data['module'] = config('cigo.MODULE_LIST')[Request::module()];//菜单数据划分模块
    }

    public function edit()
    {
        $res = $this->doEdit(new AuthRule(), '', 'cigoadmin@auth_rule:edit');
        if ($res)
            return $res;
        else
            return '';
    }

    protected function beforeEditDisplay($model, &$data)
    {
        $this->assign('label_title', '编辑菜单');
        $this->assign('target_list', json_encode($this->getLinkTargetList()));
        $this->assign('label_class_list', json_encode($this->getLabelClassList()));
        $this->getParentDataList();
    }

    protected function beforeEditCreateData(&$data, &$dataExtra)
    {
        isset($data['menu_flag'])
            ? $data['menu_flag'] = 1
            : $data['menu_flag'] = 0;
        $data['module'] = config('cigo.MODULE_LIST')[Request::module()];//菜单数据划分模块
    }

    public function editValItem()
    {
        $model = new AuthRule();
        //TODO 验证sort必须为数字
        $this->doEditValItem($model);
    }


    private function getLinkTargetList()
    {
        return array(
            array('id' => 'page_content', 'text' => '右侧内容窗口'),
            array('id' => '_blank', 'text' => '新窗口打开')
        );
    }

    private function getLabelClassList()
    {
        return array(
            array('id' => 'label-default', 'text' => 'Default-样式'),
            array('id' => 'label-primary', 'text' => 'Primary-样式'),
            array('id' => 'label-success', 'text' => 'Success-样式'),
            array('id' => 'label-info', 'text' => 'Info-样式'),
            array('id' => 'label-warning', 'text' => 'Warning-样式'),
            array('id' => 'label-danger', 'text' => 'Danger-样式')
        );
    }
}
