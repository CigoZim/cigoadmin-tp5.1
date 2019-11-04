<?php
//----------------------------------------------------------------------------------------------------------------------
// 服务器接口返回数据格式
// {
//     'code': 'code',
//     'msg': '消息',
//     'data': {
//         'key': 加密后的aes key字符串,
//         'data': {}/string 数据对象/字符串/json字符串
//         'errorCode': 'errorCode'
//     }
// }

// 加密接口终端发起请求数据格式
// {
//      'tk': 'token',
//      'key': 加密后的aes key字符串,
//      'cg': json字符串,
//      'data': {}/string 数据对象/字符串/json字符串
// }
// 非加密接口终端发起请求数据格式， key=>val
// {
// }
//----------------------------------------------------------------------------------------------------------------------

namespace app\cigoadmin\controller;

use think\Db;
use app\cigoadmin\library\encrypt\Encrypt;
use app\cigoadmin\library\flags\EncryptType;
use app\cigoadmin\library\flags\ErrorCode;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Env;
use think\Exception;
use think\exception\DbException;
use think\facade\Request;

/**
 * Class Api
 * @package app\cigoadmin\controller
 * @summary 西谷Api基类
 */
class Api extends CigoAdmin
{
    protected $clientUserInfo = '';

    protected function initialize()
    {
        parent::initialize();
        //TOOD 检查为什么需要跨域访问
        header('Access-Control-Allow-Origin:*');//允许跨域
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, version");
        header("Access-Control-Max-Age: 86400");
    }

    /**
     * 获取本次请求中登录用户信息
     *
     * @return mixed
     */
    public function getClientUserInfo()
    {
        return $this->clientUserInfo;
    }

    /**
     * 获取普通非加密请求参数
     *
     * @return mixed 字符串/json字符串/对象
     */
    protected function getRequestArgsCommon()
    {
        return Request::request();
    }

    /**
     * 获取加密请求参数
     *
     * @param string $encryptType 加密key类型
     * @return mixed 字符串/json字符串
     * @throws Exception
     */
    protected function getRequestArgsDecrypted($encryptType = EncryptType::RSA_KEY_TYPE_OPEN)
    {
        if (!Request::isPost()) {
            $this->apiReturn(0, [], '请求类型错误！', ErrorCode::REQUEST_TYPE_WRONG);
        }
        $requestArgs = Request::request();
        if (
            !isset($requestArgs['key']) || empty($requestArgs['key']) ||
            !isset($requestArgs['cg']) || empty($requestArgs['cg'])
        ) {
            $this->apiReturn(0, [], '参数错误！', ErrorCode::ARGS_WRONG);
        }

        //获取aesKey
        $this->descryptAeskey($encryptType, $requestArgs);
        //解密并验证cg中所有验证参数
        $this->descryptCg($requestArgs);
        //通过解密出来的key对数据进行AES解密
        if (isset($requestArgs['data']) && !empty($requestArgs['data'])) {
            config('cigo.IF_ENCRYPT')
                ? $requestArgs['data'] = Encrypt::aesDescrypt($requestArgs['data'], $requestArgs['key'])
                : false;
            $requestArgs['data'] = json_decode($requestArgs['data'], true);
        }
        return $requestArgs;
    }

    private function descryptCg(&$requestArgs)
    {
        $cgJson = config('cigo.IF_ENCRYPT')
            ? Encrypt::aesDescrypt($requestArgs['cg'], $requestArgs['key'])
            : $requestArgs['cg'];
        $cgArr = json_decode($cgJson, true);

        if (config('cigo.IF_ENCRYPT')) {
            $this->checkCg($cgArr);
        }
        $requestArgs['cg'] = $cgArr;
    }

    private function checkCg($cgArr)
    {
        //验证cg中请求时间戳
        if (!isset($cgArr['requestTime']) ||
            empty($cgArr['requestTime']) ||
            abs(time() - $cgArr['requestTime']) >= config('cigo.REQUEST_ALLOWABLE_TIME_INTERVAL')
        ) {
            $this->apiReturn(0, [], '参数错误！', ErrorCode::ARGS_WRONG);
        }
    }

