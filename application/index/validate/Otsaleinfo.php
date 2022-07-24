<?php
namespace app\index\validate;
use think\Validate;
class Otsaleinfo extends Validate{
    //默认创建规则
    protected $rule = [
        ['room', 'require|integer', '仓储数据不可为空!|仓储数据不正确!'],
        ['goods', 'require|integer', '商品数据不可为空!|商品数据不正确!'],
        ['warehouse', 'require|integer', '仓库数据不可为空!|仓库数据不正确!'],
        ['nums', 'require|number', '数量数据不可为空!|数量数据不正确!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
}