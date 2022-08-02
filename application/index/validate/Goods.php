<?php
namespace app\index\validate;
use think\Validate;
class Goods extends Validate{
    //正则规则
    protected $regex;
    public function __construct() {
        $this->regex= ['plus' => get_regex('plus')];
    }
    //默认创建规则
    protected $rule = [
        ['name', 'require|RepeatName:create', '商品名称不可为空!|字段数据重复'],
        ['number', 'RepeatNumber:create', '字段数据重复'],
        ['class', 'require', '商品分类不可为空!'],
        ['buy', 'require|regex:plus', '购货价格不可为空!|购货价格不正确!'],
        ['sell', 'require|regex:plus', '销货价格不可为空!|销货价格不正确!'],
        ['retail', 'require|regex:plus', '零售价格不可为空!|零售价格不正确!'],
        ['integral', 'regex:plus', '兑换积分不正确!'],
        ['code', 'alphaDash', '条形码不正确!'],
        ['stocktip', 'regex:plus', '库存阈值不正确!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'name'=>'require|RepeatName:update',
            'number'=>'RepeatNumber:update',
            'class',
            'buy',
            'sell',
            'retail',
            'integral',
            'code',
            'stocktip',
            'more'
        ]
    ];
    //商品名称重复性判断
    protected function RepeatName($val,$rule,$data){
        $sql['name']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('goods')->where($sql)->find();
        return empty($nod)?true:'商品名称[ '.$val.' ]已存在!';
    }
    //商品编号重复性判断
    protected function RepeatNumber($val,$rule,$data){
        $sql['number']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('goods')->where($sql)->find();
        return empty($nod)?true:'商品编号[ '.$val.' ]已存在!';
    }
}