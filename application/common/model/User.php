<?php

namespace app\common\model;

use app\cigoadmin\library\CigoPage;
use app\common\library\utils\CommonConst;
use think\Db;
use think\Model;

class User extends Model
{
    protected $connection = 'db_v1';

    /**
     * @param $value
     * @param $user
     * @return string
     */
    public function getHeadimgurlAttr($value, $user)
    {
        return $user['img'] ? getUploadFileUrl(getUploadFilePath(json_decode($user['img'], true)['icon'])) : getUploadFileUrl($value, false);
    }

    public function getList($map = [], $orderBy = 'create_time desc')
    {
        $count = Db::table('cg_user')->alias('u')
            ->leftJoin('cg_user fu', 'fu.id = u.first_leader')
            ->leftJoin('cg_user su', 'su.id = u.second_leader')
            ->where($map)
            ->count();
        $page = new CigoPage($count, 15);
        $field = "u.id,u.phone,u.nickname,u.realname,u.first_leader,u.second_leader,u.card_id,u.card_status,u.sex,u.integral,u.account_balance,u.account_recharge,u.account_recharge_attach,u.create_time,u.unionid,u.status,
            fu.nickname as first_leader_nickname,fu.realname as first_leader_realname,fu.phone as first_leader_phone,
            su.nickname as second_leader_nickname,su.realname as second_leader_realname,su.phone as second_leader_phone
        ";
        $data_list = Db::table('cg_user')->alias('u')
            ->leftJoin('cg_user fu', 'fu.id = u.first_leader')
            ->leftJoin('cg_user su', 'su.id = u.second_leader')
            ->field($field)
            ->where($map)
            ->limit($page->firstRow, $page->listRows)
            ->order($orderBy)
            ->select();
        if ($data_list) {
            return array(
                'showPage' => $page->show(),
                'dataList' => $data_list
            );
        } else {
            return false;
        }
    }

    //修改状态
    public function setStatus($id, $status)
    {
        $res = Db::table('cg_user')->where(['id' => $id])->update(array('status' => $status));
        if (0 === $res) {//数据无变化
            return true;
        } else if (!$res) {
            return false;
        } else {
            return true;
        }
    }

    public function getUserInfo($where)
    {
        //获取一条用户信息
        $UserInfo = Db::table('cg_user')->where($where)->find();
        if ($UserInfo) {
            return $UserInfo;
        } else {
            return false;
        }

    }

    //获取用户地址多行数据
    public function getInfoArray($where = array(), $order = '')
    {
        if (!$order) {
            $order = 'id desc';
        }
        $val = Db::table('cg_user_address')->where($where)->order($order)->select();
        if ($val) {
            return $val;
        } else {
            return null;
        }
    }

    /**
     * 根据城市名称查询id
     */
    function getLocationId($name)
    {
        $id = Db::name('region')->where(array("name" => $name))->field('id')->find();
        if ($id) {
            return $id['id'];
        } else {
            return false;
        }
    }

    /**
     * 获取城市名字
     */
    function getLocationName($id)
    {
        $infos = Db::name('region')->where(array("id" => $id))->find();
        if ($infos) {
            return $infos['name'];
        }
        return $infos;
    }

    public function getProvince()
    {
        return Db::name('region')->where(array("parent_id" => 0))->select();
    }

    //获取单一数据
    function getOneInfo($where = array(), $order = '')
    {
        if (!$order) {
            $order = 'id desc';
        }
        $val = Db::table('cg_user_address')->where($where)->order($order)->find();

        if ($val) {
            return $val;
        } else {
            return null;
        }

    }

    /**
     * 获取省市
     */
    public function getLocation()
    {
        $infos = array();
        $infos ['name'] = "地区";
        $infos ['keys'] = "location";
        $infos ['sub'] = Db::name('region')->where(array("parent_id" => 0))->select();
        foreach ($infos ['sub'] as &$sub) {
            $sub ['sub'] = $this->getSubLocation($sub ['id']);
        }
        return $infos;
    }

    /**
     * 获取省市的二级
     */
    function getSubLocation($top_localtion_id)
    {
        $top_localtion = Db::name("region")->where(array("id" => intval($top_localtion_id)))->find();
        $sub_localtion = array();
        if ($top_localtion) {
            $sub_localtion = Db::name("region")->where(array("parent_id" => intval($top_localtion ['id'])))->order(' `id` asc')->select();
        }
        return $sub_localtion;
    }

