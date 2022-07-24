<?php
namespace app\index\controller;
use think\Controller;
use think\Hook;
use	app\index\model\User;
class Index extends Controller{
    //登录
    public function index(){
        if(checklogin()){
            header("Location: http://".$_SERVER['HTTP_HOST']."/main.html"); 
            exit;
        }else{
            $this->assign('ver',get_ver());
            $this->assign('sys_name',get_sys(['sys_name']));
            return $this->fetch();
        }
    }
    //验证用户账号密码
    public function check_user(){
        $input=input('post.');
        if(isset_full($input,'user')&&isset_full($input,'pwd')){
            $sql=get_sql($input,['pwd'=>'md5'],'user');//构造SQL
            $user = User::get($sql);
            if($user){
                $token=user_token();
                $user->token=$token;
                $user->save();
                //设置登录
                cookie('Nod_User_Id',$user['id']);
                cookie('Nod_User_Token',$token);
                Session('is_user_id',$user['id']);
                Session('is_merchant_id',$user['merchant']);
                push_log('登录系统成功');//日志
                Hook::listen('login_success',$user);//登录成功行为
                //通用操作-开始
                del_time_file ('skin/upload/xlsx/');//删除超时文件
                del_time_file ('skin/images/code/');//删除超时文件
                //通用操作-结束
                return json(['state'=>'success']);
            }else{
                $hookinfo=['user'=>$input['user'],'pwd'=>$input['pwd']];//钩子信息
                Hook::listen('login_error',$hookinfo);//登录失败行为
                return json(['state'=>'error','info'=>'账号或密码错误,请核实!']);
            }
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
    //退出登录
    public function out(){
        $user_id=Session('is_user_id');
        Hook::listen('sys_out',$user_id);//登录成功行为
        $Backup=controller('Backup');
        $Backup->new_backup();
        Cache(null);
        Session(null);
        cookie(null,'Nod_');
        header('Location: '.'http://'.$_SERVER['HTTP_HOST']);
        exit;
    }
}
