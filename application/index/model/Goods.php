<?php
namespace app\index\model;
use	think\Model;
class Goods extends Model{
    //商品信息表
    protected $type = [
        'imgs'    =>  'json',
        'more'    =>  'json'
    ];
    
	//分类属性关联
    public function classinfo(){
        return $this->hasOne('Goodsclass','id','class');
    }
    
    //单位属性关联
    public function unitinfo(){
        return $this->hasOne('Unit','id','unit');
    }
    
    //品牌属性关联
    public function brandinfo(){
        return $this->hasOne('Brand','id','brand');
    }
    
    //仓库属性关联
    public function warehouseinfo(){
        return $this->hasOne('Warehouse','id','warehouse');
    }
    
    //辅助属性关联
    public function attrinfo(){
        return $this->hasMany('Attr','pid','id')->where(['enable'=>1]);
    }
    
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
    
    //积分_读取器
	protected function  getIntegralAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//库存阈值_读取器
	protected function  getStocktipAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//商品详情_设置器
	protected function  setDetailsAttr ($val){
	    return html_entity_decode($val);
	}
}
