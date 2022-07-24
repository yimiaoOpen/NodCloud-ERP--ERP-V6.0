<?php
namespace app\index\model;
use	think\Model;
class Summary extends Model{
    //数据汇总表
    
    //时间自动转换
	protected $type=['time'=>'timestamp:Y-m-d'];
	
	//商户属性关联
    public function merchantinfo(){
        return $this->hasOne('Merchant','id','merchant');
    }
    
    //供应商属性关联
    public function supplierinfo(){
        return $this->hasOne('Supplier','id','supplier');
    }
    
    //客户属性关联
    public function Customerinfo(){
        return $this->hasOne('Customer','id','customer');
    }
    
    //商品属性关联
    public function goodsinfo(){
        return $this->hasOne('Goods','id','goods')->with('classinfo,unitinfo,brandinfo,warehouseinfo,attrinfo');
    }
    
    //仓库属性关联
    public function warehouseinfo(){
        return $this->hasOne('Warehouse','id','warehouse');
    }
    
    //仓储属性关联
    public function roominfo(){
        return $this->hasOne('Room','id','room');
    }
    
    //制单人属性关联
    public function userinfo(){
        return $this->hasOne('User','id','user');
    }
    
    //辅助属性_读取器
	protected function  getAttrAttr($val,$data){
        $re['name']=empty($val)?'':attr_name($val);
        $re['nod']=$val;
        return $re;
	}
	
    //数量_读取器
	protected function  getNumsAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//单价_读取器
	protected function  getPriceAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//总价_读取器
	protected function  getTotalAttr ($val,$data){
	    return opt_decimal($val);
	}
    
	//单据类型_读取器
	protected function  getTypeAttr ($val,$data){
        $arr=['1'=>'购货单','2'=>'采购入库单','3'=>'购货退货单','4'=>'销货单','5'=>'销货退货单','6'=>'零售单','7'=>'零售退货单','8'=>'积分兑换单','9'=>'调拨单','10'=>'其他入库单','11'=>'其他出库单'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
	
    //单据类型_原始数据
	protected function  getTypenodAttr ($val,$data){
        return $data['type'];//返回原始数据，解决多态关联多维数组问题
	}
	
	//单据类型多态关联
    public function typedata(){
        return $this->morphTo(
            ['typenod','class'],
            [
            	'1'	    =>	'Purchaseclass',//购货单
            	'2'	    =>	'Rpurchaseclass',//采购入库单
            	'3'	    =>	'Repurchaseclass',//购货退货单
            	'4'	    =>	'Saleclass',//销货单
            	'5'	    =>	'Resaleclass',//销货退货单
            	'6'	    =>	'Cashierclass',//零售单
            	'7'	    =>	'Recashierclass',//零售退货单
            	'8'	    =>	'Exchangeclass',//积分兑换单
            	'9'     =>	'Allocationclass',//调拨单
            	'10'	=>	'Otpurchaseclass',//其他入库单
            	'11'	=>	'Otsaleclass',//其他出库单
        	]
        );
    }
    
    //结算账户多态关联
    public function accountdata(){
        return $this->morphTo(
            ['typenod','account'],
            [
            	'1'	    =>	'Account',//购货单-结算账户
            	'2'	    =>	'Account',//采购入库单-结算账户
            	'3'	    =>	'Account',//购货退货单-结算账户
            	'4'	    =>	'Account',//销货单-结算账户
            	'5'	    =>	'Account',//销货退货单-结算账户
            	'6'	    =>	'Tmpmodel',//零售单-兼容表
            	'7'	    =>	'Account',//零售退货单-结算账户
            	'8'	    =>	'Tmpmodel',//积分兑换单-兼容表
            	'9'     =>	'Tmpmodel',//调拨单-兼容表
            	'10'	=>	'Tmpmodel',//其他入库单-兼容表
            	'11'	=>	'Tmpmodel',//其他出库单-兼容表
        	]
        );
    }
}
