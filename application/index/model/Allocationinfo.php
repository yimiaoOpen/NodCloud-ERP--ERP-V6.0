<?php
namespace app\index\model;
use	think\Model;
class Allocationinfo extends Model{
    //调拨单详情表
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
    
	//调拨仓库属性关联
    public function towarehouseinfo(){
        return $this->hasOne('Warehouse','id','towarehouse');
    }
    
	//调拨仓储属性关联
    public function toroominfo(){
        return $this->hasOne('Room','id','toroom')->with('serialinfo');
    }
    
    //数量_读取器
	protected function  getNumsAttr ($val,$data){
	    return opt_decimal($val);
	}
}
