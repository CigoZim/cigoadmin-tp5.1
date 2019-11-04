<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 17/7/27
 * Time: 下午5:25
 */

namespace app\common\validate;

use think\Validate;

class Adv extends Validate
{

    protected $rule = [
        'position' => 'require',
        'title' => 'require',
        'target_type' => 'require',
        'img' => 'require',
        'start_time' => 'require',
        'end_time' => 'require',
    ];
    protected $message = [
        'position.require' => '广告位置不能为空',
        'title.require' => '标题必须填写',
        'target_type.require' => '目标数据类型必须填写',
        'img.require' => '请上传图片',
        'start_time.require' => '开始时间必须填写',
        'end_time.require' => '结束时间必须填写',
    ];

    protected $scene = [
        'edit_sort'  =>  ['sort'],
    ];
}