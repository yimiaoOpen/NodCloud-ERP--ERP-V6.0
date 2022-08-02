<?php
namespace app\index\model;
use	think\Model;
class Otpaymentinfo extends Model{
    //其他支出单详情表
    protected $type = [
        'more'    =>  'json'
    ];
    
    //资金账户属性关联
    public function accountinfo(){
        return $this->hasOne('Account','id','account');
    }
    
	//金额_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}
}
