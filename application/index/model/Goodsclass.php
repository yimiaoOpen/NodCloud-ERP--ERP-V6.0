<?php
namespace app\index\model;
use	think\Model;
class Goodsclass extends Model{
    //商品分类表
    
    //父属性关联
    public function pidinfo(){
        return $this->hasOne('Goodsclass','id','pid');
    }
}
