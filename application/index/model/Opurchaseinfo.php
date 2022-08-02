<?php
namespace app\index\model;
use	think\Model;
class Opurchaseinfo extends Model{
    //采购订单详情表
    protected $type = [
        'more'    =>  'json'
    ];
    
    //商品属性关联
    public function goodsinfo(){
        return $this->hasOne('Goods','id','goods')->with('classinfo,unitinfo,brandinfo,warehouseinfo,attrinfo');
    }
    
    //辅助属性_读取器
	protected function  getAttrAttr($val,$data){
        $re['name']=empty($val)?'':attr_name($val);
        $re['nod']=$val;
        return $re;
	}
	
    //采购总数量_读取器
	protected function  getNumsAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//已采购数量_读取器
	protected function  getReadynumsAttr ($val,$data){
	    return opt_decimal($val);
	}
}
