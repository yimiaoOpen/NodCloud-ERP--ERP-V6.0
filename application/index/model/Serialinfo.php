<?php
namespace app\index\model;
use	think\Model;
class Serialinfo extends Model{
    //串码详情
    
	//单据类型_读取器
	protected function  getTypeAttr ($val,$data){
        $arr=['1'=>'购货单','2'=>'销货单','3'=>'购货退货单','4'=>'销货退货单','5'=>'调拨单','6'=>'其他入库单','7'=>'其他出库单','8'=>'零售单','9'=>'零售退货单','10'=>'采购入库单','11'=>'积分兑换单'];
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
            	'5'	    =>	'Allocationclass',//调拨单
            	'6'	    =>	'Otpurchaseclass',//其他入库单
            	'7'	    =>	'Otsaleclass',//其他出库单
            	'8'	    =>	'Cashierclass',//零售单
            	'9'     =>	'Recashierclass',//零售退货单
            	'10'	=>	'Rpurchaseclass',//采购退货单
            	'11'	=>	'Exchangeclass',//积分兑换单
        	]
        );
    }
}
