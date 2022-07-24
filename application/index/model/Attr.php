<?php
namespace app\index\model;
use	think\Model;
class Attr extends Model{
    //辅助属性表
    
    //购货价格_读取器
	protected function  getBuyAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//销货价格_读取器
	protected function  getSellAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//零售价格_读取器
	protected function  getRetailAttr ($val,$data){
	    return opt_decimal($val);
	}
}
