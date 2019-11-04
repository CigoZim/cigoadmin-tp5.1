<?php

namespace app\cigoadmin\controller\backend;

use app\cigoadmin\controller\Backend;
use app\cigoadmin\library\flags\AuthResult;
use app\cigoadmin\library\flags\DataTag;
use app\cigoadmin\library\session\ManagerLogic;
use app\cigoadmin\library\traits\AuthCheck;

/**
 * Class Editor
 * @package app\cigoadmin\controller\Backend
 * @summary 主要负责后台登录状态、权限管理
 */
class SessionAuthCheck extends Backend
{

    use AuthCheck;

    public function initialize()
    {
        parent::initialize();
        //Auth检
        $authStatus= $this->check();
        switch ($authStatus) {
            case AuthResult::NEED_LOGIN:
                $this->redirect('Login/index');
                break;
            case AuthResult::AUTH_CHECK_FAIL:
                $this->error('您无权限访问此功能，请联系管理员！');
                break;
            case AuthResult::AUTH_CHECK_ROUTE_FORBIDDEN:
                $this->error('访问路由被禁止或不存在，请联系管理员！');
                break;
            case AuthResult::AUTH_CHECK_SUCCESS:
            case AuthResult::AUTH_CHECK_NO_NEED_LOGIN:
            case AuthResult::AUTH_CHECK_NO_NEED_AUTH:
            default:
                break;
        }
    }

    protected function alreadyLogin($logResult)
    {
        $this->assign('nickName', $logResult[DataTag::DATA][ManagerLogic::DATA_TAG_USERINFO][ManagerLogic::DATA_TAG_NICKNAME]);
    }

    /* 退出登录 */
    public function logout()
    {
        $userApi = $this->getLogLogic();
        $result = $userApi->doLogOut();
        $this->success($result[DataTag::MSG], url('Login/index'));
    }
}
