<?php
namespace app \index \controller ;
use think\Request;
use app\index\controller\Acl;
use app\index\model\Prints as printss;
class Prints extends Acl {
    //打印服务模块
    //---------------(^_^)---------------//
    //更新信息
    public function set(){
        $input=input('post.');
        if(isset_full($input,'name') && isset($input['type']) && isset_full($input,'main')){
            $type=['0'=>'paper4','1'=>'paper2'];//初始化类型
            printss::where(['name'=>$input['name']])->update([$type[$input['type']]=>$input['main']]);
            push_log ('保存打印模板[ '.$input['name'].' ]');
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //更新信息
    public function recovery(){
        $input=input('post.');
        if(isset_full($input,'name') && isset($input['type'])){
            $type=['0'=>'paper4','1'=>'paper2'];//初始化类型
            $print=printss::where(['name'=>$input['name']])->find();
            printss::where(['id'=>$print['id']])->update([$type[$input['type']]=>$print[$type[$input['type']].'default']]);
            push_log ('恢复打印模板[ '.$input['name'].' ]');
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}