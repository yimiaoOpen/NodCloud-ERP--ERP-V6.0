<?php
namespace addons\pagemore\controller;
use think\Controller;
use app\index\model\Plug;
class View extends Controller{
    // 说明
    public function about(){
        return $this->fetch(get_plug_view('pagemore','about.html'));
    }
    // 配置
    public function config(){
        return $this->fetch(get_plug_view('pagemore','config.html'));
    }
}