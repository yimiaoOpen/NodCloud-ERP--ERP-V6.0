<?php
namespace app\index\model;
use	think\Model;
class Customer extends Model{
    //客户表
    protected $type = [
        'birthday'=>'timestamp:Y-m-d',
        'more'    =>  'json'
    ];
    
    //客户生日_设置器
	protected function  setBirthdayAttr ($val){
		return strtotime($val);
	}
    
    //积分_读取器
	protected function  getIntegralAttr ($val,$data){
	    return opt_decimal($val);
	}
}
    