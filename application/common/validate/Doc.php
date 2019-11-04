<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 17/7/27
 * Time: 下午5:25
 */

namespace app\common\validate;

use think\Validate;

class Doc extends Validate
{

    protected $rule = [
        'title' => 'require',
        'sort' => 'number',
        'summary' => 'require',
        'detail' => 'require',
    ];
    protected $message = [
        'title.require' => '标题必须填写',
        'summary.require' => '简述必填',
        'detail.require' => '详情必填',
        'sort.number' => '排序必须为数字',
    ];


    protected $scene = [
        'edit_sort'  =>  ['sort'],
    ];
}