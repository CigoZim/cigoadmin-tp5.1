<?php

namespace app\cigoadmin\controller\backend;

use app\cigoadmin\library\flags\DataTag;
use app\cigoadmin\library\utils\Common;
use app\cigoadmin\model\AuthGroup;
use app\cigoadmin\model\AuthGroupAccess;
use app\cigoadmin\model\Manager;
use think\Db;
use think\facade\Request;
use think\Model;

class ManagerMg extends Editor
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
        $this->assign('label_title', '管理员维护');
        return $this->fetch('cigoadmin@manager_mg/index');
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
                'username|nickname|email', 'like', '%' . $_POST['keyword'] . '%'
            ];
        }
        //判断状态
        if (isset($_POST['status'])) {
            if ($_POST['status'] === '0') {
                $map[] = ['status', '=', 0];
            } else if ($_POST['status'] === '1') {
                $map[] = ['status', '=', 1];
            } else {
                $map[] = ['status', '>=', 0];
            }
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
        //设置管理当前module下功能节点
        $map[] = ['module', '=', config('cigo.MODULE_LIST')[Request::module()]];

        $model = new Manager();
        $dataList = $model->getList($map, $orderBy);
        if ($dataList) {
            foreach ($dataList['dataList'] as $index => $item) {
                !empty($item['last_log_time'])
                    ? Common::prepareDateToString($dataList['dataList'][$index], 'Y-m-d H:i:s', 'last_log_time')
                    : $dataList['dataList'][$index]['last_log_time'] = '';
            }
            $this->success('', '', $dataList);
        } else {
            $this->success('', '', array());
        }
    }

    public function setStatus()
    {
        $isOneline = Request::param('isonline');

        if (isset($isOneline) && in_array($isOneline, [0, 1])) {
            Db::name('manager')->where('id', Request::param('id'))->update(['is_online' => $isOneline]);
            $this->success('成功');
        }
        $this->doSetStatus(new Manager());
    }

    protected function getDataInfoForTrash($dataInfo, &$dataId, &$dataModel, &$dataTitle, &$typeTip, &$editUrl)
    {
        $dataId = $dataInfo['id'];
        $dataModel = Manager::class;
        $dataTitle = $dataInfo['username'] . ':' . $dataInfo['nickname'];
        $typeTip = '后台管理员';
        $editUrl = url('edit', ['id' => $dataInfo['id']]);

        return true;
    }

    public function add()
    {
        $res = $this->doAdd(new Manager(), '', 'cigoadmin@manager_mg/add');
        if ($res)
            return $res;
        else
            return '';
    }

    protected function beforeAddDisplay($model)
    {
        $this->assign('label_title', '添加管理员');
        $this->getRadioCheckboxOptionsConfig();
        $this->getAuthGroupList();
    }

    protected function beforeAdd($model, &$data, &$dataExtra)
    {
        if (!isset($data['username']) || empty($data['username'])) {
            $this->error('请输入用户名！');
        }
        if (!isset($data['password']) || empty($data['password'])) {
            $this->error('请输入密码！');
        }
        if (!isset($data['nickname']) || empty($data['nickname'])) {
            $this->error('请输入昵称！');
        }
        $data['password'] = Common::encrypt($data['password']);
        $data['module'] = config('cigo.MODULE_LIST')[Request::module()];
        $data['create_time'] = time();
        $data['create_ip'] = Request::ip();
        $data['update_time'] = time();
        $data['last_log_ip'] = '';
        $data['last_log_time'] = 0;

        $this->checkIfUserUnique($model, $data);
    }

    /**
     * 检查管理员唯一性
     * @param Model $model
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function checkIfUserUnique($model, $data)
    {
        $userInfo = $model->where([
            'username' => $data['username'],
            'module' => $data['module']
        ])->find();
        if ($userInfo) {
            $this->error('当前用户名"' . $data['username'] . '"已存在，请更换后重新添加！');
        }
    }

    protected function afterAdd($model, &$data, &$dataExtra, $id)
    {
        //添加绑定
        $this->bindUserWithUserGroup($id, $data);
    }

    function bindUserWithUserGroup($id, $data)
    {
        //1. 删除原绑定数据
        $model = new AuthGroupAccess();
        $model->where('uid', $id)->delete();
        //2. 添加新绑定
        if (isset($data['authGroup']) && !empty($data['authGroup'])) {
            $list = [];
            foreach ($data['authGroup'] as $item) {
                $list[] = ['uid' => $id, 'group_id' => $item];
            }
            $model->saveAll($list);
        }
    }

    public function edit()
    {
        $res = $this->doEdit(new Manager(), '', 'cigoadmin@manager_mg/edit');
        if ($res)
            return $res;
        else
            return '';
    }

    protected function beforeEditDisplay($model, &$data)
    {
        $this->assign('label_title', '修改管理员信息');
        $this->getRadioCheckboxOptionsConfig();
        Common::prepareDateToString($data, 'Y-m-d H:i:s', 'last_log_time');
        $this->getAuthGroupList();
        $this->getBindUserGroupForUser($data);
    }

    private function getBindUserGroupForUser($data)
    {
        $model = new AuthGroupAccess();
        $dataList = $model->where('uid', '=', $data['id'])->column('group_id');
        $this->assign('lastSelectedAuthGroups', !empty($dataList) ? json_encode($dataList) : json_encode([]));
    }

    protected function beforeEdit($model, &$data, &$dataExtra)
    {
        if (!isset($data['nickname']) || empty($data['nickname'])) {
            $this->error('请输入昵称！');
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Common::encrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $data['update_time'] = time();
    }

    protected function afterEdit($model, &$data, &$dataExtra, $id)
    {
        $this->bindUserWithUserGroup($id, $data);
    }

    private function getRadioCheckboxOptionsConfig()
    {
        $this->assign('status_options_list', json_encode(array(
            array('id' => '0', 'text' => '禁止'),
            array('id' => '1', 'text' => '启用')
        )));
    }

    protected function getAuthGroupList()
    {
        $model = new AuthGroup();
        $dataList = $model->getList();
        $treeList = array();
        $model->convertToTree($dataList, $treeList, 0, 'pid', false);
        $this->assign('auth_group_list', json_encode($treeList));
    }

    public function showInfo()
    {
        $this->assign('label_title', '当前登录用户信息');
        return $this->fetch('cigoadmin@manager_mg:showInfo');
    }

    public function modifyNickName()
    {
        if (Request::isPost()) {
            $userApi = $this->getLogLogic();
            $result = $userApi->modifyNickName(Request::request('nickname'), Request::request('password'));

            if (!$result[DataTag::STATUS]) {
                $this->error($result[DataTag::MSG]);
            } else {
                $this->success($result[DataTag::MSG], url('Index/index'));
            }
        } else {
            $this->assign('label_title', '修改昵称');
            return $this->fetch('cigoadmin@manager_mg:modifyNickName');
        }
    }

    public function modifyPwd()
    {
        if (Request::isPost()) {
            $userApi = $this->getLogLogic();
            $result = $userApi->modifyPwd(
                Request::request('oldPwd'),
                Request::request('newPwd'),
                Request::request('repeatPwd')
            );

            if (!$result[DataTag::STATUS]) {
                $this->error($result[DataTag::MSG]);
            } else {
                $this->success($result[DataTag::MSG], url('Index/index'));
            }
        } else {
            $this->assign('label_title', '修改密码');
            return $this->fetch('cigoadmin@manager_mg:modifyPwd');
        }
    }
}
