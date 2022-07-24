<?php
namespace app\index\model;
use	think\Model;
class User extends Model{
    //用户表
    protected $type = [
        'more'    =>  'json'
    ];
    //用户密码_设置器
	protected function  setPwdAttr ($val){
		return md5($val);
	}
    
    //商户属性关联
    public function merchantinfo(){
        return $this->hasOne('Merchant','id','merchant');
    }
}
