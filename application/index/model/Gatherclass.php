<?php
namespace app\index\model;
use	think\Model;
class Gatherclass extends Model{
    //收款单
    protected $type = [
        'time'=>'timestamp:Y-m-d',
        'auditingtime'=>'timestamp:Y-m-d H:i:s',
        'more'    =>  'json'
    ];
    
    //商户属性关联
    public function merchantinfo(){
        return $this->hasOne('Merchant','id','merchant');
    }
    
    //客户属性关联
    public function customerinfo(){
        return $this->hasOne('Customer','id','customer');
    }
    
    //制单人属性关联
    public function userinfo(){
        return $this->hasOne('User','id','user');
    }
    
    //审核人属性关联
    public function auditinguserinfo(){
        return $this->hasOne('User','id','auditinguser');
    }
    
    //单据日期_设置器
	protected function setTimeAttr ($val){
		return strtotime($val);
	}
    
    //审核状态_读取器
	protected function getTypeAttr ($val,$data){
        $arr=['0'=>'未审核','1'=>'已审核'];
        $re['name']=$arr[$val];
        $re['nod']=$val;
        return $re;
	}
}
