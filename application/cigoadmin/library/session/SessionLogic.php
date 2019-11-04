<?php
namespace app\cigoadmin\library\session;

/**
 * Class SessionLogic
 * 用户登录管理基类
 *
 * @package app\cigoadmin\library\session
 */
abstract class SessionLogic
{
    /**
     * 检查是否登陆
     */
    public abstract function isLogIn();

    /**
     * 执行登陆操作
     *
     * @param $userInfo 用户信息
     */
    public abstract function doLogIn($userInfo);

    /**
     * 执行退出登陆操作
     */
    public abstract function doLogOut();

    /**
     * 检查登录是否超时
     */
    protected abstract function checkIfTimeOut();

    /**
     * 保存登录信息
     */
    public abstract function saveUserInfoToSession();

    /**
     * 获取登录信息
     */
    public abstract function getUserInfoFromSession();

    /**
     * 修改昵称
     *
     * @param $params
     *
     * @return
     */
    public abstract function modifyNickName($params);

    /**
     * 修改密码
     *
     * @param $params 参数
     */
    public abstract function modifyPwd($params);
}