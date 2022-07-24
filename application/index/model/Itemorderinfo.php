<?php
namespace app\index\model;
use	think\Model;
class Itemorderinfo extends Model{
    //服务单详情表
    protected $type = [
        'more'    =>  'json'
    ];
    
    //服务属性关联
    public function serveinfo(){
        return $this->hasOne('Serve','id','serve');
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
