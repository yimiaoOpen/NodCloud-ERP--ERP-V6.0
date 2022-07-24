<?php
namespace app\index\model;
use	think\Model;
class Recashierinfo extends Model{
    //零售退货单详情表
    protected $type = [
        'more'    =>  'json'
    ];
    
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
        return $this->hasOne('Room','id','room')->with('serialinfo');
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
}
