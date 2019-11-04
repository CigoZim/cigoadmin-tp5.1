<?php

namespace app\cigoadmin\library\utils;

use app\cigoadmin\library\flags\ClientType;
use app\cigoadmin\library\flags\Sex;
use app\cigoadmin\library\flags\SexTip;
use app\cigoadmin\model\Trash;
use think\captcha\Captcha;

class Common
{
    /**
     * 检测验证码
     * @param string $code 验证码
     * @param string $id 验证码标识
     * @return bool 检测结果
     */
    static function verifyCheck($code, $id = '')
    {
        $captcha = new Captcha();
        return $captcha->check($code, $id);
    }

    /**
     * 时间戳格式化
     * @param int $time
     * @param string $format 时间格式
     * @return string 完整的时间显示
     */
    static function timeFormat($time = NULL, $format = 'Y-m-d H:i')
    {
        $time = $time === NULL ? time() : intval($time);
        return date($format, $time);
    }

    /**
     * 加密函数
     * @param string $src 明文
     * @return string 加密后的密文
     */
    static function encrypt($src)
    {
        return base64_encode(hash('sha256', $src, false));
    }

    /**
     * 获取性别文字提示
     * @param int $sex 传入性别
     * @return string 返回性别文字提示
     */
    static function getSexTip($sex)
    {
        switch ($sex) {
            case Sex::MAN:
                $sexTxt = SexTip::MAN;
                break;
            case Sex::WOMEN:
                $sexTxt = SexTip::WOMEN;
                break;
            case Sex::UNKOWN:
            default:
                $sexTxt = SexTip::UNKOWN;
                break;
        }
        return $sexTxt;
    }

    /**
     * 获取菜单url地址
     * @param string $url 传入pathinfo地址
     * @return string 返回菜单地址
     */
    static function get_menu_url($url)
    {
        switch ($url) {
            case 'http://' === substr($url, 0, 7):
            case 'https://' === substr($url, 0, 8):
            case '#' === substr($url, 0, 1):
                break;
            default:
                $url = url($url);
                break;
        }
        return $url;
    }

    /**
     * 检查终端类型
     * @return int 终端类型标识
     */
    static function checkClientType()
    {
        $clientAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($clientAgent, "android")) {
            return ClientType::ANDROID;
        } else if (strpos($clientAgent, "iphone")) {
            return ClientType::IPHONE;
        } else {
            return ClientType::PC;
        }
    }

    /**
     * 检查是否微信终端
     * @return bool|int
     */
    static function checkIfWeiXinClientType()
    {
        $clientAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        return strpos($clientAgent, "micromessenger") !== false;
    }

    /**
     * 将从数据库中读取的文案数据转化成前端显示数据
     * @param string $src
     * @return string
     */
    static function convert_doc_content($src)
    {
        return htmlspecialchars_decode(html_entity_decode($src));
    }

    /**
     * 格式验证
     * @param string $value 待验证数据
     * @param string $rule 验证规则
     * @return bool 验证结果
     */
    static function regex($value, $rule)
    {
        $validate = array(
            'username' => '/^\w{3,20}$/',
            'password' => '/^[A-Za-z0-9_]{6,20}$/',
            'nickname' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]{1,10}$/u',
            'phone' => '/^1[1-9]{1}[0-9]{9}$/',
            'require' => '/\S+/',
            'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url' => '/^http(s?):\/\/(?:[A-Za-z0-9-_]+\.)+[A-Za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'number' => '/^\d+$/',
            'zip' => '/^\d{6}$/',
            'integer' => '/^[-\+]?\d+$/',
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'english' => '/^[A-Za-z]+$/',
            'money' => "/^([1-9][\d]{0,7}|0)(\.[\d]{1,2})?$/",
        );
        // 检查是否有内置的正则表达式
        if (isset($validate[strtolower($rule)]))
            $rule = $validate[strtolower($rule)];

        return preg_match($rule, $value) === 1;
    }

    /**
     * 手机号码格式判断
     * @param string $phone 手机号码
     * @return bool 验证结果
     */
    static function formatCheckPhone($phone = '')
    {
        return Common::regex($phone, 'phone');
    }

    /**
     * 密码格式判断
     * @param string $password 密码
     * @return bool 验证结果
     */
    static function formatCheckPassword($password = '')
    {
        return Common::regex($password, 'password');
    }

    /**
     * 昵称格式判断
     * @param string $nickName 用户昵称
     * @return bool 验证结果
     */
    static function formatCheckNickName($nickName = '')
    {
        return Common::regex($nickName, 'nickname');
    }

    /**
     * 数字格式判断
     * @param string $val 待验证数字字符串
     * @return bool 验证结果
     */
    static function formatCheckInteger($val = '')
    {
        return Common::regex($val, 'integer');
    }

    /**
     * 金额判断
     * @param string $val 待验证金额字符串
     * @return bool 验证结果
     */
    static function formatCheckMoney($val = '')
    {
        return Common::regex($val, 'money');
    }


    /**
     * 邮箱格式判断
     * @param string $email 待验证邮箱
     * @return bool 验证结果
     */
    static function formatCheckEmail($email = '')
    {
        return Common::regex($email, 'email');
    }

    /**
     * 邮政编码格式判断
     * @param string $zip 待验证邮政编码
     * @return bool 验证结果
     */
    static function formatCheckZip($zip = '')
    {
        return Common::regex($zip, 'zip');
    }


    static function prepareDateToTimeStamp(&$data, $editKey = 'date', $autoDefault = false)
    {
        if (isset($data[$editKey]) && $data[$editKey] != '') {
            $data[$editKey] = strtotime($data[$editKey]);
        } else {
            if ($autoDefault) {
                $data[$editKey] = time();
            }
        }
    }

    static function prepareDateToString(&$data, $dateTimeFormat = 'Y-m-d H:i', $editKey = 'date', $autoDefault = false)
    {
        if (isset($data[$editKey]) && $data[$editKey] != '') {
            $data->$editKey = date($dateTimeFormat, $data[$editKey]);
        } else {
            if ($autoDefault) {
                $data->$editKey = date($dateTimeFormat, time());
            }
        }
    }

    static function prepareMultiDataToJson(&$data, $editKey = 'img', $removeEmptyFlag = true)
    {
        if (isset($data[$editKey]) && $data[$editKey] != '') {
            $tempKeyData = array();
            foreach ($data[$editKey] as $key => $item) {
                if ($item != '' || !$removeEmptyFlag) {
                    $tempKeyData[$key] = $item;
                }
            }
            $data[$editKey] = json_encode($tempKeyData);
        }
    }

    static function prepareMultiDataToArray(&$data, $editKey = 'img')
    {
        if (isset($data[$editKey]) && $data[$editKey] != '') {
            $data[$editKey] = json_decode($data[$editKey], true);
        }
    }

    static function deleteFromTrash($dataId = 0, $module = 0)
    {
        if (empty($dataId) || empty($module)) {
            return;
        }

        $trashModel = new Trash();
        $trashModel->where([
            ['data_id', '=', $dataId],
            ['module', '=', $module]
        ])->delete();
    }
}
