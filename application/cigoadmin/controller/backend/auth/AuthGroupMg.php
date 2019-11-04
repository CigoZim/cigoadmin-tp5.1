<?php

namespace app\cigoadmin\controller\backend\auth;

use app\cigoadmin\controller\backend\Editor;
use app\cigoadmin\model\AuthGroup;
use app\cigoadmin\model\AuthRule;
use think\facade\Request;

class AuthGroupMg extends Editor
{
    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $this->assign('label_title', '用户分组管理');
        return $this->fetch('cigoadmin@auth_group:index');
    }

    public function getDataList()
    {
        $model = new AuthGroup();
        $dataList = $model->getList();
        $treeList = array();
        $model->convertToTree($dataList, $treeList, 0, 'pid', false);
        $this->success('', '', $treeList);
    }

    public function setStatus()
    {
        $this->doSetStatus(new AuthGroup());
    }

    protected function afterSetStatus($key, $status, $dataInfo, $model)
    {
        //TODO 待解决树形结构状态问题
        //by zim 后来考虑以下两点：
        // 1. 配置时不级联操作
        // 2. 检查时级联检查
        // 3. 主要考虑父级被禁止后又启用，级联子孙状态如何保持

        if ($status === '0') {
            //禁止所有下级状态
            $path = $dataInfo['path'] . $dataInfo['id'] . ',';
            $model->isUpdate(true)
                ->update([
                    'status' => 0
                ], [[
                    'path', 'like', $path . '%'
                ]]);
        }
    }

    protected function getDataInfoForTrash($dataInfo, &$dataId, &$dataModel, &$dataTitle, &$typeTip, &$editUrl)
    {
        $dataId = $dataInfo['id'];
        $dataModel = AuthGroup::class;
        $dataTitle = $dataInfo['title'];
        $typeTip = '用户分组';
        $editUrl = url('edit', ['id' => $dataInfo['id']]);

        return true;
    }

    public function add()
    {
        $res = $this->doAdd(new AuthGroup(), '', 'cigoadmin@auth_group:add');
        if ($res)
            return $res;
        else
            return '';
    }

    private function getParentDataList()
    {
        $model = new AuthGroup();
        $dataList = $model->field('id, title text, path, status')
            ->where([
                ['module', '=', config('cigo.MODULE_LIST')[Request::module()]],//菜单数据划分模块
                ['status', '>', -1]
            ])
            ->order('path asc, id asc')
            ->select();
        $this->assign('pList', json_encode($dataList ? $dataList : array()));
    }

    protected function beforeAddDisplay($model)
    {
        $this->assign('label_title', '添加用户分组');
        $this->assign('pid', input('pid', 0));
        $this->getParentDataList();
        $this->getAuthRuleList();
    }

    protected function beforeAddCreateData(&$data, &$dataExtra)
    {
        if (!isset($data['title']) || empty($data['title'])) {
            $this->error('分组名称不能为空！');
        }
        $data['module'] = config('cigo.MODULE_LIST')[Request::module()];//菜单数据划分模块
        if (isset($data['authRules'])) {
            $data['rules'] = implode(',', $data['authRules']);
        } else {
            $data['rules'] = '';
        }
        $data['status'] = 1;
    }

    protected function beforeAdd($model, &$data, &$dataExtra)
    {
        if (isset($data['authRules'])) {
            unset($data);
        }
    }

    public function edit()
    {
        $res = $this->doEdit(new AuthGroup(), '', 'cigoadmin@auth_group:edit');
        if ($res)
            return $res;
        else
            return '';
    }

    protected function beforeEditDisplay($model, &$data)
    {
        $this->assign('label_title', '编辑菜单');
        $this->getParentDataList();
        $this->getAuthRuleList();
        (isset($data['rules']) && !empty($data['rules']))
            ? $data['rules'] = json_encode(explode(',', $data['rules']))
            : $data['rules'] = json_encode(array());
    }

    protected function beforeEditCreateData(&$data, &$dataExtra)
    {
        if (!isset($data['title']) || empty($data['title'])) {
            $this->error('分组名称不能为空！');
        }
        $data['module'] = config('cigo.MODULE_LIST')[Request::module()];//菜单数据划分模块
        isset($data['authRules'])
            ? $data['rules'] = implode(',', $data['authRules'])
            : $data['rules'] = '';
    }

    protected function beforeEdit($model, &$data, &$dataExtra)
    {
        if (isset($data['authRules'])) {
            unset($data);
        }
    }

    protected function getAuthRuleList()
    {
        $model = new AuthRule();
        $dataList = $model->getList();
        $treeList = array();
        $model->convertToTree($dataList, $treeList, 0, 'pid', false);
        $this->assign('auth_rule_list', json_encode($treeList));
    }
}
