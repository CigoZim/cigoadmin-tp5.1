<?php

namespace app\cigoadmin\library\session;

use app\cigoadmin\library\flags\DataTag;
use app\cigoadmin\library\utils\Common;
use app\cigoadmin\model\Manager;
use PDOStatement;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;
use think\Model;

/**
 * Class ManagerLogic
 * 通用后台用户登录管理类(可被继承实现定制化登录)
 *
 * @package app\cigoadmin\library\session
 */
class ManagerLogic extends SessionLogic
{
    const DATA_TAG_USERNAME = "username";
    const DATA_TAG_PASSWORD = "password";
    const DATA_TAG_ID = "id";
    const DATA_TAG_IS_ADMIN = "isAdmin";
    const DATA_TAG_NICKNAME = "nickname";
    const DATA_TAG_USERINFO = "_userinfo";
    const DATA_TAG_LOGTIME = "_logtime";

    /**
     * 检查是否登陆
     */
    public function isLogIn()
    {
        if (!session(Request::module() . ManagerLogic::DATA_TAG_USERINFO)) {
            return array(
                DataTag::STATUS => false
            );
        } else {
            //判断是否过期
            if (!$this->checkIfTimeOut()) {
                return array(
                    DataTag::STATUS => false
                );
            }
            //更新登陆时间
            session(Request::module() . ManagerLogic::DATA_TAG_LOGTIME, time());

            //返回用户信息
            return $this->getUserInfoFromSession();
        }
    }

    /**
     * 获取存储用户信息
     *
     * @param $userInfo
     * @return array|PDOStatement|string|Model|null
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    protected function queryUser($userInfo)
    {
        $model = new Manager();
        $result = $model
            ->field('id,is_admin,username,nickname,password,status')
            ->where([
                'username' => $userInfo[ManagerLogic::DATA_TAG_USERNAME],
                'module' => config('cigo.MODULE_LIST')[Request::module()]
            ])
            ->find();
        return $result;
    }

    /**
     * 执行登陆操作
     *
     * @param array $userInfo 用户信息
     * @return array
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function doLogIn($userInfo)
    {
        $result = $this->queryUser($userInfo);
        if (!$result) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '用户不存在！'
            );
        }
        if (!$result['status']) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '用户已被禁止！'
            );
        }
        if (Common::encrypt($userInfo[ManagerLogic::DATA_TAG_PASSWORD]) != $result['password']) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '密码错误！'
            );
        }

        $this->saveUserInfoToSession($result);
        //保存登陆时间
        session(Request::module() . ManagerLogic::DATA_TAG_LOGTIME, time());
        return array(
            DataTag::STATUS => TRUE,
            DataTag::MSG => '登陆成功，稍后跳转...'
        );
    }

    /**
     * 执行退出登陆操作
     */
    public function doLogOut()
    {
        session(Request::module() . ManagerLogic::DATA_TAG_USERINFO, NULL);
        return array(
            DataTag::STATUS => TRUE,
            DataTag::MSG => '退出成功！'
        );
    }

    /**
     * 检查登录是否超时
     */
    protected function checkIfTimeOut()
    {
        if (time() - session(Request::module() . ManagerLogic::DATA_TAG_LOGTIME) > config('cigo.ADMIN_LOGIN_TIMEOUT')) {
            return false;
        }
        return true;
    }

    /**
     * 保存登录信息
     * @param array $userInfo
     */
    public function saveUserInfoToSession($userInfo = array())
    {
        //保存用户信息到Session
        session(Request::module() . ManagerLogic::DATA_TAG_USERINFO, array(
            ManagerLogic::DATA_TAG_ID => $userInfo['id'],
            ManagerLogic::DATA_TAG_IS_ADMIN => $userInfo['is_admin'],
            ManagerLogic::DATA_TAG_USERNAME => $userInfo['username'],
            ManagerLogic::DATA_TAG_PASSWORD => $userInfo['password'],
            ManagerLogic::DATA_TAG_NICKNAME => $userInfo['nickname']
        ));
    }

