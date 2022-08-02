<?php
namespace app\index\validate;
use think\Validate;
class User extends Validate{
    //默认创建规则
    protected $rule = [
        ['user', 'require|RepeatUser:create', '职员账号不可为空!|字段数据重复'],
        ['pwd', 'require', '职员密码不可为空!'],
        ['merchant', 'require', '所属商户不可为空!'],
        ['name', 'require', '职员名称不可为空!'],
        ['more', 'array', '扩展信息格式不正确!']
    ];
    //场景规则
    protected $scene = [
        'update'  =>  [
            'user'=>'require|RepeatUser:update',
            'merchant',
            'name',
            'more'
        ]
    ];
    //职员账号重复性判断
    protected function RepeatUser($val,$rule,$data){
        $sql['user']=$val;
        $rule=='update'&&($sql['id']=['neq',$data['id']]);
        $nod=db('user')->where($sql)->find();
        return empty($nod)?true:'职员账号[ '.$val.' ]已存在!';
    }
}