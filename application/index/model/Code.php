<?php
namespace app\index\model;
use	think\Model;
class Code extends Model{
    //条码表
    protected $type = [
        'more'    =>  'json'
    ];
    
    //条码类型_读取器
	protected function  getTypeAttr ($val,$data){
        $arr=['0'=>'条形码','1'=>'二维码'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
}