    /**
     * 解析获取aeskey
     *
     * @param $encryptType
     * @param $requestArgs
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    private function descryptAeskey($encryptType, &$requestArgs)
    {
        $rsaPrivateKey = '';
        switch ($encryptType) {
            case EncryptType::RSA_KEY_TYPE_OPEN:
                config('cigo.IF_ENCRYPT')
                    ? $rsaPrivateKey = config('cigo.RSA_PRIVATE_KEY')['value']
                    : false;
                break;
            case EncryptType::RSA_KEY_TYPE_CLIENT:
                if (
                    !isset($requestArgs['tk']) || empty($requestArgs['tk'])
                ) {
                    $this->apiReturn(0, [], '请登录后再试！', ErrorCode::NEED_RELOGIN);
                }
                $this->checkUserInfoByTk($requestArgs['tk']);
                $rsaPrivateKey = $this->clientUserInfo['rsa_private_key_server'];
                break;
            default:
                throw new Exception('操作错误！');
                break;
        }
        if (config('cigo.IF_ENCRYPT')) {
            Encrypt::rsaDecryptByPrivateKey($rsaPrivateKey, $requestArgs['key'], $aesKey);
            ($aesKey == '' || $aesKey == false)
                ? $this->apiReturn(0, [], '参数错误！', ErrorCode::ARGS_WRONG)
                : false;
            $requestArgs['key'] = $aesKey;
        }
    }

    /**
     * 根据请求参数中的tk获取用户信息
     *
     * @param $token
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    private function checkUserInfoByTk($token)
    {

        //获取用户信息
        /*
        $data = Db::table('silversea_wl_yw.cg_user')
            ->where(array(
                'token' => $token
            ))->find();
        */
        $data = Db::table('cg_user')->where('token', $token)->find();
        if (!$data) {
            $this->apiReturn(0, [], '已在其它终端登录，请重新登录！', ErrorCode::NEED_RELOGIN);
        }
        if (!(abs($data['rsa_key_time_server'] - time()) < config('cigo.TOKEN_TIMEOUT'))) {
            $this->apiReturn(0, [], '登录已超时，请重新登录', ErrorCode::NEED_RELOGIN);
        }
        $this->clientUserInfo = $data;
        $this->updateUserAction();
    }

    private function updateUserAction()
    {
        //记录用户最后的活动信息
        $user = $this->getClientUserInfo();
        if (isset($user['id'])) {
            $data = array(
                'last_active_time' => time(),
                'last_active_ip' => Request::ip(),
                'last_active_client' => checkClientType()
            );
            Db::table('cg_user')->where('id = ' . $user['id'])->update($data);
        }
    }

    /**
     * api请求返回
     *
     * @param integer $code 返回的code
     * @param mixed $data 要返回的数据
     * @param mixed $msg 提示信息
     * @param string $errorCode 错误码
     * @param array $header 发送的Header信息
     * @param string $type 返回数据格式
     */
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

    /**
     * 加密请求返回
     *
     * @param string $rsaPublicKey rsa加密公钥
     * @param mixed $data 要返回的数据
     * @return array|false|mixed|string
     * @throws Exception
     */
    protected
    function encryptedApiData(
        $rsaPublicKey = '',
        $data = array()
    )
    {
        $data = json_encode($data);
        $aesKey = Encrypt::initAesKey();
        $encryptAesKeyStr = $aesKey;
        if (config('cigo.IF_ENCRYPT')) {
            Encrypt::rsaEncryptByPublicKey($rsaPublicKey, $aesKey, $encryptAesKeyStr);
            $data = Encrypt::aesEncrypt($data, $aesKey);
        }
        return array(
            'key' => $encryptAesKeyStr,
            'data' => $data,
        );
    }
}
