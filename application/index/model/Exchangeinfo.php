<?php
namespace app\index\model;
use	think\Model;
class Exchangeinfo extends Model{
    //积分兑换详情表
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
	
	//兑换积分_读取器
	protected function  getIntegralAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//总积分_读取器
	protected function  getAllintegralAttr ($val,$data){
	    return opt_decimal($val);
	}
}
