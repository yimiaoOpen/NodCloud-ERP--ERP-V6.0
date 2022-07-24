<?php
namespace addons\loginerror;
use think\Db;
use app\index\model\Action;
use app\index\model\Plug;
class Config {
    //插件标识
    public $only='loginerror';
    //插件入口
    public $entry='addons\loginerror\controller\main';
    //插件操作
    public $set=[
        ['title'=>'报表','parameter'=>'loginerror/view/form'],
        ['title'=>'说明','parameter'=>'loginerror/view/about']
    ];
    //插件信息
    public function info(){
        return [
            'name'=>'登陆失败记录',
            'info'=>'登录失败后将记录错误的账号密码。',
            'only'=>'loginerror',
            'ver'=>'1.0',
            'author'=>'NODCLOUD.COM'
        ];
    }
    //安装
    public function install(){
        //1.判断行为挂载点是否存在
        $action=Action::where(['value'=>'login_error','pid'=>0])->find();
        if(empty($action)){
            //挂载点不存在创建挂载点-来自开发文档
            $action=Action::create([
                'pid'=>0,
                'name'=>'登陆失败',
                'value'=>'login_error',
                'sort'=>0,
                'data'=>'传入：账号密码信息'
            ]);
        }
        //2.判断行为是否存在
        $action_find=Action::where(['value'=>$this->entry])->find();
        if(empty($action_find)){
            //行为信息
            Action::create([
                'pid'=>$action['id'],
                'name'=>'登陆失败记录',
                'value'=>$this->entry,
                'state'=>1,//开启行为
                'sort'=>0,
                'data'=>'登陆失败记录-创建插件['.$this->only.']'
            ]);
        }
        //3.判断插件记录是否存在
        $plug_find=Plug::where(['only'=>$this->only])->find();
        if(empty($plug_find)){
            //创建插件信息
            $plug_info=$this->info();//读取配置信息
            $plug_info['config']=json_encode(['by'=>'nodcloud.com']);//插件配置
            $plug_info['state']=1;//开启插件
            Plug::create($plug_info);
        }
        //4.创建数据表
        $creater_sql="CREATE TABLE `is_loginerror` ( `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT, `time` int NOT NULL COMMENT '登陆时间', `ip` varchar(15) NOT NULL COMMENT '登陆IP', `user` varchar(36) NOT NULL COMMENT '登陆账号', `pwd` varchar(128) NOT NULL COMMENT '登陆密码' ) COMMENT '登陆失败记录-创建插件[loginerror]';";
        Db::execute($creater_sql);
        return true;
    }
    //卸载
    public function uninstall(){
        //1.删除插件记录
        Plug::where(['only'=>$this->only])->delete();
        //2.删除插件记录
        Action::where(['value'=>$this->entry])->delete();
        //3.删除数据表
        $drop_sql="DROP TABLE `is_loginerror`;";
        Db::execute($drop_sql);
        return true;
    }
}
