<?php
namespace addons\pagemore\validate;
use think\Validate;
class Pagemore extends Validate{
    //默认创建规则
    protected $rule = [
        ['name', 'require|RepeatName:create', '配置名称不可为空!|字段数据重复'],
        ['type', 'require', '配置类型不可为空!'],
        ['info', 'require', '配置内容不可为空!'],
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'name'=>'require|RepeatName:update',
            'type',
            'info'
        ]
    ];
    //重复性判断
    protected function RepeatName($val,$rule,$data){
        $sql['name']=$val;
        $sql['type']=$data['type'];
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('pagemore')->where($sql)->find();
        return empty($nod)?true:'配置项[ '.$val.' | '.$data['type'].']已存在!';
    }
}