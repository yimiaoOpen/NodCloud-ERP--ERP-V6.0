<?php
namespace app\index\validate;
use think\Validate;
class Eftinfo extends Validate{
    //默认创建规则
    protected $rule = [
        ['account', 'require|integer', '调出资金账户数据不可为空!|调出资金账户数据不正确!'],
        ['toaccount', 'require|integer', '调入资金账户数据不可为空!|调入资金账户数据不正确!'],
        ['total', 'require|number', '结算金额数据不可为空!|结算金额数据不正确!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
}