<?php

namespace app\admin\controller;

use app\cigoadmin\controller\Backend;
use app\cigoadmin\library\flags\DataTag;
use app\cigoadmin\library\flags\VerifyCodeFlag;
use app\cigoadmin\library\session\ManagerLogic;
use app\cigoadmin\library\utils\Common;
use think\captcha\Captcha;

class Login extends Backend
{
    public function index()
    {
        $this->assign('pageTitle', '西谷后台-Demo');
        return $this->fetch();
    }

    public function doLogIn()
    {
        if (!$this->request->isPost()) {
            $this->error('访问异常!');
        }

        /* 检测用户名是否为空 */
        if (empty($_POST['username'])) {
            $this->error('请输入用户名！');
        }
        /* 检测密码是否为空 */
        if (empty($_POST['password'])) {
            $this->error('请输入密码！');
        }
        /* 检测验证码 TODO: */
        if (empty($_POST['verify_code'])) {
            $this->error('请输入验证码！');
        }
        if (!Common::verifyCheck($_POST['verify_code'], VerifyCodeFlag::ADMIN)) {
            $this->error('验证码输入错误！');
        }

        /* 调用UC登录接口登录 */
        $userApi = new ManagerLogic();
        $result = $userApi->doLogIn(array(
            ManagerLogic::DATA_TAG_USERNAME => $_POST['username'],
            ManagerLogic::DATA_TAG_PASSWORD => $_POST['password']
        ));

        //登陆成功
        if ($result[DataTag::STATUS]) {
            $this->success($result[DataTag::MSG], url('Index/index'));
        } else { // 登录失败
            $this->error($result[DataTag::MSG]);
        }
    }

    public function verifyCode()
    {
        $captcha = new Captcha();
        $captcha->length = 4;
        return $captcha->entry(VerifyCodeFlag::ADMIN);
    }
}
