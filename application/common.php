<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Db;
use think\facade\Env;

/**
 * 获取下一级地址列表
 * @param int $pid
 * @return mixed
 */
function getRegionListByPid($pid = 0)
{
    return model('region')->where(array('parent_id' => intval($pid)))->select();
}

/**
 * 获取地址详情
 * @param $id
 * @return mixed
 */
function getRegionById($id = 0)
{
    return model('region')->where(array('id' => $id))->find();
}

/**
 * 获取上传文件路径
 * @param int $fileId
 * @param string $field
 * @param \think\Model $model
 * @return string
 */
function getUploadFilePath($fileId = 0, $field = 'path', $model = null)
{
    if (empty($fileId)) {
        return '';
    }
    $model = null == $model ? new \app\cigoadmin\model\Files() : $model;
    $data = $model
        ->where([
            ['id', '=', $fileId],
            ['status', '=', 1]
        ])
        ->find();
    if (!$data) {
        return '';
    }
    return empty($field) ? $data : trim($data[$field], '.');
}

/**
 * 获取上传文件Url地址
 * @param string $path 图片path
 * @param bool $isCdn
 * @return string 图片url
 */
function getUploadFileUrl($path, $isCdn = true)
{

    if (empty($path)) {
        return '';
    }
    switch ($path) {
        case 'http://' === substr($path, 0, 7):
        case 'https://' === substr($path, 0, 8):
            break;
        default:
            //TODO byzim
            $path = "https://" . (
                $isCdn
                    ? \think\facade\Env::get('APP_CDN_DOMAIN')
                    : Env::get('APP_DOMAIN')
                ) . $path;
            break;
    }
    return $path;
}

/*
 * 更换编辑器上传图片地址
 * * @param string $body 内容
 * @return string 图片url
 */
function replaceUEditorBody($body = '')
{
    return str_replace(
        'src="/ueditor/php/upload/image/',
        'src="https://' . env('APP_CDN_DOMAIN') . '/ueditor/php/upload/image/', $body
    );
}


/**
 * 格式验证
 * @param $value
 * @param $rule
 * @return bool
 */
function regex($value, $rule)
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
    if (isset($validate[strtolower($rule)])) {
        $rule = $validate[strtolower($rule)];
    }

    return preg_match($rule, $value) === 1;
}

/**
 * 验证身份证号是否正确
 * @param $id
 * @return bool
 */
function check_idcard($id)
{
    $id = strtoupper($id);
    $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
    $arr_split = array();
    if (!preg_match($regx, $id)) {
        return false;
    }
    if (15 == strlen($id)) //检查15位
    {
        $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

        @preg_match($regx, $id, $arr_split);
        //检查生日日期是否正确
        $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) {
            return false;
        } else {
            return true;
        }
    } else      //检查18位
    {
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) //检查生日日期是否正确
        {
            return false;
        } else {
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $sign = 0;
            for ($i = 0; $i < 17; $i++) {
                $b = (int)$id{$i};
                $w = $arr_int[$i];
                $sign += $b * $w;
            }
            $n = $sign % 11;
            $val_num = $arr_ch[$n];
            if ($val_num != substr($id, 17, 1)) {
                return false;
            } //phpfensi.com
            else {
                return true;
            }
        }
    }

}

/**
 *  根据身份证号码计算年龄
 * @param string $idcard 身份证号码
 * @return int $age
 */
