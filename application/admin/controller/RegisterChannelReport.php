<?php

namespace app\admin\controller;

use app\cigoadmin\controller\backend\Editor;
use app\common\library\GlobalConfig;
use think\Db;

class RegisterChannelReport extends Editor
{
    /**
     * 注册渠道报表
     */
    public function index()
    {
        $this->assign('memberType', array(
            GlobalConfig::COMMON_USER =>'正常用户',
            GlobalConfig::ROBOT_USER  => '机器人',
            GlobalConfig::XI_USER  => '熙小姐',
            GlobalConfig::TEST_USER  => '测试用户',
            GlobalConfig::STAFF_USER  => '公司员工'
        ));
        return $this->fetch();
    }

    public function getRegisterInfoList()
    {
        $member_type = !isset($_POST['member_type']) || $_POST['member_type']===[] ? "" : $_POST['member_type'];
        if (!empty($member_type)) {
            $member_type = implode(',',$member_type);
        }
        $orderGroup = !isset($_POST['orderGroup']) || empty($_POST['orderGroup']) ? 'day' : $_POST['orderGroup'];
        switch ($orderGroup) {
            case 'week':
                $orderGroupSql = '%Y第%u周';
                break;
            case 'month':
                $orderGroupSql = '%Y年%m月';
                break;
            case 'year':
                $orderGroupSql = '%Y年';
                break;
            case 'day':
            default:
                $orderGroupSql = '%Y-%m-%d';
                break;
        }
        $dateRange = !isset($_POST['dateRange']) || empty($_POST['dateRange']) ? false : $_POST['dateRange'];
        if (!isset($dateRange['startDate']) || empty($dateRange['startDate'])) {
            $dateRange['startDate'] = '2019/09/28';
            $dateRange['endDate'] = date('Y/m/d H:i:s', time());
        }
        $dataList = array();
        $member = $this->registerChannel($orderGroupSql, $dateRange,$member_type);//注册渠道
        if (!empty($member)) {
            foreach ($member as $k => $item) {
                $dataList[$k]['rowFlag'] = $item['rowFlag'];
                $dataList[$k]['allRegister'] = $item['allRegister'];
                $dataList[$k]['inviteMember'] = !empty($item['inviteMember']) ? $item['inviteMember'] . "[" . $item['inviteMemberRate'] . ']' : 0;
                $dataList[$k]['otherMember'] = !empty($item['otherMember']) ? $item['otherMember'] . "[" . $item['otherMemberRate'] . ']' : 0;
            }
        }
        $dataList = !empty($dataList) ? array_values($dataList) : [];
        $this->apiReturn(0, $dataList);
    }

    protected function apiReturn(
        $code = 0,
        $data = array(),
        $msg = '',
        $errorCode = '',
        $header = [],
        $type = 'json'
    )
    {
        if (!empty($errorCode)) {
            $data['errorCode'] = $errorCode;
        }
        $this->result($data, $code, $msg, 'json', $header);
    }

