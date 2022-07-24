<?php
namespace addons\speed\controller;
use think\Controller;
use app\index\model\Plug;
class View extends Controller{
    // 说明
    public function about(){
        return $this->fetch(get_plug_view('speed','about.html'));
    }
    // 说明
    public function config(){
        $plug = Plug::where(['only'=>'speed'])->find();
        $this->assign('plug',$plug);
        return $this->fetch(get_plug_view('speed','config.html'));
    }
}