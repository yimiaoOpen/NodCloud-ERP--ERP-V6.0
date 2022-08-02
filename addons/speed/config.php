<?php
namespace addons\speed;
use app\index\model\Action;
use app\index\model\Plug;
class Config {
    //插件标识
    public $only='speed';
    //插件入口
    public $entry='addons\speed\controller\main';
    //插件操作
    public $set=[
        ['title'=>'配置','parameter'=>'speed/view/config'],
        ['title'=>'说明','parameter'=>'speed/view/about']
    ];
    //插件信息
    public function info(){
        return [
            'name'=>'页面访问优化',
            'info'=>'该插件可优化页面数据，提高访问速度。',
            'only'=>$this->only,
            'ver'=>'1.0',
            'author'=>'NODCLOUD.COM'
        ];
    }
    //安装
    public function install(){
        //1.判断行为挂载点是否存在
        $action=Action::where(['value'=>'view_filter','pid'=>0])->find();
        if(empty($action)){
            //挂载点不存在创建挂载点-来自开发文档
            $action=Action::create([
                'pid'=>0,
                'name'=>'视图内容过滤',
                'value'=>'view_filter',
                'sort'=>0,
                'data'=>'传入：HTML代码'
            ]);
        }
        //2.判断行为是否存在
        $action_find=Action::where(['value'=>$this->entry])->find();
        if(empty($action_find)){
            //行为信息
            Action::create([
                'pid'=>$action['id'],
                'name'=>'页面访问优化',
                'value'=>$this->entry,
                'state'=>1,//开启行为
                'sort'=>0,
                'data'=>'页面访问优化-创建插件['.$this->only.']'
            ]);
        }
        //3.判断插件记录是否存在
        $plug_find=Plug::where(['only'=>$this->only])->find();
        if(empty($plug_find)){
            //创建插件信息
            $plug_info=$this->info();//读取配置信息
            $plug_info['config']=json_encode([
                'cache_time'=>'2',
                'exclude_css'=>[
                    "/skin/css/layui.css"
                ],
                'exclude_js'=>[
                    "/skin/js/jquery.js",
                    "/skin/js/layui.js",
                    "/skin/js/public/treeTable.js",
                    "/skin/js/public/ztree.js"
                ]
            ]);//插件配置
            $plug_info['state']=1;//开启插件
            Plug::create($plug_info);
        }
        //4.创建缓存文件目录
        mkdir(ROOT_PATH.'skin'.DS.'speed');
        mkdir(ROOT_PATH.'skin'.DS.'speed'.DS.'css');
        mkdir(ROOT_PATH.'skin'.DS.'speed'.DS.'js');
        file_put_contents(ROOT_PATH.'skin'.DS.'speed'.DS.'about.txt', '该文件夹存放[speed插件]的静态文件。');
        return true;
    }
    //卸载
    public function uninstall(){
        //1.删除插件记录
        Plug::where(['only'=>$this->only])->delete();
        //2.删除插件记录
        Action::where(['value'=>$this->entry])->delete();
        //3.删除插件目录
        removedir(ROOT_PATH.'skin'.DS.'speed');
        return true;
    }
}