    public function getCardList($where, $orderBy = 'create_time desc')
    {
        $count = Db::table('cg_user')->where($where)->count();
        $page = new CigoPage($count, 15);
        $data_list = Db::table('cg_user')
            ->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->order($orderBy)
            ->select();
        if ($data_list) {
            return array(
                'showPage' => $page->show(),
                'dataList' => $data_list
            );
        } else {
            return array();
        }
    }

    public function lastActiveClient()
    {
        $count_all = Db::table('cg_user')->count();
        $count_pc = Db::table('cg_user')->where(['last_active_client' => 1])->count();
        $count_Android = Db::table('cg_user')->where(['last_active_client' => 2])->count();
        $count_IOS = Db::table('cg_user')->where(['last_active_client' => 3])->count();
        $count_wx = Db::table('cg_user')->where(['last_active_client' => 4])->count();
        $count_webpage = Db::table('cg_user')->where(['last_active_client' => 5])->count();
        $count_other = Db::table('cg_user')->where(['last_active_client' => 0])->count();
        $data['all'] = $count_all;
        $data['all_rate'] = round($count_all / $count_all * 100, 2);
        $data['pc'] = $count_pc;
        $data['pc_rate'] = round($count_pc / $count_all * 100, 2);
        $data['Android'] = $count_Android;
        $data['Android_rate'] = round($count_Android / $count_all * 100, 2);
        $data['IOS'] = $count_IOS;
        $data['IOS_rate'] = round($count_IOS / $count_all * 100, 2);
        $data['wx'] = $count_wx;
        $data['wx_rate'] = round($count_wx / $count_all * 100, 2);
        $data['webpage'] = $count_webpage;
        $data['webpage_rate'] = round($count_webpage / $count_all * 100, 2);
        $data['other'] = $count_other;
        $data['other_rate'] = round($count_other / $count_all * 100, 2);

        return $data;
    }

    public function registClientType()
    {
        $count_all = Db::table('cg_user')->count();
        $count_pc = Db::table('cg_user')->where(['regist_client_type' => 1])->count();
        $count_Android = Db::table('cg_user')->where(['regist_client_type' => 2])->count();
        $count_IOS = Db::table('cg_user')->where(['regist_client_type' => 3])->count();
        $count_wx = Db::table('cg_user')->where(['regist_client_type' => 4])->count();
        $count_webpage = Db::table('cg_user')->where(['regist_client_type' => 5])->count();
        $data['all'] = $count_all;
        $data['all_rate'] = round($count_all / $count_all * 100, 2);
        $data['pc'] = $count_pc;
        $data['pc_rate'] = round($count_pc / $count_all * 100, 2);
        $data['Android'] = $count_Android;
        $data['Android_rate'] = round($count_Android / $count_all * 100, 2);
        $data['IOS'] = $count_IOS;
        $data['IOS_rate'] = round($count_IOS / $count_all * 100, 2);
        $data['wx'] = $count_wx;
        $data['wx_rate'] = round($count_wx / $count_all * 100, 2);
        $data['webpage'] = $count_webpage;
        $data['webpage_rate'] = round($count_webpage / $count_all * 100, 2);
        $data['other'] = 0;
        $data['other_rate'] = 0;

        return $data;
    }

