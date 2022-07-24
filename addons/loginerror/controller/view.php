<?php
namespace addons\loginerror\controller;
use think\Controller;
class View extends Controller{
    // 说明
    public function about(){
        return $this->fetch(get_plug_view('loginerror','about.html'));
    }
    // 报表
    public function form(){
        
        return $this->fetch(get_plug_view('loginerror','form.html'));
    }
}