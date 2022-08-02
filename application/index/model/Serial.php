<?php
namespace app\index\model;
use	think\Model;
class Serial extends Model{
    //串码表
	
	//商品属性关联
    public function goodsinfo(){
        return $this->hasOne('Goods','id','goods');
    }
    
    //仓储属性关联
    public function roominfo(){
        return $this->hasOne('Room','id','room');
    }
	
	//条码类型_读取器
	protected function  getTypeAttr ($val,$data){
        $arr=['0'=>'未销售','1'=>'已销售','2'=>'不在库'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
}
