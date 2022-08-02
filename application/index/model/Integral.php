<?php
namespace app\index\model;
use	think\Model;
class Integral extends Model{
    //客户积分表
    
    //时间自动转换
	protected $type=['time'=>'timestamp:Y-m-d'];
    
    //积分_读取器
	protected function  getIntegralAttr ($val,$data){
	    return opt_decimal($val);
	}
	
	//积分操作_读取器
	protected function  getSetAttr ($val,$data){
        $arr=['0'=>'积分减少','1'=>'积分增加'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
	
	//制单人属性关联
    public function userinfo(){
        return $this->hasOne('User','id','user');
    }
    
	//来源类型_读取器
	protected function  getTypeAttr ($val,$data){
        $arr=['1'=>'零售单','2'=>'零售退货单','3'=>'人工操作','4'=>'积分兑换单'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
    //来源类型_原始数据
	protected function  getTypenodAttr ($val,$data){
        return $data['type'];//返回原始数据，解决多态关联多维数组问题
	}
	//来源类型多态关联
    public function typedata(){
        return $this->morphTo(
            ['typenod','class'],
            [
            	'1'	=>	'Cashierclass',//零售单
            	'2'	=>	'Recashierclass',//零售退货单
            	'3'	=>	'Tmpmodel',//人工操作
            	'4'	=>	'Exchangeclass',//积分兑换单
        	]
        );
    }
}
