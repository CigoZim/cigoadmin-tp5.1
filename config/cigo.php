<?php
//TODO by zim
return [
    'AUTH_PWD_KEY' => 'adlfkjaskldf@$wsdfsl781734812^&HJK',
    'ICON_CONFIG_CIGO' => 'https://at.alicdn.com/t/font_224903_1putybtbaan',
    'ADMIN_LOGIN_TIMEOUT' => 24 * 60 * 60,//后台登陆超时时间
    'HOME_LOGIN_TIMEOUT' => 1 * 60 * 60,//后台登陆超时时间

    //模块列表
    'MODULE_LIST' => array(
        'index' => 0,
        'admin' => 1,
    ),
    'MODULE_LIST_TIP' => array(
        '0' => 'Home：前台',
        '1' => 'Admin：Admin后台',
    ),

    /* 文件上传相关配置 */
    'FILE_UPLOAD' => array(
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Upload/images', //保存根路径
        'waterImg' => './cigopublic/common/img/water.jpg',//图片水印图片路径
        'waterText' => '我是水印',//文字水印
        'waterTextFont' => './cigopublic/common/font/msyh.ttf',//文字水印字体路径
        'replace' => false, //存在同名是否覆盖
        'fileLimit' => array(
            'img' => array(
                'maxSize' => 10 * 1024 * 1024,
                'exts' => 'jpg,gif,png,jpeg',
            ),
            'video' => array(
                'maxSize' => 100 * 1024 * 1024,
                'exts' => 'mp4,rmvb,mov'
            ),
            'file' => array(
                'maxSize' => 100 * 1024 * 1024,
                'exts' => 'doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt,apk'
            )
        ),
    ), //文件上传相关配置（文件上传类配置）

    /* App接口数据加密通信密钥开头结尾 */
    'IF_ENCRYPT' => false,
    'PRIVATE_KEY_BEGIN' => '-----BEGIN PRIVATE KEY-----
',
    'PRIVATE_KEY_END' => '
-----END PRIVATE KEY-----',

    'PUBLIC_KEY_BEGIN' => '-----BEGIN PUBLIC KEY-----
',
    'PUBLIC_KEY_END' => '
-----END PUBLIC KEY-----',
    'AES_METHODS' => 'AES-128-ECB',
    'AES_KEY_LEN' => 32,
    'TOKEN_LEN' => 64,
    'TOKEN_TIMEOUT' => 30 * 24 * 60 * 60,
    'REQUEST_ALLOWABLE_TIME_INTERVAL' => Env::get('REQUEST_ALLOWABLE_TIME_INTERVAL', 5 * 60),

    //Auth认证
    'AUTH_CONFIG' => [
        //是否需要权限检查
        'auth_on' => true,
        //是否需要登录
        'NO_NEED_LOGIN' => [
            '/logout'
        ],
        //是否需要权限检查
        'NO_NEED_AUTH_CHECK' => [
            'Index/index',
            '/getDataList'
        ],
    ]

];
