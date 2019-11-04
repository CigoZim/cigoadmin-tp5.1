<?php

namespace app\admin\controller;

use app\cigoadmin\controller\backend\Editor;
use think\facade\Validate;
use think\facade\Request;

class Adv extends Editor
{
    const position = [
        '1' => '首页顶部轮播',
        '2' => '首页滚动消息',
        '3' => '说说顶部轮播',
        '4' => '思TV顶部轮播',
        '5' => '好文顶部轮播',
        '6' => '砍价顶部轮播',
        '7' => '拼团顶部轮播',
        '8' => '积分商城顶部',
    ];

    public function index()
    {
        $this->assign('args', array(
            'keyword' => isset($_GET['keyword']) ? $_GET['keyword'] : '',
            'status' => !isset($_GET['status']) ? '' : (in_array($_GET['status'], array('0', '1')) ? $_GET['status'] : '0'),
            'startDate' => isset($_GET['startDate']) ? $_GET['startDate'] : '2019-01-01',
            'endDate' => isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d', time() + 24 * 3600),
            'orderBy' => isset($_GET['orderBy']) ? $_GET['orderBy'] : 'create_time',
            'order' => isset($_GET['order']) ? $_GET['order'] : '1',
            'position' => isset($_GET['position']) ? $_GET['position'] : '0'
        ));
        $this->assign('label_title', '广告管理');
        $this->assign('position', json_encode(array(
            array('id' => '0', 'text' => '所有'),
            array('id' => '1', 'text' => '首页顶部轮播'),
            array('id' => '2', 'text' => '首页滚动消息'),
            array('id' => '3', 'text' => '说说顶部轮播'),
            array('id' => '4', 'text' => '思TV顶部轮播'),
            array('id' => '5', 'text' => '好文顶部轮播'),
            array('id' => '6', 'text' => '砍价顶部轮播'),
            array('id' => '7', 'text' => '拼团顶部轮播'),
            array('id' => '8', 'text' => '积分商城顶部'),
        )));
        return $this->fetch();
    }

    public function getDataList()
    {
        if (!request()->isPost()) {
            $this->error('请求类型错误！');
        }

        //判断关键词
        if (!empty(input('keyword'))) {
            $map[] = ['title', 'like', '%' . input('keyword') . '%'];
        }
        //判断位置
        if (isset($_POST['position']) && $_POST['position'] > 0) {
            $map[] = ['position', '=', $_POST['position']];
        }

        //判断状态
        $map_query = false;
        if (isset($_POST['status'])) {
            if ($_POST['status'] == '0') {
                $map[] = ['status', '=', '0'];
            } else if ($_POST['status'] == '1') {
                $map[] = ['status', '=', '1'];
            } else {
                $map[] = ['status', '<>', '-1'];
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
                array('create_time', 'sort')
            ) && isset($_POST['order'])
        ) {
            $orderBy = $_POST['orderBy'] . ' ' . (($_POST['order'] === '0') ? 'asc' : 'desc');
        }

        $model = new \app\common\model\Adv();
        $dataList = $model->getList($map, $orderBy, $map_query);
        if ($dataList) {
            foreach ($dataList['dataList'] as $k => &$v) {
                //状态
                if ($v['status'] == 1 && time() >= $v['start_time'] && time() < $v['end_time']) {
                    $v['status_msg'] = '展示';
                } else {
                    $v['status_msg'] = '不展示';
                }

                $v['position'] = self::position[$v['position']];

                $v['start_time'] = date('Y-m-d H:i:s', $v['start_time']);
                $v['end_time'] = date('Y-m-d H:i:s', $v['end_time']);
            }
            $this->success('成功', '', $dataList);
        } else {
            $this->success('成功', '', array());
        }
    }

    public function add()
    {
        if (request()->isPost()) {
            if (strtotime(input('post.start_time')) >= strtotime(input('post.end_time'))) {
                $this->error('开始时间必须小于结束时间');
            }
            $validate = validate('Adv');
            if (!$validate->check(input('param.'))) {
                $this->error($validate->getError());
            }
        }
        $res = $this->doAdd(new \app\common\model\Adv());
        if ($res)
            return $res;
    }

    protected function beforeAdd($model, &$data, &$dataExtra)
    {
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
    }

    public function edit()
    {
        if (request()->isPost()) {
            $validate = validate('Adv');
            if (!$validate->check(input('param.'))) {
                $this->error($validate->getError());
            }
        }
        $res = $this->doEdit(new \app\common\model\Adv());
        if ($res) {
            return $res;
        }
    }

    protected function beforeEditDisplay($model, &$data)
    {
        $data['start_time'] = date('Y-m-d H:i:s', $data['start_time']);
        $data['end_time'] = date('Y-m-d H:i:s', $data['end_time']);
        $data['img-src'] = getUploadFilePath($data['img']);
    }

    protected function beforeEdit($model, &$data, &$dataExtra)
    {
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
    }

    public function setStatus()
    {
        $this->doSetStatus(new \app\common\model\Adv());
    }

    public function editValItem()
    {
        $validate = validate('Adv');
        if (!$validate->scene('edit_sort')->check(input('param.'))) {
            $this->error($validate->getError());
        }
        $this->doEditValItem(new \app\common\model\Adv());
    }

}