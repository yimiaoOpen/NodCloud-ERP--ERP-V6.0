<?php
namespace addons\pagemore;
use think\Db;
use app\index\model\Action;
use app\index\model\Plug;
class Config {
    //插件标识
    public $only='pagemore';
    //插件入口
    public $entry='addons\pagemore\controller\main';
    //插件操作
    public $set=[
        ['title'=>'配置','parameter'=>'pagemore/view/config'],
        ['title'=>'说明','parameter'=>'pagemore/view/about']
    ];
    //插件信息
    public function info(){
        return [
            'name'=>'静态文件扩展',
            'info'=>'页面[JS,CSS]静态文件扩展',
            'only'=>'pagemore',
            'ver'=>'1.0',
            'author'=>'NODCLOUD.COM'
        ];
    }
    //安装
    public function install(){
        //1.判断行为挂载点是否存在
        $action=Action::where(['value'=>'pagemore','pid'=>0])->find();
        if(empty($action)){
            //挂载点不存在创建挂载点-来自开发文档
            $action=Action::create([
                'pid'=>0,
                'name'=>'静态文件扩展',
                'value'=>'pagemore',
                'sort'=>0,
                'data'=>'传入：["配置类型","配置标识|为空自动判断"]'
            ]);
        }
        //2.判断行为是否存在
        $action_find=Action::where(['value'=>$this->entry])->find();
        if(empty($action_find)){
            //行为信息
            Action::create([
                'pid'=>$action['id'],
                'name'=>'静态文件扩展',
                'value'=>$this->entry,
                'state'=>1,//开启行为
                'sort'=>0,
                'data'=>'静态文件扩展-创建插件['.$this->only.']'
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
        $creater_sql="CREATE TABLE `is_pagemore` ( `id` int NOT NULL PRIMARY KEY AUTO_INCREMENT, `name` varchar(64) NOT NULL COMMENT '配置名称', `type` varchar(32) NOT NULL COMMENT '配置类型[js|css]', `info` text NULL COMMENT '配置内容', `data` varchar(256) NULL DEFAULT '' COMMENT '备注信息' ) COMMENT '静态文件扩展-创建插件[pagemore]';";
        Db::execute($creater_sql);
        db('pagemore')->insertAll([
            [
                'name'=>'extend',
                'type'=>'css',
                'info'=>'<link rel="stylesheet" href="/skin/pagemore/css/more_font.css" media="all">',
                'data'=>'插件自动创建'
            ],
            [
                'name'=>'public',
                'type'=>'css',
                'info'=>'<link rel="stylesheet" href="/skin/pagemore/css/more_font.css" media="all">',
                'data'=>'插件自动创建'
            ]
        ]);//插入预设数据
        return true;
    }
    //卸载
    public function uninstall(){
        //1.删除插件记录 
        Plug::where(['only'=>$this->only])->delete();
        //2.删除插件记录
        Action::where(['value'=>$this->entry])->delete();
        //3.删除数据表
        $drop_sql="DROP TABLE `is_pagemore`;";
        Db::execute($drop_sql);
        return true;
    }
}
