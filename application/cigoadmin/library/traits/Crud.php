<?php

namespace app\cigoadmin\library\traits;

use app\cigoadmin\library\utils\Common;
use app\cigoadmin\model\Trash;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\facade\Request;
use think\Model;

/**
 * Trait Edit
 * @package app\admin\library\traits
 * @summary 负责后台管理中的数据标准化的增、删、改操作
 */
trait Crud
{
    /**
     * @param array $dataInfo
     */
    private function moveToTrash($dataInfo = array())
    {
        if (empty($dataInfo)) {
            return;
        }

        if ($this->getDataInfoForTrash($dataInfo, $dataId, $dataModel, $title, $typeTip, $editUrl)) {
            $model = new Trash();
            //TODO byZim
//        $data = $model->create([
//            'data_id' => $dataId,
//            'type' => $type,
//            'title' => $title
//        ]);
//        if (!$data) {
//            return;
//        }
            $model->insert(array(
                'data_id' => $dataId,
                'module' => config('cigo.MODULE_LIST')[Request::module()],
                'model' => $dataModel,
                'title' => $title,
                'type_tip' => $typeTip,
                'edit_url' => $editUrl,
                'create_time' => time()
            ));
        }
    }

    /**
     * @param $dataInfo
     * @param $dataId
     * @param $dataModel
     * @param $dataTitle
     * @param $typeTip
     * @param $editUrl
     * @return bool
     */
    protected function getDataInfoForTrash($dataInfo, &$dataId, &$dataModel, &$dataTitle, &$typeTip, &$editUrl)
    {
        return false;
    }

    private function setStatusTip()
    {
        $id = input('id');
        $status = input('status');
        if (!$id) {
            $this->error('参数错误!');
        }
        switch ($status) {
            case -1 :
                $tips = array(
                    'success' => (input('ctrlTip') ? input('ctrlTip') : '删除') . '成功!',
                    'error' => (input('ctrlTip') ? input('ctrlTip') : '删除') . '失败!'
                );
                break;
            case 0  :
                $tips = array(
                    'success' => (input('ctrlTip') ? input('ctrlTip') : '禁用') . '成功!',
                    'error' => (input('ctrlTip') ? input('ctrlTip') : '禁用') . '失败!'
                );
                break;
            case 1  :
                $tips = array(
                    'success' => (input('ctrlTip') ? input('ctrlTip') : '启用') . '成功!',
                    'error' => (input('ctrlTip') ? input('ctrlTip') : '启用') . '失败!'
                );
                break;
            default :
                $tips = array(
                    'success' => (input('ctrlTip') ? input('ctrlTip') : '操作') . '成功!',
                    'error' => (input('ctrlTip') ? input('ctrlTip') : '操作') . '失败!'
                );
                break;
        }

        return array(
            'id' => $id,
            'status' => $status,
            'tips' => $tips
        );
    }

    /**
     * @param Model $model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws PDOException
     */
    protected function doSetStatus($model)
    {
        //TODO byZim
//        if (!$model->create($_GET)) {
//            $this->error($model->getError());
//        }
        $tipData = $this->setStatusTip();
        $dataInfo = $model->where([['id', '=', $tipData['id']]])->find();
        if (!$dataInfo) {
            $this->error('数据不存在！');
        }

        //修改状态
        $key = input('key') ? input('key') : 'status';
        $res = $model->where([['id', '=', $tipData['id']]])->update([$key => $tipData['status']]);
        if (0 === $res) {//数据无变化
            $this->success('操作成功！');
        } else if (!$res) {
            $this->error($tipData['tips']['error']);
        }

        // 修改成功
        if ($key == 'status' && -1 == $tipData['status']) {
            $this->moveToTrash($dataInfo);
        }
        //修改完毕
        $this->afterSetStatus($key, $tipData['status'], $dataInfo, $model);
        $this->success($tipData['tips']['success']);
    }

    protected function dataNoChange()
    {
        $this->error('数据无变化！');
    }

