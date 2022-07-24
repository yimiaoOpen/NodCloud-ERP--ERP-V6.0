<?php
namespace app\index\controller;
use think\Controller;
use think\Hook;
class Acl extends Controller{
    //访问控制
    public function _initialize() {
        if (checklogin()) {
            
        }else{
            //判断接口访问
            $input=input('get.');
            if(isset($input['apitime']) && isset($input['apikey'])){
                //验证秘钥是否超时
                if($input['apitime'] + 60 > time()){
                    //验证秘钥
                    if(get_plug_key($input['apitime'])!=$input['apikey']){
                        echo json_encode(['state'=>'error','info'=>'秘钥错误,拒绝访问!']);
                        exit;
                    }
                }else{
                    echo json_encode(['state'=>'error','info'=>'秘钥有效期超时!']);
                    exit;
                }
            }else{
                header("Location: http://".$_SERVER['HTTP_HOST']);
                exit;
            }
        }
    }
}