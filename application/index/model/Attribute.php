<?php
namespace app\index\model;
use	think\Model;
class Attribute extends Model{
    //辅助属性表
    protected $type = [
        'more'    =>  'json'
    ];
    //子属性关联
    public function subinfo(){
        return $this->hasMany('Attribute','pid','id');
    }
}
