<?php
namespace app\index\model;
use	think\Model;
class Room extends Model{
    //仓储信息
    
    //仓库属性关联
    public function warehouseinfo(){
        return $this->hasOne('Warehouse','id','warehouse');
    }
    
    //商品属性关联
    public function goodsinfo(){
        return $this->hasOne('Goods','id','goods')->with('classinfo,unitinfo,brandinfo,warehouseinfo,attrinfo');
    }
    
    //串码属性关联
    public function serialinfo(){
        return $this->hasMany('Serial','room','id');
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
}
