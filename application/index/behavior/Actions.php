<?php
namespace app\index\behavior;
use think\Hook;
use app\index\model\Action;
class Actions{
    public function run(){
        // 加入命名空间
        \think\Loader::addNamespace('addons', ROOT_PATH.'addons'.DS);
        // 获取插件数据
        $actions=Action::with('pidinfo')->where(['pid'=>['neq',0],'state'=>1])->select();
        foreach ($actions as $actions_vo) {
            Hook::add($actions_vo['pidinfo']['value'],$actions_vo['value']); 
        }
    }
}