    /**
     * 获取登录信息
     */
    public function getUserInfoFromSession()
    {
        $userInfo = session(Request::module() . ManagerLogic::DATA_TAG_USERINFO);
        return $userInfo == null
            ? array(
                DataTag::STATUS => false,
                DataTag::MSG => '用户已注销！'
            )
            : array(
                DataTag::STATUS => true,
                DataTag::DATA => array(
                    ManagerLogic::DATA_TAG_USERINFO => $userInfo
                )
            );
    }

    /**
     * 修改昵称
     * @param string $nickname
     * @param string $password
     * @return array
     */
    public function modifyNickName($nickname = '', $password = '')
    {
        $nickname = trim($nickname);
        $password = trim($password);
        if (empty($nickname)) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '请输入新昵称！'
            );
        }
        if (empty($password)) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '请输入用户密码！'
            );
        }
        $logUserInfo = session(Request::module() . ManagerLogic::DATA_TAG_USERINFO);
        if ($logUserInfo[ManagerLogic::DATA_TAG_PASSWORD] !== Common::encrypt($password)) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '密码错误！'
            );
        }
        //更新昵称
        //TODO byzim 数据验证
        $model = new Manager();
        $result = $model
            ->isUpdate(true)
            ->save([
                'id' => $logUserInfo[ManagerLogic::DATA_TAG_ID],
                'nickname' => $nickname
            ]);
        //判断结果
        if ($result) {
            //保存用户信息到Session
            $logUserInfo[ManagerLogic::DATA_TAG_NICKNAME] = $nickname;
            session(Request::module() . ManagerLogic::DATA_TAG_USERINFO, $logUserInfo);
            //保存登陆时间
            session(Request::module() . ManagerLogic::DATA_TAG_LOGTIME, time());

            return array(
                DataTag::STATUS => TRUE,
                DataTag::MSG => '昵称修改成功！'
            );
        } else {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '昵称修改失败！'
            );
        }
    }

    /**
     * 修改密码
     *
     * @param string $oldPwd
     * @param string $newPwd
     * @param string $repeatPwd
     * @return array
     */
    public function modifyPwd($oldPwd = '', $newPwd = '', $repeatPwd = '')
    {
        $oldPwd = trim($oldPwd);
        $newPwd = trim($newPwd);
        $repeatPwd = trim($repeatPwd);
        if (empty($oldPwd)) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '请输入原密码！'
            );
        }
        if (empty($newPwd)) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '请输入新密码！'
            );
        }
        if ($repeatPwd !== $newPwd) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '新密码两次输入不一致！'
            );
        }
        $logUserInfo = session(Request::module() . ManagerLogic::DATA_TAG_USERINFO);
        if ($logUserInfo[ManagerLogic::DATA_TAG_PASSWORD] !== Common::encrypt($oldPwd)) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '原密码错误！'
            );
        }
        //检查密码是否修改
        if ($newPwd == $oldPwd) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '密码未做修改，请尝试重新修改！'
            );
        }
        if (!Common::formatCheckPassword($newPwd)) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '密码格式错误<br/>6～20个大小写字母和数字组成！'
            );
        }

        //更新密码
        //TODO byZim 注意检查
        $model = new Manager();
        $result = $model
            ->isUpdate(true)
            ->save([
                'id' => $logUserInfo[ManagerLogic::DATA_TAG_ID],
                'password' => Common::encrypt($newPwd)
            ]);

        //判断结果
        if (!$result) {
            return array(
                DataTag::STATUS => false,
                DataTag::MSG => '密码修改失败！'
            );
        }
        //退出登录
        $this->doLogOut();
        return array(
            DataTag::STATUS => TRUE,
            DataTag::MSG => '密码修改成功！'
        );
    }
}
