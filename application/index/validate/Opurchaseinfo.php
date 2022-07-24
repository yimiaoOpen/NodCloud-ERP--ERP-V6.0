<?php
namespace app\index\validate;
use think\Validate;
class Opurchaseinfo extends Validate{
    //默认创建规则
    protected $rule = [
        ['goods', 'require|integer', '商品数据不可为空!|商品数据不正确!'],
        ['nums', 'require|number', '数量数据不可为空!|数量数据不正确!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
}