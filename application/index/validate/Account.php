<?php
namespace app\index\validate;
use think\Validate;
class Account extends Validate{
    //默认创建规则
    protected $rule = [
        ['name', 'require|RepeatName:create', '资金账户名称不可为空!|字段数据重复'],
        ['number', 'RepeatNumber:create', '字段数据重复'],
        ['initial', 'number', '期初余额格式错误!'],
        ['createtime', 'date', '开账时间格式错误!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'name'=>'require|RepeatName:update',
            'number'=>'RepeatNumber:update',
            'initial',
            'createtime',
            'more'
        ]
    ];
    //资金账户名称重复性判断
    protected function RepeatName($val,$rule,$data){
        $sql['name']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('account')->where($sql)->find();
        return empty($nod)?true:'资金账户名称[ '.$val.' ]已存在!';
    }
    //资金账户编号重复性判断
    protected function RepeatNumber($val,$rule,$data){
        $sql['number']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('account')->where($sql)->find();
        return empty($nod)?true:'资金账户编号[ '.$val.' ]已存在!';
    }
}