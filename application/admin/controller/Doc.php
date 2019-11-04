<?php

namespace app\admin\controller;

use app\cigoadmin\controller\backend\Editor;
use app\cigoadmin\library\utils\Common;
use think\facade\Request;
use think\facade\Validate;

class Doc extends Editor
{

    public function index()
    {
        $this->assign('args', array(
            'keyword' => isset($_GET['keyword']) ? $_GET['keyword'] : '',
        ));
        $this->assign('label_title', '文案管理');
        return $this->fetch();
    }

    public function getDataList()
    {
        if (!request()->isPost()) {
            $this->error('请求类型错误！');
        }
        $map = [
            ['status', '>', -1]
        ];
        //判断关键词
        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
            $map[] = ['title', 'like', '%' . $_POST['keyword'] . '%'];
        }
        //判断排序
        $orderBy = 'sort desc,id desc';
        $model = new \app\common\model\Doc();
        $dataList = $model->getList($map, $orderBy);
        if ($dataList) {
            $this->success('', '', $dataList);
        } else {
            $this->success('', '', array());
        }
    }

    public function add()
    {
        if (request()->isPost()) {
            $validate = validate('Doc');
            if (!$validate->check(input('param.'))) {
                $this->error($validate->getError());
            }
        }
        $res = $this->doAdd(new \app\common\model\Doc());
        if ($res) {
            return $res;
        }
    }
    protected function beforeAdd($model, &$data, &$dataExtra)
    {
        !isset($data['detail_imgs']) ? $data['detail_imgs'] = '' : Common::prepareMultiDataToJson($data, 'detail_imgs');
        !isset($data['detail_links']) ? $data['detail_links'] = '' : Common::prepareMultiDataToJson($data, 'detail_links');
    }
    public function edit()
    {
        if (request()->isPost()) {
            $validate = validate('Doc');
            if (!$validate->check(input('param.'))) {
                $this->error($validate->getError());
            }
            !isset($_POST['detail_imgs']) ? $_POST['detail_imgs'] = '' : Common::prepareMultiDataToJson($_POST, 'detail_imgs');
            !isset($_POST['detail_links']) ? $_POST['detail_links'] = '' : Common::prepareMultiDataToJson($_POST, 'detail_links');
            \app\common\model\Doc::update($_POST);
            $this->success('编辑成功');
        }else{
            $model = new \app\common\model\Doc();
            $data = $model->where('id', input('id'))->find();
            Common::prepareMultiDataToArray($data, 'detail_imgs');
            Common::prepareMultiDataToArray($data, 'detail_links');
            if ($data['detail_imgs']) {
                $_detail_imgs = array();
                foreach ($data['detail_imgs'] as $key => $item) {
                    $_detail_imgs[$key] = array(
                        'img-id' => $item,
                        'img-src' => getUploadFileUrl(getUploadFilePath($item, 'path'))
                    );
                }
                $data['detail_imgs'] = $_detail_imgs;
            } else {
                $data['detail_imgs'] = array();
            }
            $data['detail_imgs'] = json_encode($data['detail_imgs']);
            !isset($data['detail_links']) ? $data['detail_links'] = '' : Common::prepareMultiDataToJson($data, 'detail_links');


            //判断有无其他商品规格绑定
            $this->assign('data', $data);
            $this->assign('layerIndex', input('layerIndex') ? input('layerIndex') : 0);
            return $this->fetch();
        }

    }

    public function setStatus()
    {
        $this->doSetStatus(new \app\common\model\Doc());
    }

    public function editValItem()
    {
        $validate = validate('Doc');
        if (!$validate->scene('edit_sort')->check(input('param.'))) {
            $this->error($validate->getError());
        }
        $this->doEditValItem(new \app\common\model\Doc());
    }


    //固定文案模式
    public function fixDoc()
    {
        if (Request::isPost()) {
            if (empty(input('post.id'))) {
                $this->error('参数错误');
            }
            if (empty(input('post.title'))) {
                $this->error('标题不能为空');
            }
            if (empty(input('post.summary'))) {
                $this->error('简介不能为空');
            }
            if (empty(input('post.detail'))) {
                $this->error('详情不能为空');
            }
            $saveData = array(
                'id' => input('post.id'),
                'title' => input('post.title'),
                'type' => 1,
                'summary' => input('post.summary'),
                'detail' => input('post.detail')
            );
            if (db('doc')->where('id', input('post.id'))->find()) {
                db('doc')->where('id', input('post.id'))->update($saveData);
            } else {
                $saveData['create_time'] = time();
                db('doc')->insert($saveData);
            }
            $this->success('操作成功');
        } else {
            $this->assign('label_title', '网站配置');
            //用户协议
            $data['agreement'] = db('doc')->where('id', 1)->find();
            //关于公司
            $data['about'] = db('doc')->where('id', 2)->find();
            //使用手册
            $data['book'] = db('doc')->where('id', 3)->find();
            //服务条款
            $data['term'] = db('doc')->where('id', 4)->find();

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

}