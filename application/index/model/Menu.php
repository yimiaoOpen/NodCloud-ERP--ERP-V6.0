<?php
namespace app\index\model;
use	think\Model;
class Menu extends Model{
    //菜单表
    
    //父属性关联
    public function pidinfo(){
        return $this->hasOne('Menu','id','pid');
    }
    
    //子属性关联
    public function subinfo(){
        return $this->hasOne('Menu','pid','id');
    }
    
    //菜单类型_读取器
	protected function  getTypeAttr ($val,$data){
        $arr=['0'=>'独立菜单','1'=>'附属菜单'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
	
	//跳转类型_读取器
	protected function  getJumpAttr ($val,$data){
        $arr=['0'=>'iframe模式','1'=>'独立窗口'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
    
}
