<?php
namespace app\index\model;
use	think\Model;
class Purchasebill extends Model{
    //购货核销单
    
	protected $type=[
	    'time'=>'timestamp:Y-m-d H:i:s'
	 ];
	
	//资金账户属性关联
    public function accountinfo(){
        return $this->hasOne('Account','id','account');
    }
    
    //制单人属性关联
    public function userinfo(){
        return $this->hasOne('User','id','user');
    }
    
    //金额_读取器
	protected function  getMoneyAttr ($val,$data){
	    return opt_decimal($val);
	}
}
