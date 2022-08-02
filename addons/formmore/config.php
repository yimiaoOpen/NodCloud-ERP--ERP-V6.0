<?php
namespace addons\formmore;
use think\Db;
use app\index\model\Action;
use app\index\model\Plug;
class Config {
    //插件标识
    public $only='formmore';
    //插件入口
    public $entry='addons\formmore\controller\main';
    //插件操作
    public $set=[
        ['title'=>'配置','parameter'=>'formmore/view/config'],
        ['title'=>'说明','parameter'=>'formmore/view/about']
    ];
    //插件信息
    public function info(){
        return [
            'name'=>'字段扩展',
            'info'=>'扩展模块字段信息',
            'only'=>'formmore',
            'ver'=>'1.0',
            'author'=>'NODCLOUD.COM'
        ];
    }
    //安装
    public function install(){
        //1.判断行为挂载点是否存在
        $action=Action::where(['value'=>'formmore','pid'=>0])->find();
        if(empty($action)){
            //挂载点不存在创建挂载点-来自开发文档
            $action=Action::create([
                'pid'=>0,
                'name'=>'字段扩展',
                'value'=>'formmore',
                'sort'=>0,
                'data'=>''
            ]);
        }
        //2.判断行为是否存在
        $action_find=Action::where(['value'=>$this->entry])->find();
        if(empty($action_find)){
            //行为信息
            Action::create([
                'pid'=>$action['id'],
                'name'=>'字段扩展',
                'value'=>$this->entry,
                'state'=>1,//开启行为
                'sort'=>0,
                'data'=>'字段扩展-创建插件['.$this->only.']'
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
        $creater_sql="CREATE TABLE `is_formmore` ( `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT, `name` varchar(64) NOT NULL COMMENT '配置名称', `info` text NULL COMMENT '配置内容', `data` varchar(256) NULL DEFAULT '' COMMENT '备注信息' ) COMMENT '字段扩展-创建插件[formmore]';";
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
        $drop_sql="DROP TABLE `is_formmore`;";
        Db::execute($drop_sql);
        return true;
    }
}