function getAgeByIdcard($idcard)
{
    if (empty($idcard)) {
        return 0;
    }
    #  获得出生年月日的时间戳
    $date = strtotime(substr($idcard, 6, 8));
    #  获得今日的时间戳
    $today = strtotime('today');
    #  得到两个日期相差的大体年数
    $diff = floor(($today - $date) / 86400 / 365);
    #  strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
    $age = strtotime(substr($idcard, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
    return $age;
}

/**
 * 获取显示用户昵称
 * @param array $userInfo
 * @return mixed|string
 */
function getUserNickNameShow($userInfo = array())
{
    if (isset($userInfo['realname']) && !empty($userInfo['realname'])) {
        return $userInfo['realname'];
    }
    if (isset($userInfo['nickname']) && !empty($userInfo['nickname'])) {
        return $userInfo['nickname'];
    }
    return '';
}

/**
 * 生成用户登录随机密码
 * @return bool|mixed|string
 */
function create_user_temp_password()
{
    $randStr = str_shuffle('1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    $randId = substr($randStr, 0, 8);
    return $randId;
}

/**
 * 检查终端类型
 * @return int
 */
function checkClientType()
{
    $clientAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

    if (strpos($clientAgent, "micromessenger") !== false) {
        return \app\common\library\GlobalConfig::CLIEANT_TYPE_WX;
    }

    if (strpos($clientAgent, "android") !== false) {
        return \app\common\library\GlobalConfig::CLIEANT_TYPE_ANDROID;
    }

    if (strpos($clientAgent, "iphone") !== false) {
        return \app\common\library\GlobalConfig::CLIEANT_TYPE_IPHONE;
    }

    return \app\common\library\GlobalConfig::CLIEANT_TYPE_PC;

    if (strpos($clientAgent, "android")) {
        return \app\common\library\GlobalConfig::CLIEANT_TYPE_ANDROID;
    } else {
        if (strpos($clientAgent, "iphone")!==false) {
            return \app\common\library\GlobalConfig::CLIEANT_TYPE_IPHONE;
        } else {
            if (strpos($clientAgent, "micromessenger")) {
                return \app\common\library\GlobalConfig::CLIEANT_TYPE_WX;
            } else {
                return \app\common\library\GlobalConfig::CLIEANT_TYPE_PC;
            }
        }
    }
}


/**
 * 生成订单终端类型
 * @return int
 */
function createOrderCheckClientType()
{
    $clientAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (strpos($clientAgent, "android")) {
        return "A";
    } else {
        if (strpos($clientAgent, "iphone")) {
            return "I";
        } else {
            if (strpos($clientAgent, "micromessenger")) {
                return "W";
            } else {
                return "P";
            }
        }
    }
}

/**
 * 生成新编号
 * @param int $type
 * @return string
 */
function createPrintSn($type = 0)
{
    $new_print_sn = date('Ymd') . '0001';
    $map['type'] = $type;
    $map['print_sn'] = array('like', date('Ymd') . '%');
    $max_print_sn = Db::name('print_log')->where($map)->max('print_sn');
    if ($max_print_sn) {
        $new_print_sn = $max_print_sn + 1;
    }
    return $new_print_sn;
}


/**
 * 生成商品sku唯一编码
 * @return string
 */
function create_goods_serial_num()
{
    $serial_num = mb_substr(date('Ymd'), 2, 8, 'utf-8') . substr(time() . '', -4);
    $info = model('GoodsSku')->where(array('serial_num' => $serial_num))->find();
    return $info ? create_goods_serial_num() : $serial_num;
}

/**
 * 生成商品spu唯一编码
 * @return string
 */
function create_spu_serial_num()
{
    $serial_num = mb_substr(date('Ymd'), 2, 8, 'utf-8') . substr(time() . '', -4);
    $info = model('GoodsSpu')->where(array('serial_num' => $serial_num))->find();
    return $info ? create_spu_serial_num() : $serial_num;
}

/**
 * 生成商品spec唯一编码
 * @return string
 */
function create_spec_sn()
{
    $serial_num = mb_substr(date('Ymd'), 2, 8, 'utf-8') . substr(time() . '', -4);
    $info = model('GoodsSpec')->where(array('spec_sn' => $serial_num))->find();
    return $info ? create_spec_sn() : $serial_num;
}

/**
 * 生成唯一长度数字编码
 * @param $model
 * @param string $filed
 * @param int $len
 * @return bool|string
 */
function create_random_sn($model, $filed = '', $len = 6)
{
    if (!$model || !$filed) {
        return '';
    }
    $randStr = str_shuffle(time());
    $randId = substr($randStr, 0, $len);
    $info = $model->where($filed, $randId)->find();
    return $info ? create_random_sn($model, $filed = '', $len) : $randId;
}

/**
 * 生成唯一长度年度8位数字编码
 * @param $model
 * @param string $filed
 * @param int $len
 * @return bool|string
 */
function create_partner_sn()
{
    $randStr = date('Y') . substr(time() . '', -4);
    $info = model('partner')->where('partner_sn', $randStr)->find();
    return $info ? create_partner_sn() : $randStr;
}

/**
 * 生成唯一长度年度8位数字编码
 * @param $model
 * @param string $filed
 * @param int $len
 * @return bool|string
 */
function create_shop_sn()
{
    $randStr = date('Y') . substr(time() . '', -4);
    $info = model('shop')->where('shop_sn', $randStr)->find();
    return $info ? create_shop_sn() : $randStr;
}

/**
 * 密码加密
 * @param $password
 * @return string
 */
function encryptUserPwd($password)
{
    return base64_encode(hash('sha256', $password, false));
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false)
{
    $type = $type ? 1 : 0;
    static $ip = null;
    if ($ip !== null) {
        return $ip[$type];
    }
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}


/**
 * 生成商品spec唯一编码
 * @return string
 */
function create_store_Handle_sn()
{
    $serial_num = mb_substr(date('Ymd'), 0, 8, 'utf-8') . substr(time() . '', -4);
    $info = model('StoreHandle')->where(array('handle_sn' => $serial_num))->find();
    return $info ? create_store_Handle_sn() : $serial_num;
}

//生成唯一订单号
function getOrderNum($table = '', $column = 'order_sn', $orderPrefix = 'C')
{
    $orderPrefix .= createOrderCheckClientType();
    $order_code = $orderPrefix .
        mb_substr(date('Ymd'), 0, 8, 'utf-8') .
        sprintf("%04s", rand(0, intval(substr(time() . '', -4))));
    $info = Db::name($table)->where([$column => $order_code])->find();
    return $info ? getOrderNum($table, $column, $orderPrefix) : $order_code;
}

function apiHandleReturn($code, $message, $errorCode = 500, $trace = null)
{
    $data = ['errorCode' => $errorCode];
    if ($trace) {
        $data['trace'] = $trace;
    }
    $result = [
        'code' => $code,
        'msg' => $message,
        'time' => time(),
        'data' => $data,
        'errorCode' => $errorCode,
    ];
    return json($result);
}

function sprintfPointsTwoPlace($num)
{
    return sprintf("%.2f", substr(sprintf("%.3f", $num), 0, -1));
}

/**
 * 邮件发送
 */
function sendEmail($un, $pwd, $target, $title, $content, $target_name = '嘿嘿')
{
    $mail = new PHPMailer\PHPMailer\PHPMailer();           //实例化PHPMailer对象
    $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    // 设定使用SMTP服务
    $mail->SMTPDebug = 1;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';          // 使用安全协议
    $mail->Host = 'smtp.qq.com';        // SMTP 服务器
    $mail->Port = 465;                  // SMTP服务器的端口号
    $mail->Username = $un;    // SMTP服务器用户名
    $mail->Password = $pwd;     // SMTP服务器密码
    $mail->SetFrom($un, '西谷后台');
    $mail->Subject = $title;    //邮件标题
    $mail->Body = $content;     //邮件内容
    $mail->IsHTML(true);
    $mail->AddAddress($target, $target_name);
    return $mail->Send() ? true : $mail->ErrorInfo;
}

/**
 * @param float $total 可砍掉的总金额(T)
 * @param integer $tn 总砍价次数(K)
 * @param float $pfn 前N个人比例
 * @param int $fn 前N个人
 * @param string $key 前N个人
 * @throws Exception
 */
function getReduceList($total, $tn, $pfn, $fn, $key)
{
    $tool = new \app\common\library\BargainTool($pfn, $fn, $key);
    return $tool->getReduceList($total, $tn);
}

if (!function_exists('round2point')) {
    /*
     * @保留两位小数 处理精度问题
     *
     * @param float $num
     *
     */
    function round2point($num)
    {
        return sprintf('%.2f', round($num, 2));
    }
}

if (!function_exists('createOrderPaySn')) {
    /*
     * @生成订单流水号
     *
     *
     *
     */
    function createOrderPaySn()
    {
        return 'PAY' . date('YmdHis') . mt_rand(1000, 9999);
    }
}

//返回当前的毫秒时间戳
if (!function_exists('get_msectime')) {
    function get_msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
}

if (!function_exists('auth')) {
    /**
     * Get the available auth instance.
     *
     * @return \app\common\library\Auth
     */
    function auth()
    {
        return app(\app\common\library\Auth::class);
    }
}

function downloadImageFromUrl($url, $path = "./Download/images/")
{
    // 因为不知道最后接受到的文件是什么格式，先建立一个临时文件，用于保存
    $tmpFile = tempnam(sys_get_temp_dir(), 'image');
    # 文件下载 BEGIN #
    // 打开临时文件，用于写入（w),b二进制文件
    $resource = fopen($tmpFile, 'wb');
    // 初始化curl
    $curl = curl_init($url);
    // 设置输出文件为刚打开的
    curl_setopt($curl, CURLOPT_FILE, $resource);
    // 不需要头文件
    curl_setopt($curl, CURLOPT_HEADER, 0);
    // 执行
    curl_exec($curl);
    // 关闭curl
    curl_close($curl);
    // 关闭文件
    fclose($resource);
    # 文件下载 END #
    // 获取文件类型
    if (function_exists('exif_imagetype')) {
        // 读取一个图像的第一个字节并检查其签名(这里需要打开mbstring及php_exif)
        $fileType = exif_imagetype($tmpFile);
    } else {
        // 获取文件大小，里面第二个参数是文件类型 （这里后缀可以直接通过getimagesize($url)来获取，但是很慢）
        $fileInfo = getimagesize($tmpFile);
        $fileType = $fileInfo[2];
    }
    // 根据文件类型获取后缀名
    $extension = image_type_to_extension($fileType);
    // 计算指定文件的 MD5 散列值，作为保存的文件名，重复下载同一个文件不会产生重复保存，相同的文件散列值相同
    $md5FileName = md5_file($tmpFile);
    // 最终保存的文件
    $returnFile = $path . $md5FileName . $extension;
    // 检查传过来的路径是否存在，不存在就创建
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
    // 复制临时文件到最终保存的文件中
    copy($tmpFile, $returnFile);
    // 释放临时文件
    @unlink($tmpFile);
    // 返回保存的文件路径
    return $returnFile;
}

/**
 * @param $url
 * @param $param
 * @return bool|string
 */
function curlRemote($url, $param)
{
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    //post提交的数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    return $data;
}

/**
 * @param $url
 * @return 判断远程图片是否存在
 */
function img_exits($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, 1); // 不下载
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if (curl_exec($ch) !== false) {
        return true;
    } else {
        return false;
    }
}

function getReduceListNew($total,$tn,$fn,$key = ''){
    $tool = new \app\common\library\BargainToolNew($fn, $key);
    if(array_sum(array_column($fn,'before')) > $tn){
        throw new \Exception("前排砍价刀数不能大于总砍价刀数");
    }
    if($tn < 2 ){
        throw new \Exception("总砍价刀数不能小于2");
    }
    return $tool->getReduceList($total, $tn);
}

