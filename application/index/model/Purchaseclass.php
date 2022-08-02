<?php
namespace app\index\model;
use	think\Model;
class Purchaseclass extends Model{
    //购货单
    protected $type = [
        'time'=>'timestamp:Y-m-d',
        'auditingtime'=>'timestamp:Y-m-d H:i:s',
        'more'    =>  'json'
    ];
    
    //商户属性关联
    public function merchantinfo(){
        return $this->hasOne('Merchant','id','merchant');
    }
    
    //供应商属性关联
    public function supplierinfo(){
        return $this->hasOne('Supplier','id','supplier');
    }
    
    //制单人属性关联
    public function userinfo(){
        return $this->hasOne('User','id','user');
    }
    
    //结算账户属性关联
    public function accountinfo(){
        return $this->hasOne('Account','id','account');
    }
    
    //审核人属性关联
    public function auditinguserinfo(){
        return $this->hasOne('User','id','auditinguser');
    }
    
    //单据日期_设置器
	protected function  setTimeAttr ($val){
		return strtotime($val);
	}
    
    //审核状态_读取器
	protected function  getTypeAttr ($val,$data){
        $arr=['0'=>'未审核','1'=>'已审核'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
	
	//核销类型_读取器
	protected function  getBilltypeAttr ($val,$data){
        $arr=['-1'=>'未处理','0'=>'未核销','1'=>'部分核销','2'=>'已核销','3'=>'强制核销'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
	
    //单据金额_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//实际金额_读取器
	protected function  getActualAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//实付金额_读取器
	protected function  getMoneyAttr ($val,$data){
	    return opt_decimal($val);
	}
}
