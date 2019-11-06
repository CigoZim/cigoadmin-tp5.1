<?php

namespace app\cigoadmin\library\traits;

use app\cigoadmin\library\flags\AuthResult;
use app\cigoadmin\library\flags\DataTag;
use app\cigoadmin\library\session\ManagerLogic;
use app\cigoadmin\model\AuthRule;
use think\facade\Request;

trait AuthCheck
{
    protected $authRuleDataList = array();
    protected $authLeftMenuDataList = array();
    protected $authTopMenuDataList = array();

    use Tree;

    public function check()
    {
        //检查是否需要授权检查
        if (!config('cigo.AUTH_CONFIG.auth_on')) {
            $this->getAllMenu();
            return AuthResult::AUTH_CHECK_NO_NEED_AUTH;
        }
        // 检查是否免登录，否则执行登录信息检查
        if ($this->match(config('cigo.AUTH_CONFIG.NO_NEED_LOGIN'))) {
            $this->getAllMenu();
            return AuthResult::AUTH_CHECK_NO_NEED_LOGIN;
        }
        //执行登录
        $userApi = $this->getLogLogic();
        $result = $userApi->isLogIn();
        if (!$result[DataTag::STATUS]) {
            return AuthResult::NEED_LOGIN;
        }
        //处理登录后的事宜
        $this->alreadyLogin($result);
        //检查是否超级管理员
        if (session(Request::module() . ManagerLogic::DATA_TAG_USERINFO)[ManagerLogic::DATA_TAG_IS_ADMIN]) {
            $this->getAllMenu();
            return AuthResult::AUTH_CHECK_NO_NEED_AUTH;
        }
        // 检查是否免授权检查
        if ($this->match(config('cigo.AUTH_CONFIG.NO_NEED_AUTH_CHECK'))) {
            $authRuleIds= $this->getAuthRuleIdsForLogUser();
            $noNeedAuthRuleIds = $this->getNoNeedAuthRuleIds(config('cigo.AUTH_CONFIG.NO_NEED_AUTH_CHECK'));
            $this->getAuthMenu(array_merge($authRuleIds, $noNeedAuthRuleIds));
            return AuthResult::AUTH_CHECK_NO_NEED_AUTH;
        }
        //检查访问路由是否被禁止
        if ($this->ifRouteForbidden()) {
            return AuthResult::AUTH_CHECK_ROUTE_FORBIDDEN;
        }
        //进行权限检查
        $authRuleIds = $this->getAuthRuleIdsForLogUser();
        $this->getAuthRule($authRuleIds);
        if (!$this->authCheck()) {
            return AuthResult::AUTH_CHECK_FAIL;
        }
        $this->getAuthMenu($authRuleIds);
        return AuthResult::AUTH_CHECK_SUCCESS;
    }

    /**
     * 处理登录结果
     * @param $logResult
     */
    protected function alreadyLogin($logResult)
    {
    }

    /**
     * 获取登录逻辑管理器
     *
     * @return ManagerLogic
     */
    protected function getLogLogic()
    {
        return new ManagerLogic();
    }

    /**
     * 匹配当前路由
     *
     * @param array $range
     * @return bool
     */
    private function match($range = [])
    {
        $range = is_array($range)
            ? $range
            : explode(',', $range);
        if (!$range) {
            return FALSE;
        }
        $range = array_map('strtolower', $range);
        if (
            //特定控制器下所有操作
            in_array(strtolower(Request::controller()) . config('template.view_depr'), $range) ||
            //所有控制器下特定操作操作
            in_array(strtolower(config('template.view_depr') . Request::action()), $range) ||
            //特定控制器特定操作
            in_array(strtolower(Request::controller()) . config('template.view_depr') . strtolower(Request::action()), $range) ||
            //所有请求
            in_array('*', $range)
            //TODO 特定路由?、特定url?
        ) {
            return TRUE;
        }
        return FALSE;
    }

    protected function ifRouteForbidden()
    {
        $path = strtolower(Request::controller()) . '/' . strtolower(Request::action());

        $ruleList = DB('AuthRule')
            ->where([
                ['status', 'eq', 1],
                ['url', 'in', [
                    //特定控制器下所有操作
                    strtolower(Request::controller()) . config('template.view_depr'),
                    //所有控制器下特定操作操作
                    strtolower(config('template.view_depr') . Request::action()),
                    //特定控制器特定操作
                    strtolower(Request::controller()) . config('template.view_depr') . strtolower(Request::action()),
                    //所有请求
                    '*'
                    //TODO 特定路由?、特定url?
                ]]
            ])->find();
        return $ruleList === null ? true : false;
    }

