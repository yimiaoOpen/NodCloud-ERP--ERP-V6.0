<?php
namespace app\index\validate;
use think\Validate;
class Otsaleclass extends Validate{
    //默认创建规则
    protected $rule = [
        ['merchant', 'require|integer', '所属商户不可为空!|所属商户数据不正确!'],
        ['time', 'require|date', '单据时间不可为空|单据时间不正确!'],
        ['number', 'require|RepeatNumber:create', '单据编号不可为空!|字段数据重复'],
        ['pagetype', 'require|integer', '单据类型不可为空!|单据类型数据不正确!'],
        ['user', 'require|integer', '制单人不可为空!|制单人数据不正确!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'time',
            'number'=>'require|RepeatNumber:update',
            'pagetype',
            'user',
            'more'
        ]
    ];
    //单据编号重复性判断
    protected function RepeatNumber($val,$rule,$data){
        $sql['number']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('otsaleclass')->where($sql)->find();
        return empty($nod)?true:'单据编号[ '.$val.' ]已存在!';
    }
}