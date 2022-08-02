<?php
namespace app\index\model;
use	think\Model;
class Action extends Model{
    //菜单表
    
    //父信息属性关联
    public function pidinfo(){
        return $this->hasOne('Action','id','pid');
    }
    
}