    public function registerChannel($orderGroupSql, $dateRange,$member_type)
    {
        $allRegister = $this->getRegisterChannel($orderGroupSql, $dateRange, $member_type, 0);
        $inviteMember = $this->getRegisterChannel($orderGroupSql, $dateRange, $member_type, 1);
        $otherMember = $this->getRegisterChannel($orderGroupSql, $dateRange, $member_type, 2);
        $newRegister = array();
        if (!empty($allRegister)) {
            foreach ($allRegister as $k => $v) {
                $newRegister[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $newRegister[$v['rowFlag']]['allRegister'] = $v['member'];
            }
        }
        if (!empty($inviteMember)) {
            foreach ($inviteMember as $k => $v) {
                $newRegister[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $newRegister[$v['rowFlag']]['inviteMember'] = $v['member'];
            }
        }
        if (!empty($otherMember)) {
            foreach ($otherMember as $k => $v) {
                $newRegister[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $newRegister[$v['rowFlag']]['otherMember'] = $v['member'];
            }
        }
        if (!empty($newRegister)) {
            foreach ($newRegister as $k => $v) {
                if (!isset($v['inviteMember'])) {
                    $newRegister[$k]['inviteMember'] = 0;
                }
                if (!isset($v['otherMember'])) {
                    $newRegister[$k]['otherMember'] = 0;
                }
                $newRegister[$k]['inviteMemberRate'] = round2point($newRegister[$k]['inviteMember'] / $v['allRegister'] * 100) . '%';
                $newRegister[$k]['otherMemberRate'] = round2point($newRegister[$k]['otherMember'] / $v['allRegister'] * 100) . '%';
            }
        }
        return $newRegister;
    }

    public function getRegisterChannel($orderGroupSql, $dateRange,$member_type, $first_leader, $from = 0)
    {
        $data_list = Db::query(
            "SELECT " .
            "   FROM_UNIXTIME(create_time, '" . $orderGroupSql . "') as rowFlag, " .
            "   count(*) as member " .
            "FROM cg_user " .
            "WHERE status = 1 " .
            (
            $first_leader === 2
                ? "and first_leader = 0 "
                : ($first_leader === 1
                ? "and first_leader <> 0 "
                : "")
            ) .
            (
            $from === 2
                ? "and regist_client_type = 2 "
                : (
            $from === 3
                ? "and regist_client_type = 3 "
                : (
            $from === 4
                ? "and regist_client_type = 4 "
                : (
            $from === 5
                ? "and regist_client_type in (1,5) "
                : ""
            )
            )
            )
            ) .
            (
            $member_type
                ? " and user_type in (". $member_type .")"
                : ""
            ) .
            (
            $dateRange
                ? " and create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            " GROUP BY rowFlag"
        );
        return $data_list;
    }

    /**
     * 用户终端报表
     */
    public function memberClient()
    {
        $this->assign('memberType', array(
            GlobalConfig::COMMON_USER =>'正常用户',
            GlobalConfig::ROBOT_USER  => '机器人',
            GlobalConfig::XI_USER  => '熙小姐',
            GlobalConfig::TEST_USER  => '测试用户',
            GlobalConfig::STAFF_USER  => '公司员工'
        ));
        return $this->fetch();
    }

    public function getClientInfoList()
    {
        $member_type = !isset($_POST['member_type']) || $_POST['member_type']===[] ? "" : $_POST['member_type'];
        if (!empty($member_type)) {
            $member_type = implode(',',$member_type);
        }
        $orderGroup = !isset($_POST['orderGroup']) || empty($_POST['orderGroup']) ? 'day' : $_POST['orderGroup'];
        switch ($orderGroup) {
            case 'week':
                $orderGroupSql = '%Y第%u周';
                break;
            case 'month':
                $orderGroupSql = '%Y年%m月';
                break;
            case 'year':
                $orderGroupSql = '%Y年';
                break;
            case 'day':
            default:
                $orderGroupSql = '%Y-%m-%d';
                break;
        }
        $dateRange = !isset($_POST['dateRange']) || empty($_POST['dateRange']) ? false : $_POST['dateRange'];
        if (!isset($dateRange['startDate']) || empty($dateRange['startDate'])) {
            $dateRange['startDate'] = '2019/09/28';
            $dateRange['endDate'] = date('Y/m/d H:i:s', time());
        }
        $dataList = array();
        $MemberClient = $this->getMemberClient($orderGroupSql, $dateRange,$member_type);//用户终端
        if ($MemberClient) {
            foreach ($MemberClient as $k => $item) {
                $dataList[$k]['rowFlag'] = $item['rowFlag'];
                $dataList[$k]['allMember'] = $item['allMember'];
                $dataList[$k]['androidMember'] = $item['androidMember'];
                $dataList[$k]['iosMember'] = $item['iosMember'];
                $dataList[$k]['wxMember'] = $item['wxMember'];
                $dataList[$k]['androidRegister'] = !empty($item['androidRegister']) ? $item['androidRegister'] . "[" . $item['androidRegisterRate'] . ']' : 0;
                $dataList[$k]['iosRegister'] = !empty($item['iosRegister']) ? $item['iosRegister'] . "[" . $item['iosRegisterRate'] . ']' : 0;
                $dataList[$k]['wxRegister'] = !empty($item['wxRegister']) ? $item['wxRegister'] . "[" . $item['wxRegisterRate'] . ']' : 0;
                $dataList[$k]['otherRegister'] = !empty($item['otherRegister']) ? $item['otherRegister'] . "[" . $item['otherRegisterRate'] . ']' : 0;
                $dataList[$k]['downloadMember'] = !empty($item['downloadMember']) ? $item['downloadMember'] . "[" . $item['downloadMemberRate'] . ']' : 0;
                $dataList[$k]['undownloadMember'] = !empty($item['undownloadMember']) ? $item['undownloadMember'] . "[" . $item['undownloadMemberRate'] . ']' : 0;
            }
        }
        $dataList = !empty($dataList) ? array_values($dataList) : [];
        $this->apiReturn(0, $dataList);
    }

    public function getMemberClient($orderGroupSql, $dateRange,$member_type)
    {
        $allMember = $this->getRegisterChannel($orderGroupSql, $dateRange, $member_type,0);//总注册人数
        $androidRegister = $this->getRegisterChannel($orderGroupSql, $dateRange, $member_type,0, 2);//安卓注册用户
        $iosRegister = $this->getRegisterChannel($orderGroupSql, $dateRange, $member_type,0, 3);//ios注册用户
        $wxRegister = $this->getRegisterChannel($orderGroupSql, $dateRange, $member_type,0, 4);//微信注册用户
        $otherRegister = $this->getRegisterChannel($orderGroupSql, $dateRange, $member_type,0, 5);//其他注册用户
        $androidMember = $this->getMemberClientInfo($orderGroupSql, $dateRange, 2,$member_type);//2-Android登录
        $iosMember = $this->getMemberClientInfo($orderGroupSql, $dateRange, 3,$member_type);//3-IOS登录
        $wxMember = $this->getMemberClientInfo($orderGroupSql, $dateRange, 4,$member_type);//4-微信登录
        $downloadMember = $this->getDownloadApp($orderGroupSql, $dateRange,$member_type);//注册转下载
        $MemberClient = array();
        if (!empty($allMember)) {
            foreach ($allMember as $k => $v) {
                $MemberClient[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $MemberClient[$v['rowFlag']]['allMember'] = $v['member'];
            }
        }
        if (!empty($androidRegister)) {
            foreach ($androidRegister as $k => $v) {
                $MemberClient[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $MemberClient[$v['rowFlag']]['androidRegister'] = $v['member'];
            }
        }
        if (!empty($iosRegister)) {
            foreach ($iosRegister as $k => $v) {
                $MemberClient[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $MemberClient[$v['rowFlag']]['iosRegister'] = $v['member'];
            }
        }
        if (!empty($wxRegister)) {
            foreach ($wxRegister as $k => $v) {
                $MemberClient[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $MemberClient[$v['rowFlag']]['wxRegister'] = $v['member'];
            }
        }
        if (!empty($otherRegister)) {
            foreach ($otherRegister as $k => $v) {
                $MemberClient[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $MemberClient[$v['rowFlag']]['otherRegister'] = $v['member'];
            }
        }
        if (!empty($androidMember)) {
            foreach ($androidMember as $k => $v) {
                $MemberClient[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $MemberClient[$v['rowFlag']]['androidMember'] = $v['member'];
            }
        }
        if (!empty($iosMember)) {
            foreach ($iosMember as $k => $v) {
                $MemberClient[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $MemberClient[$v['rowFlag']]['iosMember'] = $v['member'];
            }
        }
        if (!empty($wxMember)) {
            foreach ($wxMember as $k => $v) {
                $MemberClient[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $MemberClient[$v['rowFlag']]['wxMember'] = $v['member'];
            }
        }
        if (!empty($downloadMember)) {
            foreach ($downloadMember as $k => $v) {
                $MemberClient[$v['rowFlag']]['rowFlag'] = $v['rowFlag'];
                $MemberClient[$v['rowFlag']]['downloadMember'] = $v['member'];
            }
        }
        if (!empty($MemberClient)) {
            foreach ($MemberClient as $k => $v) {
                !isset($v['androidRegister']) ? $MemberClient[$k]['androidRegister'] = 0 : $MemberClient[$k]['androidRegister'] = $v['androidRegister'];
                !isset($v['iosRegister']) ? $MemberClient[$k]['iosRegister'] = 0 : $MemberClient[$k]['iosRegister'] = $v['iosRegister'];
                !isset($v['wxRegister']) ? $MemberClient[$k]['wxRegister'] = 0 : $MemberClient[$k]['wxRegister'] = $v['wxRegister'];
                !isset($v['otherRegister']) ? $MemberClient[$k]['otherRegister'] = 0 : $MemberClient[$k]['otherRegister'] = $v['otherRegister'];
                !isset($v['androidMember']) ? $MemberClient[$k]['androidMember'] = 0 : $MemberClient[$k]['androidMember'] = $v['androidMember'];
                !isset($v['iosMember']) ? $MemberClient[$k]['iosMember'] = 0 : $MemberClient[$k]['iosMember'] = $v['iosMember'];
                !isset($v['wxMember']) ? $MemberClient[$k]['wxMember'] = 0 : $MemberClient[$k]['wxMember'] = $v['wxMember'];
                !isset($v['downloadMember']) ? $MemberClient[$k]['downloadMember'] = 0 : $MemberClient[$k]['downloadMember'] = $v['downloadMember'];
                if (isset($v['downloadMember'])) {
                    $MemberClient[$k]['undownloadMember'] = $v['allMember'] - $v['downloadMember'];
                } else {
                    $MemberClient[$k]['undownloadMember'] = 0;
                }
                $MemberClient[$k]['androidRegisterRate'] = round2point($MemberClient[$k]['androidRegister'] / $v['allMember'] * 100) . '%';
                $MemberClient[$k]['iosRegisterRate'] = round2point($MemberClient[$k]['iosRegister'] / $v['allMember'] * 100) . '%';
                $MemberClient[$k]['wxRegisterRate'] = round2point($MemberClient[$k]['wxRegister'] / $v['allMember'] * 100) . '%';
                $MemberClient[$k]['otherRegisterRate'] = round2point($MemberClient[$k]['otherRegister'] / $v['allMember'] * 100) . '%';
                $MemberClient[$k]['downloadMemberRate'] = round2point($MemberClient[$k]['downloadMember'] / $v['allMember'] * 100) . '%';
                $MemberClient[$k]['undownloadMemberRate'] = round2point($MemberClient[$k]['undownloadMember'] / $v['allMember'] * 100) . '%';
            }
        }

        return $MemberClient;
    }

    public function getMemberClientInfo($orderGroupSql, $dateRange, $type,$member_type)
    {
        $data_list = Db::query(
            "SELECT " .
            "   FROM_UNIXTIME(u.create_time, '" . $orderGroupSql . "') as rowFlag, " .
            "   count(*) as member " .
            "FROM cg_user u " .
            "LEFT JOIN cg_user_login_log b on b.user_id = u.id " .
            "WHERE u.status = 1 " .
            (
            $type === 2
                ? "and b.login_client_type = 2 "
                : (
            $type === 3
                ? "and b.login_client_type = 3 "
                : (
            $type === 4
                ? "and b.login_client_type = 4 "
                : ""
            )
            )
            ) .
            (
            $member_type
                ? " and u.user_type in (". $member_type .")"
                : ""
            ) .
            (
            $dateRange
                ? " and u.create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and u.create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            "GROUP BY rowFlag"
        );
        return $data_list;
    }

    //获取注册人数下载APP
    public function getDownloadApp($orderGroupSql, $dateRange,$member_type)
    {
        $data_list = Db::query(
            "SELECT " .
            "   FROM_UNIXTIME(u.create_time, '" . $orderGroupSql . "') as rowFlag, " .
            "   count(*) as member " .
            "FROM cg_user u " .
            "WHERE u.status = 1 " .
            (
            $member_type
                ? " and u.user_type in (". $member_type .")"
                : ""
            ) .
            (
            $dateRange
                ? " and u.create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and u.create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            "and exists(select ul.id FROM cg_user_login_log ul " .
            " WHERE u.id = ul.user_id and ul.login_client_type in(2,3) limit 1 )  " .
            "GROUP BY rowFlag"
        );
        return $data_list;

    }

}