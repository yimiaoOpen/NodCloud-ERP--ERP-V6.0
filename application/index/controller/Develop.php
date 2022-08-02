<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Menu;
use app\index\model\Action;
use app\index\model\Plug;
use app\index\model\Formfield;
use app\index\model\Formfieldinfo;
class Develop extends Acl {
    //开发工具
    //---------------(^_^)---------------//
    //菜单设置
    public function menu(){
        $list=Menu::order('sort asc')->select();
        if(!empty($list)){
            $tree=new \org\tree();
            $list=$tree::vTree($list);//按照关联排序
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    //新增|更新菜单信息  
    public function set_menu(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $create_info=Menu::create(syn_sql($input,'menu'));
                Hook::listen('create_menu',$create_info);//菜单新增行为
                $resule=['state'=>'success'];
            }else{
                //更新
                //所属菜单不可等于或包含当前所属菜单
                if(in_array($input['pid'],find_tree_arr('menu',[$input['id']]))){
                    $resule=['state'=>'error','info'=>'所属菜单选择不正确!'];
                }else{
                    $update_info=Menu::update(syn_sql($input,'menu'));
                    Hook::listen('update_menu',$update_info);//菜单更新行为
                    $resule=['state'=>'success'];
                }
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //获取菜单信息
    public function get_menu(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Menu::with('pidinfo')->where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除菜单信息
    public function del_menu(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $find=Menu::where(['pid'=>$input['id']])->find();
            if(empty($find)){
                Menu::where(['id'=>$input['id']])->delete();
                Hook::listen('del_menu',$input['id']);//菜单删除行为
                $resule=['state'=>'success'];
            }else{
                $resule=['state'=>'exist_data','info'=>'存在子数据,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //---------------(^_^)---------------//
    //行为管理
    public function action(){
        $list=Action::order('sort asc')->select();
        if(!empty($list)){
            $tree=new \org\tree();
            $list=$tree::vTree($list);//按照关联排序
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    //新增|更新行为信息  
    public function set_action(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $create_info=Action::create(syn_sql($input,'action'));
                Hook::listen('create_action',$create_info);//行为新增行为
            }else{
                //更新
                $update_info=Action::update(syn_sql($input,'action'));
                Hook::listen('update_action',$update_info);//行为更新行为
            }
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //获取行为信息
    public function get_action(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Action::with('pidinfo')->where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除行为信息
    public function del_action(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $find=Action::where(['pid'=>$input['id']])->find();
            if(empty($find)){
                Action::where(['id'=>$input['id']])->delete();
                Hook::listen('del_action',$input['id']);//行为删除行为
                $resule=['state'=>'success'];
            }else{
                $resule=['state'=>'exist_data','info'=>'存在子数据,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //---------------(^_^)---------------//
    //插件管理
    public function plug(){
        return $this->fetch();
    }
    //插件列表
    public function plug_list(){
        $input=input('post.');
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $list=[];
            foreach (getDir(ROOT_PATH.'addons') as $dir) {
                //获取插件信息
                $data=[];
                $class = "addons\\{$dir}\\config";
                $plug = new $class;
                $data = $plug->info();//获取插件基本信息
                $find=Plug::where(['only'=>$data['only']])->find();
                $data['state']=empty($find)?['nod'=>-1,'name'=>'未安装']:$find['state'];
                $data['set']=$plug->set;
                array_push($list,$data);
            }
            //按照条件匹配数组
            isset_full($input,'name')&&($condition['name'] = ['in',$input['name']]);
            isset_full($input,'state')&&($condition['state|nod'] = ['eq',$input['state']-1]);
            isset($condition)&&($list=searchdata($list,$condition));//搜索数据
            $count =count($list);//获取总条数
            $page=$input['page'];
            $limit=$input['limit'];
            $arr = array_slice($list,$limit*($page-1),$limit);
            $resule['code']=0;
        	$resule['msg']='获取成功';
        	$resule['count']=$count;
        	$resule['data']=$arr;
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json ($resule);
        
    }
    //插件服务
    public function plug_server(){
        $input=input('post.');
        if(isset_full($input,'event') && isset_full($input,'only')){
            $space = 'addons\\'.$input['only'].'\\config';
            $plug = new $space;
            if($input['event']=='install'){
                //安装插件
                $state = $plug->install();//安装插件
                if($state){
                    $resule=['state'=>'success'];
                }else{
                    $resule=[
                        'state'=>'error',
                        'info'=>'插件安装失败,请联系插件作者解决!'
                    ];
                }
            }elseif($input['event']=='discont'){
                //停用插件
                Plug::where(['only'=>$plug->only])->update(['state'=>0]);
                Action::where(['value'=>$plug->entry])->update(['state'=>0]);
                $resule=['state'=>'success'];
            }elseif($input['event']=='enable'){
                //启用插件
                Plug::where(['only'=>$plug->only])->update(['state'=>1]);
                Action::where(['value'=>$plug->entry])->update(['state'=>1]);
                $resule=['state'=>'success'];
            }elseif($input['event']=='uninstall'){
                //卸载插件
                $state = $plug->uninstall();//卸载插件
                if($state){
                    $resule=['state'=>'success'];
                }else{
                    $resule=[
                        'state'=>'error',
                        'info'=>'插件卸载失败,请联系插件作者解决!'
                    ];
                }
            }elseif($input['event']=='delect'){
                //删除插件
                $entry = $plug->entry;//获取入口信息
                $only = $plug->only;//获取标识
                $state=more_table_find([
                    ['table'=>'plug','where'=>['only'=>$only]],
                    ['table'=>'action','where'=>['value'=>$entry]],
                ]);
                if(!$state){
                    $remove=removedir(ROOT_PATH.'addons'.DS.$only);
                    if($remove){
                        $resule=['state'=>'success'];
                    }else{
                        $resule=[
                            'state'=>'error',
                            'info'=>'插件删除失败,请检查插件目录权限!'
                        ];
                    }
                }else{
                    $resule=[
                        'state'=>'error',
                        'info'=>'插件删除失败,存在残留数据!'
                    ];
                }
            }else{
                $resule=['state'=>'error','info'=>'参数识别失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json ($resule);
    }
    //代码压缩
    public function encode(){
        return $this->fetch();
    }
    //压缩代码-逻辑
    public function compress(){
        $input=input('post.',null,'html_entity_decode');//兼容XSS防护
        if(isset($input['type'])&& isset_full($input,'code')){
            $code=$input['code'];
            if($input['type']=='0'){
                //HTML
                $code=compress_html($code);//HTML压缩代码
            }elseif($input['type']=='1'){
                //JS
                $jsmin=new \org\jsmin();
                $code=$jsmin::minify($code);
            }elseif($input['type']=='2'){
                //CSS
                $code=compress_css($code);//CSS代码压缩
            }
            $resule=['state'=>'success','code'=>$code];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json ($resule);
    }
    //表单字段
    public function formfield(){
        return $this->fetch();
    }
    //表单字段列表
    public function formfield_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_like',
                'key'=>'full_like',
                'type'=>'full_dec_1'
            ],'formfield');//构造SQL
            $count = Formfield::where ($sql)->count();//获取总条数
            $arr = Formfield::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'code'=>0,
                'msg'=>'获取成功',
                'count'=>$count,
                'data'=>$arr
            ];//返回数据
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //新增|更新表单字段
    public function set_formfield(){
        $input=input('post.',null,'html_entity_decode');//兼容XSS防护
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'formfield');
                if($vali===true){
                    $create_info=Formfield::create(syn_sql($input,'formfield'));
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'formfield.update');
                if($vali===true){
                    $update_info=Formfield::update(syn_sql($input,'formfield'));
                    Formfieldinfo::where(['pid'=>$input['id']])->delete();
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }
            //增加字段详情
            if(isset_full($input,'info') && $resule['state']=='success'){
                $info_pid=empty($input['id'])?$create_info['id']:$update_info['id'];
                foreach ($input['info'] as $info_vo) {
                    Formfieldinfo::create([
                        'pid'=>$info_pid,
                        'info'=>$info_vo['info'],
                        'show'=>$info_vo['show']
                    ]);
                }
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //获取表单字段
    public function get_formfield(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Formfield::with('subinfo')->where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除表单字段
    public function del_formfield(){
        $input=input('post.');
        if(isset_full($input,'arr')){
            Formfield::where(['id'=>['in',$input['arr']]])->delete();
            Formfieldinfo::where(['pid'=>['in',$input['arr']]])->delete();
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //复制表单字段
    public function copy_formfield(){
        $input=input('post.');
        if(isset_full($input,'id')){
            //1.获取源数据
            $formfield=Formfield::where(['id'=>$input['id']])->find();
            $formfieldinfo=Formfieldinfo::where(['pid'=>$input['id']])->order('id asc')->select();
            //2.新增新数据
            $create_info=Formfield::create([
                'name'=>$formfield['name'].'|自动复制',
                'key'=>$formfield['key'],
                'data'=>$formfield['data']
            ]);
            foreach ($formfieldinfo as $vo) {
                Formfieldinfo::create([
                    'pid'=>$create_info['id'],
                    'info'=>$vo['info'],
                    'show'=>$vo['show']
                ]);
            }
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}