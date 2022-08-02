<?php
namespace app \index \controller ;
use app\index\controller\Acl;
use app\index\model\Often as Oftens;
use app\index\model\Menu;
class Often extends Acl {
    //常用功能模块
    //---------------(^_^)---------------//
    //常用功能视图
    public function main(){
        return $this->fetch();
    }
    //常用功能列表
    public function often_list(){
        $info=Menu::where(['pid'=>['neq',0],'url'=>['neq','/']])->select()->ToArray();
        foreach ($info as $key=>$vo) {
            $check=Oftens::where(['set'=>$vo['url']])->find();
            $info[$key]['checked']=empty($check)?false:true;
        }
        $resule=['state'=>'success','info'=>$info];
        return json($resule);
    }
    //保存常用功能列表
    public function set_often(){
        $input=input('post.',null,'html_entity_decode');//兼容XSS防护
        if(isset_full($input,'arr')){
            Oftens::where(['id'=>['gt',0]])->delete();//初始化数据表
            Oftens::insertAll(json_decode($input['arr'],true));//解码兼容空数组
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}