    //获取每日注册人数
    public function getRegistByDay($orderGroupSql, $dateRange, $first_leader)
    {
        $data_list = Db::query(
            "SELECT " .
            "   FROM_UNIXTIME(create_time, '" . $orderGroupSql . "') as rowFlag, " .
            "   count(*) as allMember " .
            "FROM cg_user " .
            "WHERE user_type = 0 and status = 1 " .
            (
            $first_leader === 0
                ? "and first_leader = 0 "
                : ($first_leader === 1
                ? "and first_leader <> 0 "
                : "")
            ) .
            (
            $dateRange
                ? " and create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            "GROUP BY rowFlag"
        );
        return $data_list;
    }

    //获取每日注册人数并发起砍价
    public function getRegistBargainByDay($orderGroupSql, $dateRange, $first_leader)
    {
        $data_list = Db::query(
            "SELECT " .
            "   FROM_UNIXTIME(u.create_time, '" . $orderGroupSql . "') as rowFlag, " .
            "   count(*) as allMember " .
            "FROM cg_user u " .
            "WHERE u.user_type = 0 and u.status = 1 " .
            (
            $first_leader === 0
                ? "and u.first_leader = 0 "
                : ($first_leader === 1
                ? "and u.first_leader <> 0 "
                : "")
            ) .
            (
            $dateRange
                ? " and u.create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and u.create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            "and exists(select b.id FROM cg_bargaining b " .
            " WHERE u.id = b.user_id limit 1 )" .
            "GROUP BY rowFlag"
        );
        return $data_list;

    }

    //获取每日注册人数注册APP数量
    public function getRegistUploadAppByDay($orderGroupSql, $dateRange, $first_leader)
    {
        $data_list = Db::query(
            "SELECT " .
            "   FROM_UNIXTIME(u.create_time, '" . $orderGroupSql . "') as rowFlag, " .
            "   count(*) as allMember " .
            "FROM cg_user u " .
            "WHERE  u.user_type = 0 and u.status = 1 " .
            (
            $first_leader === 0
                ? "and u.first_leader = 0 "
                : ($first_leader === 1
                ? "and u.first_leader <> 0 "
                : "")
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

    //用户发起砍价数量
    public function getUserBargainByDay($orderGroupSql, $dateRange)
    {
        $data_list = Db::query(
            "SELECT " .
            "   FROM_UNIXTIME(u.create_time, '" . $orderGroupSql . "') as rowFlag, " .
            "   count(*) as allMember " .
            "FROM cg_user u " .
            "inner join cg_bargaining b on b.user_id =u.id " .
            "WHERE u.user_type = 0 and u.status = 1 " .
            (
            $dateRange
                ? " and u.create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and u.create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            "GROUP BY rowFlag"
        );
        return $data_list;
    }

//用户是否发起砍价
    public function getBargainUserByDay($orderGroupSql, $dateRange)
    {
        $data_list = Db::query(
            "SELECT " .
            "   FROM_UNIXTIME(u.create_time, '" . $orderGroupSql . "') as rowFlag, " .
            "   count(*) as allMember " .
            "FROM cg_user u " .
            "WHERE u.user_type = 0 and u.status = 1 " .
            (
            $dateRange
                ? " and u.create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and u.create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            "and exists(select b.id FROM cg_bargaining b " .
            " WHERE u.id = b.user_id limit 1 )" .
            "GROUP BY rowFlag"
        );
        return $data_list;
    }

    //发起新老用户
    public function getBargainUserStateByDay($orderGroupSql, $dateRange, $startTime = "")
    {
        $sql = "SELECT " .
            "   FROM_UNIXTIME(cb.create_time, '" . $orderGroupSql . "') as rowFlag," .
            "   count(cb.user_id) as allMember " .
            "FROM cg_bargaining cb " .
            "LEFT JOIN cg_user cu on cb.user_id = cu.id " .
            "WHERE cu.user_type = 0 and cu.status = 1 and cb.user_id = cu.id " .
            ($startTime ? "and cu.create_time <= unix_timestamp('" . $startTime . "') " : '') .
            (
            $dateRange
                ? " and cb.create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and cb.create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            " GROUP BY rowFlag";

        $data_list = Db::query($sql);
        return $data_list;
    }

    //发起砍价终端
    public function getBargainClientUserByDay($orderGroupSql, $dateRange, $clientType = '')
    {
        $sql = "SELECT " .
            "   FROM_UNIXTIME(cb.create_time, '" . $orderGroupSql . "') as rowFlag," .
            "   count(cb.user_id) as allMember " .
            "FROM cg_bargaining cb " .
            "LEFT JOIN cg_user cu on cb.user_id = cu.id " .
            "WHERE cu.user_type = 0 and cu.status = 1 and cb.user_id = cu.id " .
            (
            $dateRange
                ? " and cb.create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and cb.create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            (
            $clientType
                ? " and cb.client_type = " . $clientType . ""
                : ""
            ) .
            " GROUP BY rowFlag";

        $data_list = Db::query($sql);
        return $data_list;
    }

    //获取每日注册人数
    public function getRegisterClientByDay($orderGroupSql, $dateRange, $first_leader)
    {
        $data_list = Db::query(
            "SELECT " .
            "   FROM_UNIXTIME(create_time, '" . $orderGroupSql . "') as rowFlag, " .
            "   count(*) as allMember " .
            "FROM cg_user " .
            "WHERE user_type = 0 and status = 1 " .
            (
            $first_leader === 1
                ? "and regist_client_type = 3 "
                : ($first_leader === 2
                ? "and regist_client_type = 2 "
                : ($first_leader === 3
                    ? "and regist_client_type = 4 "
                    : ($first_leader === 4
                        ? "and regist_client_type in (1,5) "
                        : '')
                )
            )
            ) .
            (
            $dateRange
                ? " and create_time >= unix_timestamp('" . $dateRange['startDate'] . "') and create_time < unix_timestamp('" . $dateRange['endDate'] . "') "
                : ""
            ) .
            "GROUP BY rowFlag"
        );
        return $data_list;

    }
}
