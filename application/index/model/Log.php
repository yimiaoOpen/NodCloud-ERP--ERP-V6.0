<?php
namespace app\index\model;
use	think\Model;
class Log extends Model{
    //日志表
    protected $type = [
        'time'=>'timestamp:Y-m-d H:i:s',
    ];
    
    //商户属性关联
    public function merchantinfo(){
        return $this->hasOne('Merchant','id','merchant');
    }
    
    //用户属性关联
    public function userinfo(){
        return $this->hasOne('User','id','user');
    }
    
}
