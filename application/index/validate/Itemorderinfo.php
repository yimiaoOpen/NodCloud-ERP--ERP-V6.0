<?php
namespace app\index\validate;
use think\Validate;
class Itemorderinfo extends Validate{
    //默认创建规则
    protected $rule = [
        ['serve', 'require|integer', '服务数据不可为空!|服务数据不正确!'],
        ['nums', 'require|number', '数量数据不可为空!|数量数据不正确!'],
        ['price', 'require|number', '单价数据不可为空!|单价数据不正确!'],
        ['total', 'require|number', '总价数据不可为空!|总价数据不正确!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
}