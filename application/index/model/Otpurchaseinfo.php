<?php
namespace app\index\model;
use	think\Model;
class Otpurchaseinfo extends Model{
    //其他入库单详情表
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
        return $this->hasOne('Room','id','room');
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
