<?php
namespace app \index \controller ;
use app\index\controller\Acl;
class Plug extends Acl {
    //插件访问扩展
    public function more(){
        $input=input('get.');
        //判断插件信息是否传入
        if(isset($input['plug_info'])){
            $info=explode("/",$input['plug_info']);
            //判断插件层级是否正确
            if(count($info)==3){
                $class="addons\\{$info[0]}\\controller\\{$info[1]}";
                //判断插件命名空间是否存在
                if(class_exists($class)){
                    //执行插件操作
                    $plug=controller($class);//实例化
                    $fun=$info[2];//执行方法
                    return $plug->$fun();
                }else{
                    return json (['state'=>'error','info'=>'插件命名空间不存在!']);
                }
            }else{
                return json (['state'=>'error','info'=>'插件信息错误!']);
            }
        }else{
            return json (['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
}