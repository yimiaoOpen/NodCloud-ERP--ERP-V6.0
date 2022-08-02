<?php
namespace app\index\validate;
use think\Validate;
class Formfield extends Validate{
    //默认创建规则
    protected $rule = [
        ['name', 'require', '表单名称不可为空!'],
        ['key', 'require|RepeatKey:create', '表单标识不可为空!|字段数据重复'],
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'key'=>'require|RepeatKey:update'
        ],
    ];
    //表单标识重复性判断
    protected function RepeatKey($val,$rule,$data){
        $sql['key']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('formfield')->where($sql)->find();
        return empty($nod)?true:'表单标识[ '.$val.' ]已存在!';
    }
    
    
}