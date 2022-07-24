<?php
namespace app\index\model;
use	think\Model;
class Serve extends Model{
    //服务项目表
    protected $type = [
        'more'    =>  'json'
    ];
    
	//服务价格_读取器
	protected function  getPriceAttr ($val,$data){
	    return opt_decimal($val);
	}
}
