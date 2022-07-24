<?php
namespace app\index\model;
use	think\Model;
class Formfield extends Model{
    //表单字段
    
    //子属性关联
    public function subinfo(){
        return $this->hasMany('Formfieldinfo','pid','id')->order('id asc');
    }
}