    protected function authCheck()
    {
        $authUrlList = array_map('strtolower', array_column($this->authRuleDataList, 'url'));
        return $this->match($authUrlList);
    }


    protected function getNoNeedAuthRuleIds($noNeedAuthRules = array())
    {
        return DB('AuthRule')
            ->where([
                ['url', 'in', $noNeedAuthRules],
                ['status', 'eq', 1]
            ])
            ->column('id');
    }

    protected function getAuthRuleIdsForLogUser()
    {
        //获取用户关联被禁止Group编号列表
        $groupIdList = DB('AuthGroupAccess aga')
            ->field('aga.uid, aga.group_id, ag.pid, ag.path, ag.title, ag.rules')
            ->where([
                ['aga.uid', '=', session(Request::module() . ManagerLogic::DATA_TAG_USERINFO)[ManagerLogic::DATA_TAG_ID]],
                ['ag.status', 'neq', 1]
            ])
            ->leftJoin('auth_group ag', 'ag.id = aga.group_id')
            ->column('aga.group_id');
        //获取符合要求的有效角色分组
        $map = [
            ['aga.uid', '=', session(Request::module() . ManagerLogic::DATA_TAG_USERINFO)[ManagerLogic::DATA_TAG_ID]],
            ['ag.status', 'eq', '1'],
            ['ag.id', 'not in', $groupIdList],
        ];
        foreach ($groupIdList as $item) {
            $map[] = ['ag.path', 'not like', '%,' . $item . ',%'];
        }
        //获取所有菜单
        $authRuleIdList = DB('AuthGroupAccess aga')
            ->where($map)
            ->leftJoin('auth_group ag', 'ag.id = aga.group_id')
            ->column('ag.rules');
        $authRuleIds = array();
        foreach ($authRuleIdList as $item) {
            $authRuleIds = array_merge(explode(',', $item), $authRuleIds);
        }
        $authRuleIds = array_unique($authRuleIds);
        //过滤无效菜单
        return DB('AuthRule')
            ->where([
                ['id', 'in', $authRuleIds],
                ['status', 'eq', 1]
            ])
            ->column('id');
    }

    protected function getAllMenu()
    {
        //左侧菜单
        $model = new AuthRule();
        $leftMenuList = $model->getList([
            ['status', '=', 1],
            ['menu_flag', '=', 1],
        ]);
        $this->convertToTree($leftMenuList, $this->authLeftMenuDataList, 0, 'pid');
        //顶部菜单
        $topMenuList = $model->getTopList([
            ['status', '=', 1],
            ['menu_flag', '=', 1],
            ['show_top_menu', '=', 1]
        ]);
        $topMenuTreeList = array();
        $this->convertToTree($topMenuList, $topMenuTreeList, 0, 'pid', false);
        $this->getTopTree($topMenuTreeList, $this->authTopMenuDataList);
    }

    protected function getAuthRule($authRuleIds = array())
    {
        //获取符合要求的菜单或者功能节点
        $this->authRuleDataList = DB('AuthRule ar')
            ->where([
                ['ar.status', 'eq', 1],
                ['ar.id', 'in', array_unique(explode(',', trim(implode(',', $authRuleIds), ',')))]
            ])
            ->select();
    }

    protected function getAuthMenu($authRuleIds = array())
    {
        //左侧菜单
        $model = new AuthRule();
        $leftMenuList = $model->getList([
            ['status', 'eq', 1],
            ['menu_flag', 'eq', 1],
            ['id', 'in', array_unique(explode(',', trim(implode(',', $authRuleIds), ',')))]
        ]);
        $this->convertToTree($leftMenuList, $this->authLeftMenuDataList, 0, 'pid');
        //顶部菜单
        $topMenuList = $model->getTopList([
            ['status', 'eq', 1],
            ['menu_flag', 'eq', 1],
            ['show_top_menu', 'eq', 1],
            ['id', 'in', array_unique(explode(',', trim(implode(',', $authRuleIds), ',')))]
        ]);
        $topMenuTreeList = array();
        $this->convertToTree($topMenuList, $topMenuTreeList, 0, 'pid', false);
        $this->getTopTree($topMenuTreeList, $this->authTopMenuDataList);
    }
}

