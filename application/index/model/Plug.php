<?php
namespace app\index\model;
use	think\Model;
class Plug extends Model{
    //插件表
    
    //启用状态_读取器
	protected function  getStateAttr ($val,$data){
        $arr=['0'=>'停用','1'=>'正常'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
	
	//配置信息_读取器
	protected function  getConfigAttr ($val,$data){
        return json_decode($val,true);
	}
    
}
