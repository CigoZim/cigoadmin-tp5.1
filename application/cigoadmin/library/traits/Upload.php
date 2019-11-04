<?php

namespace app\cigoadmin\library\traits;

use app\cigoadmin\library\IResponse;
use app\cigoadmin\library\uploader\UploadMg;
use think\facade\Request;

/**
 * Trait Edit
 * @package app\admin\library\traits
 * @summary 负责后台管理中的文件上传操作
 */
trait Upload
{
    public function upload()
    {
        if (!Request::isPost()) {
            $this->error('访问异常!');
        }

        //1. 实例化上传类，并创建文件上传实例
        $upMg = new UploadMg();
        if (!$upMg->init()->makeFileUploader()) {
            $response = $upMg->response();
            $this->result(
                array(
                    IResponse::FLAG_DATA => $response[IResponse::FLAG_DATA],
                    IResponse::FLAG_ERRORCODE => $response[IResponse::FLAG_ERRORCODE]
                ),
                $response[IResponse::FLAG_STATUS],
                $response[IResponse::FLAG_MSG],
                'json'
            );
        }
        //2. 执行上传操作
        $upMg->doUpload();
        $response = $upMg->response();
        $this->result(
            array(
                IResponse::FLAG_DATA => $response[IResponse::FLAG_DATA],
                IResponse::FLAG_ERRORCODE => $response[IResponse::FLAG_ERRORCODE]
            ),
            $response[IResponse::FLAG_STATUS],
            $response[IResponse::FLAG_MSG],
            'json'
        );
    }

    public function imgArgsByToolsCropCommon()
    {
        return $this->fetch('cigoadmin@public:imgArgsByToolsCropCommon');
    }

    public function imgArgsByManual()
    {
        return $this->fetch('cigoadmin@public:imgArgsByManual');
    }
}