<?php
namespace app\index\validate;
use think\Validate;
class Attribute extends Validate{
    //默认创建规则
    protected $rule = [
        ['name', 'require|RepeatName:create', '辅助属性名称不可为空!|字段数据重复'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'name'=>'require|RepeatName:update',
            'more'
        ]
    ];
    //辅助属性重复性判断
    protected function RepeatName($val,$rule,$data){
        $sql['name']=$val;
        $sql['pid']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('attribute')->where($sql)->find();
        return empty($nod)?true:'辅助属性名称[ '.$val.' ]已存在!';
    }
}