    /**
     * @param $key
     * @param $status
     * @param $dataInfo
     * @param Model $model
     */
    protected function afterSetStatus($key, $status, $dataInfo, $model)
    {
    }

    /**
     * @param Model $model
     * @param string $jumpTo
     * @param string $tpl
     * @return
     */
    protected function doAdd($model, $jumpTo = '', $tpl = '')
    {
        if (Request::isPost()) {
            $dataExtra = array();
            $data = $_POST;
            $this->beforeAddCreateData($data, $dataExtra);

            //TODO byzim
//            $data = $model->create($_POST);
//            if (!$data) {
//                $this->error($model->getError());
//            }

            $this->beforeAdd($model, $data, $dataExtra);

            try {
                $model->allowField(true)->save($data);
            } catch (\Exception $e) {
                return $this->error('', 0, $e->getMessage());
            }
            $insertId = $model->id;
            $this->afterAdd($model, $data, $dataExtra, $insertId);
            $this->success('添加成功!', (!empty($jumpTo) ? $jumpTo : url('index')));
        } else {
            $this->beforeAddDisplay($model);
            return $this->fetch($tpl);
        }
    }

    /**
     * @param Model $model
     * @param string $jumpTo
     * @param string $tpl
     * @return mixed
     */
    protected function doEdit($model, $jumpTo = '', $tpl = '')
    {
        if (Request::isPost()) {
            $dataExtra = array();
            $data = $_POST;
            $this->beforeEditCreateData($data, $dataExtra);

            //TOOD byZim
//            $data = $model->create($_POST);
//            if (!$data) {
//                $this->error($model->getError());
//            }
            $this->beforeEdit($model, $data, $dataExtra);
//            var_dump($data);exit;
            $res = $model->isUpdate(true)->save($data);
            if (0 === $res) {
                $this->dataNoChange();
            } else if (!$res) {
                $this->error('操作异常！');
                //TODO byZim
//                $this->error($model->getError());
            }
            $this->afterEdit($model, $data, $dataExtra, $_POST['id']);

            //修改成功
            Common::deleteFromTrash($data['id'], config('cigo.MODULE_LIST')[Request::module()]);
            $this->success('修改成功!', (!empty($jumpTo) ? $jumpTo : url('index')));
        } else {
            $data = false;
            $this->getEditData($model, $data);
            if (!$data) {
                $this->error('数据不存在!');
            }
            $this->beforeEditDisplay($model, $data);
            $this->assign('data', $data);
            return $this->fetch($tpl);
        }
    }

    protected function beforeAddDisplay($model)
    {
    }

    protected function beforeAddCreateData(&$data, &$dataExtra)
    {
    }

    protected function beforeAdd($model, &$data, &$dataExtra)
    {
    }

    protected function afterAdd($model, &$data, &$dataExtra, $id)
    {
    }

    protected function getEditData($model, &$data)
    {
        $id = input('id');
        if (!$id) {
            $this->error('参数错误!');
        }

        $data = $model->where([['id', '=', $id]])->find();
    }

    protected function beforeEditDisplay($model, &$data)
    {
    }

    protected function beforeEditCreateData(&$data, &$dataExtra)
    {

    }

    protected function beforeEdit($model, &$data, &$dataExtra)
    {
    }

    protected function afterEdit($model, &$data, &$dataExtra, $id)
    {
    }

    public function doEditValItem($model)
    {
        if (!Request::isPost()) {
            $this->error('请求失败！');
        }

        //TODO byZim
//        $data = $model->create($_POST);
//        if (!$data) {
//            $this->error($model->getError());
//        }
        $data = $_POST;

        $this->doEditValItemBefore($model, $data);
        $res = $model->update($data);
        if (0 === $res) {
            $this->error('数据未做修改！');
        } else if (!$res) {
            $this->error('数据未做修改！');
            //TODO byZim
//            $this->error($model->getError());
        }
        //修改成功
        $this->success('更新成功!');
    }

    protected function doEditValItemBefore($model, $data)
    {
    }
}