<?php
namespace app\index\model;
use	think\Model;
class Account extends Model{
    //资金账户表
    protected $type = [
        'createtime'=>'timestamp:Y-m-d',
        'more'    =>  'json'
    ];
    
    //期初余额_读取器
	protected function  getInitialAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//资金余额_读取器
	protected function  getBalanceAttr ($val,$data){
	    return opt_decimal($val);
	}
}
