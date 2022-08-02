<?php
namespace app\index\validate;
use think\Validate;
class Rpurchaseinfo extends Validate{
    //默认创建规则
    protected $rule = [
        ['id', 'require|integer', '详情ID不可为空!|详情ID数据不正确!'],
        ['warehouse', 'require|integer', '仓库数据不可为空!|仓库数据不正确!'],
        ['nums', 'require|number', '数量数据不可为空!|数量数据不正确!'],
        ['price', 'require|number', '单价数据不可为空!|单价数据不正确!'],
        ['total', 'require|number', '总价数据不可为空!|总价数据不正确!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
}