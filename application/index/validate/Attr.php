<?php
namespace app\index\validate;
use think\Validate;
class Attr extends Validate{
    //正则规则
    protected $regex;
    public function __construct() {
        $this->regex= ['plus' => get_regex('plus')];
    }
    //默认创建规则
    protected $rule = [
        ['name', 'require', '属性名称不可为空!'],
        ['buy', 'require|regex:plus', '购货价格不可为空!|购货价格不正确!'],
        ['sell', 'require|regex:plus', '销货价格不可为空!|销货价格不正确!'],
        ['retail', 'require|regex:plus', '零售价格不可为空!|零售价格不正确!'],
        ['code', 'alphaDash', '条形码不正确!'],
        ['enable', 'require|in:0,1', '属性启用状态不正确!'],
    ];
}