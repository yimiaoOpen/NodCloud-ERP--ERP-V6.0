<?php
namespace app\index\model;
use	think\Model;
class Eftinfo extends Model{
    //资金调拨单详情表
    protected $type = [
        'more'    =>  'json'
    ];
    
    //调出资金账户属性关联
    public function accountinfo(){
        return $this->hasOne('Account','id','account');
    }
    
    //调入资金账户属性关联
    public function toaccountinfo(){
        return $this->hasOne('Account','id','toaccount');
    }
    
	//金额_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}
}
