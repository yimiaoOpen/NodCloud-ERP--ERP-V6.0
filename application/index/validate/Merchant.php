<?php
namespace app\index\validate;
use think\Validate;
class Merchant extends Validate{
    //默认创建规则
    protected $rule = [
        ['name', 'require|RepeatName:create', '商户名称不可为空!|字段数据重复'],
        ['number', 'RepeatNumber:create', '字段数据重复'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'name'=>'require|RepeatName:update',
            'number'=>'RepeatNumber:update',
            'more'
        ]
    ];
    //商户名称重复性判断
    protected function RepeatName($val,$rule,$data){
        $sql['name']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('merchant')->where($sql)->find();
        return empty($nod)?true:'商户名称[ '.$val.' ]已存在!';
    }
    //商户编号重复性判断
    protected function RepeatNumber($val,$rule,$data){
        $sql['number']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('merchant')->where($sql)->find();
        return empty($nod)?true:'商户编号[ '.$val.' ]已存在!';
    }
    
    
}