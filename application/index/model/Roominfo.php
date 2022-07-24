<?php
namespace app\index\model;
use	think\Model;
class Roominfo extends Model{
    //仓储详情
    
	//单据类型_读取器
	protected function  getTypeAttr ($val,$data){
        $arr=['1'=>'购货单','2'=>'销货单','3'=>'购货退货单','4'=>'销货退货单','5'=>'调拨单-出','6'=>'调拨单-入','7'=>'其他入库单','8'=>'其他出库单','9'=>'零售单','10'=>'零售退货单','11'=>'采购入库单','12'=>'积分兑换单'];
        $re['trend']=in_array($val,[1,4,6,7,10,11])?'增加':'减少';
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
            	'2'	    =>	'Saleclass',//销货单
            	'3'	    =>	'Repurchaseclass',//购货退货单
            	'4'	    =>	'Resaleclass',//销货退货单
            	'5'	    =>	'Allocationclass',//调拨单-出
            	'6'	    =>	'Allocationclass',//调拨单-入
            	'7'	    =>	'Otpurchaseclass',//其他入库单
            	'8'	    =>	'Otsaleclass',//其他出库单
            	'9'     =>	'Cashierclass',//零售单
            	'10'	=>	'Recashierclass',//零售退货单
            	'11'	=>	'Rpurchaseclass',//采购入库单
            	'12'	=>	'Exchangeclass'//积分兑换单
        	]
        );
    }
    //数量_读取器
	protected function  getNumsAttr ($val,$data){
	    return opt_decimal($val);
	}
}
