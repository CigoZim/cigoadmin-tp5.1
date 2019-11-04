<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/5
 * Time: 10:57
 */

namespace app\admin\controller;

use app\cigoadmin\controller\backend\Editor;
use app\cigoadmin\library\session\ManagerLogic;
use app\common\api\PhoneVestApi;
use app\common\library\GlobalConfig;
use think\Db;
use think\facade\Request;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class User extends Editor
{

    public function index()
    {
        $time = strtotime(date('2018-01-01 00:00:00', strtotime('-1 month')));
        $this->assign('args', array(
            'keyword' => isset($_GET['keyword']) ? $_GET['keyword'] : '',
            'status' => !isset($_GET['status']) ? '' : (in_array($_GET['status'], array('0', '1')) ? $_GET['status'] : '0'),
            'startDate' => isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d H:i:00', $time),
            'endDate' => isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d H:i:59', time()),
            'orderBy' => isset($_GET['orderBy']) ? $_GET['orderBy'] : 'create_time',
            'order' => isset($_GET['order']) ? $_GET['order'] : '1'
        ));
        $this->assign('label_title', '会员列表');
        return $this->fetch();
    }

    /**
     * 弹窗绑定列表页面
     */
    public function alertForBindCoupon()
    {
        $this->assign('args', array(
            'keyword' => isset($_GET['keyword']) ? $_GET['keyword'] : '',
            'status' => !isset($_GET['status']) ? '' : (in_array($_GET['status'], array('0', '1')) ? $_GET['status'] : '0'),
            'startDate' => isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', 0),
            'endDate' => isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d H:i:59', time()),
            'orderBy' => isset($_GET['orderBy']) ? $_GET['orderBy'] : 'create_time',
            'order' => isset($_GET['order']) ? $_GET['order'] : '1'
        ));
        $this->assign('label_title', '会员列表');
        $this->assign('layerIndex', input('layerIndex', 0));
        return $this->fetch();
    }


    //获取数据列表
    public function getDataList()
    {
        if (!Request::isPost()) {
            $this->error('请求类型错误！');
        }
        $map = [];
        //判断关键词
        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
            $map[] = ['u.nickname|u.realname|u.phone', 'like', '%' . $_POST['keyword'] . '%'];
        }
        //判断状态
        if (isset($_POST['status'])) {
            if ($_POST['status'] === '0') {
                $map[] = ['u.status', '=', 0];
            } else if ($_POST['status'] === '1') {
                $map[] = ['u.status', '=', 1];
            }
        }
        //判断时间段
        if (
            isset($_POST['startDate']) && !empty($_POST['startDate']) &&
            isset($_POST['endDate']) && !empty($_POST['endDate'])
        ) {
            $map[] = ['u.create_time', 'between', strtotime($_POST['startDate']) . ',' . strtotime($_POST['endDate'])];
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
            $orderBy = 'u.' . $_POST['orderBy'] . ' ' . (($_POST['order'] === '0') ? 'asc' : 'desc');
        }
        $model = new \app\common\model\User();
        $dataList = $model->getList($map, $orderBy);
        if ($dataList['dataList']) {
            foreach ($dataList['dataList'] as $index => &$item) {
                $item['nickname'] = getUserNickNameShow($item);
                $item['create_time'] = date("Y-m-d H:i:s", $item['create_time']);
            }
            $this->success('', '', $dataList);
        } else {
            $this->success('', '', array());
        }
    }

    public function setStatus()
    {
        $id = input('id');
        $status = input('status');
        if (!$id) {
            $this->error('参数错误!');
        }

        $dataInfo = Db::table('cg_user')->where(['id' => $id])->find();
        if (!$dataInfo) {
            $this->error('数据不存在！');
        }
        $model = new \app\common\model\User();
        $res = $model->setStatus($id, $status);
        if ($res) {
            $this->success('操作成功');
        }
        $this->success('操作失败');
    }

    //查看详情
    public function showDetail()
    {
        //获取用户详情
        $id = input('id');
        $model = new \app\common\model\User();
        //获取是否有归属地信息
        $vest = $model->getUserInfo(array('id' => $id));
        if (empty($vest['phone_province'])) {
            $vestApi = new PhoneVestApi();
            $vestApi->getPhoneVest($vest['phone']);
        }
        $userInfo = $model->getUserInfo(array('id' => $id));
        $parentInfo = $model->getUserInfo(array('id' => $userInfo['first_leader']));
        if ($parentInfo) {
            $userInfo['parent_phone'] = $parentInfo['phone'];
            $userInfo['parent_nickname'] = $parentInfo['nickname'];
            $userInfo['parent_card_id'] = $parentInfo['card_id'];
        }
        $addressWhere['user_id'] = $id;
        $order = "id ASC";
        $cate = $model->getInfoArray($addressWhere, $order);

        if (!empty($cate)) {
            foreach ($cate as $k => &$v) {
                $v['pid'] = $model->getLocationId($v['province']);
                $v['cid'] = $model->getLocationId($v['city']);
                $v['did'] = $model->getLocationId($v['district']);
            }
        }

        $this->assign('cate', $cate);
        $this->assign('userInfo', $userInfo);
        return $this->fetch();
    }

    /**
     * 身份证列表
     */
    public function cardIdList()
    {
        $this->assign('args', array(
            'keyword' => isset($_GET['keyword']) ? $_GET['keyword'] : '',
            'status' => isset($_GET['status']) ? $_GET['status'] : '',
        ));
        $this->assign('label_title', '身份证列表');
        return $this->fetch();
    }

    /**
     * 身份证列表数据
     */
    public function getCardIdListData()
    {
        if (!request()->isPost()) {
            $this->error('请求类型错误');
        }
        $map = array();
        //判断关键词
        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
            $map[] = ['phone', 'like', '%' . $_POST['keyword'] . '%'];
        }
        //判断状态
        if (isset($_POST['status']) && in_array($_POST['status'], [0, 1, 2]) && $_POST['status'] != '') {
            if ($_POST['status'] == 0) {
                $map['card_status'] = 0;
                $map[] = ['verify_card_info', '<>', ''];
            } else if ($_POST['status'] == 1) {
                $map['card_status'] = 1;
            } else {
                $map['card_status'] = 2;
            }
        } else {
            $map[] = ['verify_card_info', '<>', ''];
        }

        $model = new \app\common\model\User();
        $dataList = $model->getCardList($map);
        if ($dataList) {
            $this->success('', '', $dataList);
        } else {
            $this->success('', '', array());
        }
    }

    /**
     * 审核身份证
     */
    public function checkUserCardId()
    {
        if (request()->isPost()) {
            $id = intval($_POST['id']);
            $user_info = Db::table('cg_user')->where(array('id' => $id))->find();
            if (empty($user_info)) {
                $this->error('用户不存在');
            }
            if (!in_array($_POST['card_status'], [1, 2, 3])) {
                $this->error('状态错误');
            }
            $verify_card_info = json_decode($user_info['verify_card_info'], true);
            $saveData = array(
                'realname' => $verify_card_info['real_name'],
                'card_id' => $verify_card_info['card_id'],
                'card_status' => $_POST['card_status']
            );
            if (Db::table('cg_user')->where(array('id' => $id))->update($saveData)) {
                $this->success('审核成功');
            } else {
                $this->error('审核失败');
            }
        } else {
            $id = intval(input('id'));
            $user_info = Db::table('cg_user')->field('id,phone,verify_card_info,card_status')->where(array('id' => $id))->find();
            $verify_card_info = json_decode($user_info['verify_card_info'], true);
            $user_info['real_name'] = $verify_card_info['real_name'];
            $user_info['card_id'] = $verify_card_info['card_id'];
            $refundImgArr = array();
            if (!empty($verify_card_info['card_img'])) {
                foreach (json_decode($verify_card_info['card_img']) as $k => &$v) {
                    array_push($refundImgArr, getUploadFilePath($v, 'path'));
                    $user_info['img_show'] = $refundImgArr;
                }
            }
            $this->assign('data', $user_info);
            return $this->fetch();
        }
    }


    /**
     * 创建临时密码
     */
    public function createTempPwd()
    {
        if (Request::isPost()) {
            $param_data = Request::param();
            $id = intval($param_data['id']);
            $user_info = Db::table('cg_user')->where('id', '=', $id)->find();
            if (empty($user_info)) {
                $this->error('用户不存在');
            }
            if (!isset($param_data['remark']) || empty($param_data['remark'])) {
                $this->error('请填写备注');
            }
            $temp_password = create_user_temp_password();
            $temp_valid_time = time() + GlobalConfig::LOGIN_USER_BY_ADMIN_TIME;
            $admin_id = session(Request::module() . ManagerLogic::DATA_TAG_USERINFO)[ManagerLogic::DATA_TAG_ID];
            $userSaveData = array(
                'temp_password' => encryptUserPwd($temp_password),
                'temp_valid_time' => $temp_valid_time
            );
            $adminLoginLogData = array(
                'admin_id' => $admin_id,
                'user_id' => $id,
                'phone' => $user_info['phone'],
                'remark' => $_POST['remark'],
                'create_time' => time()
            );
            Db::startTrans();
            try {
                Db::table('cg_user')->where('id', '=', $id)->update($userSaveData);
                Db::name('user_admin_login_log')->insert($adminLoginLogData);
                Db::commit();

            } catch (\Exception $e) {
                Db::rollback();
                $this->error('生成临时密码失败');
            }
            $this->success('', '', array('temp_password' => $temp_password, 'temp_valid_time' => date('Y-m-d H:i:s', $temp_valid_time)));
        } else {
            $id = Request::param('id/d');
            $user_info = Db::table('cg_user')->where('id', '=', $id)->find();
            $this->assign('data', $user_info);
            return $this->fetch();
        }
    }

    /**
     * 手机解绑微信
     */
    public function clearWxInfo()
    {
        if (Request::isPost()) {
            $uid = Request::param('id');
            $data = array(
                'unionid' => '',
                'openid_wx' => '',
                'openid_app' => '',
                'token' => ''
            );
            $res = Db::table('cg_user')->where('id = ' . $uid)->update($data);
            if ($res) {
                return ['code' => 1, 'msg' => '解绑成功'];
            } else {
                return ['code' => 0, 'msg' => '解绑失败'];
            }
        }
        $this->error('请求类型错误');
    }

    public function userSummary()
    {
        $model = new \app\common\model\User();
        $lastActiveClient = $model->lastActiveClient();
        $registClientType = $model->registClientType();
        $this->assign('lastActiveClient', $lastActiveClient);
        $this->assign('registClientType', $registClientType);
        return $this->fetch();
    }

    public function pieShow()
    {
        $type = Request::param('type/d');
        $model = new \app\common\model\User();
        if ($type == 1) {
            $data = $model->registClientType();
        } elseif ($type == 2) {
            $data = $model->lastActiveClient();
        }

        $this->assign('data', $data);
        return $this->fetch();
    }

    public function exportAdmin()
    {
        $type = Request::param('type/d');
        $model = new \app\common\model\User();
        if ($type == 1) {
            $data = $model->registClientType();
            $data['title'] = "注册终端统计";
        } elseif ($type == 2) {
            $data = $model->lastActiveClient();
            $data['title'] = "最后活跃终端统计";
        }

        $newExcel = new Spreadsheet();  //创建一个新的excel文档
        $objSheet = $newExcel->getActiveSheet();  //获取当前操作sheet的对象
        $objSheet->setTitle('管理员表');  //设置当前sheet的标题

        //设置宽度为true,不然太窄了
        $newExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);

        //设置第一栏的标题
        $objSheet->setCellValue('A1', '')
            ->setCellValue('B1', 'PC')
            ->setCellValue('C1', 'Android')
            ->setCellValue('D1', 'IOS')
            ->setCellValue('E1', '微信')
            ->setCellValue('F1', '手机网页')
            ->setCellValue('G1', '未知')
            ->setCellValue('H1', '总计');

        //第二行起，每一行的值,setCellValueExplicit是用来导出文本格式的。
        //->setCellValueExplicit('C' . $k, $val['admin_password']PHPExcel_Cell_DataType::TYPE_STRING),可以用来导出数字不变格式

        $objSheet->setCellValue('A2', "人数")
            ->setCellValue('B2', $data['pc'])
            ->setCellValue('C2', $data['Android'])
            ->setCellValue('D2', $data['IOS'])
            ->setCellValue('E2', $data['wx'])
            ->setCellValue('F2', $data['webpage'])
            ->setCellValue('G2', $data['other'])
            ->setCellValue('H2', $data['all']);
        $objSheet->setCellValue('A3', "占比")
            ->setCellValue('B3', $data['pc_rate'] . '%')
            ->setCellValue('C3', $data['Android_rate'] . '%')
            ->setCellValue('D3', $data['IOS_rate'] . '%')
            ->setCellValue('E3', $data['wx_rate'] . '%')
            ->setCellValue('F3', $data['webpage_rate'] . '%')
            ->setCellValue('G3', $data['other_rate'] . '%')
            ->setCellValue('H3', $data['all_rate'] . '%');

        $this->downloadExcel($newExcel, $data['title'], 'Xls');
    }

    //公共文件，用来传入xls并下载
    function downloadExcel($newExcel, $filename, $format)
    {
        // $format只能为 Xlsx 或 Xls
        if ($format == 'Xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } elseif ($format == 'Xls') {
            header('Content-Type: application/vnd.ms-excel');
        }

        header("Content-Disposition: attachment;filename="
            . $filename . date('Y-m-d') . '.' . strtolower($format));
        header('Cache-Control: max-age=0');
        $objWriter = IOFactory::createWriter($newExcel, $format);

        $objWriter->save('php://output');

        //通过php保存在本地的时候需要用到
        //$objWriter->save($dir.'/demo.xlsx');

        //以下为需要用到IE时候设置
        // If you're serving to IE 9, then the following may be needed
        //header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        //header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        //header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        //header('Pragma: public'); // HTTP/1.0
        exit;
    }


}