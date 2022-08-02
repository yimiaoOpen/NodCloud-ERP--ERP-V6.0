<?php
namespace addons\loginerror\model;
use	think\Model;
class loginerror extends Model{
    //登陆失败记录
    protected $type = [
        'time'=>'timestamp:Y-m-d'
    ];
}
