<?php
namespace addons\formmore\validate;
use think\Validate;
class Formmore extends Validate{
    //默认创建规则
    protected $rule = [
        ['name', 'require|RepeatName:create', '配置名称不可为空!|字段数据重复'],
        ['info', 'require', '配置内容不可为空!'],
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'name'=>'require|RepeatName:update',
            'info'
        ]
    ];
    //重复性判断
    protected function RepeatName($val,$rule,$data){
        $sql['name']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('formmore')->where($sql)->find();
        return empty($nod)?true:'配置名称[ '.$val.']已存在!';
    }
}