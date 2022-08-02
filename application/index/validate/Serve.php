<?php
namespace app\index\validate;
use think\Validate;
class Serve extends Validate{
    //默认创建规则
    protected $rule = [
        ['name', 'require|RepeatName:create', '服务项目名称不可为空!|字段数据重复'],
        ['number', 'RepeatNumber:create', '字段数据重复'],
        ['price', 'number', '期初余额格式错误!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'name'=>'require|RepeatName:update',
            'number'=>'RepeatNumber:update',
            'price',
            'more'
        ]
    ];
    //服务项目名称重复性判断
    protected function RepeatName($val,$rule,$data){
        $sql['name']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('serve')->where($sql)->find();
        return empty($nod)?true:'服务项目名称[ '.$val.' ]已存在!';
    }
    //服务项目编号重复性判断
    protected function RepeatNumber($val,$rule,$data){
        $sql['number']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('serve')->where($sql)->find();
        return empty($nod)?true:'服务项目编号[ '.$val.' ]已存在!';
    }
}