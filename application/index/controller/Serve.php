<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Serve as Serves;
use app\index\controller\Formfield;
class Serve extends Acl {
    //服务项目模块
    //---------------(^_^)---------------//
    //服务项目视图
    public function main(){
        return $this->fetch();
    }
    //服务项目列表
    public function serve_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'number'=>'full_like',
                'data'=>'full_like'
            ],'serve');//构造SQL
            $count = Serves::where ($sql)->count();//获取总条数
            $arr = Serves::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新服务项目信息
    public function set_serve(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'serve');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Serves::create(syn_sql($input,'serve'));
                    Hook::listen('create_serve',$create_info);//服务项目新增行为
                    push_log('新增服务项目信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'serve.update');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Serves::update(syn_sql($input,'serve'));
                    Hook::listen('update_serve',$update_info);//服务项目更新行为
                    push_log('更新服务项目信息[ '.$update_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //获取服务项目信息
    public function get_serve(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Serves::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除服务项目信息
    public function del_serve(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据是否存在
            $exist=more_table_find([
            	['table'=>'itemorderinfo','where'=>['serve'=>['in',$input['arr']]]]
            ]);
            //判断数据是否存在
            if(!$exist){
            	$info=db('serve')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                foreach ($info as $info_vo) {
                    push_log('删除服务项目信息[ '.$info_vo['name'].' ]');//日志
                    Hook::listen('del_serve',$info_vo['id']);//服务项目删除行为
                }
                Serves::where(['id'=>['in',$input['arr']]])->delete();
                $resule=['state'=>'success'];
            }else{
            	$resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出服务项目信息
    public function export_serve(){
        $input=input('get.');
        $sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'number'=>'full_like',
            'data'=>'full_like'
        ],'serve');//构造SQL
        $arr = Serves::where($sql)->order('id desc')->select();//查询数据
        $formfield=get_formfield('serve_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'服务项目列表']);
        //2.构造表格数据
        $table_cell=[];//初始化表头数据
        //构造表头数据
        foreach ($formfield as $formfield_vo) {
            $table_cell[$formfield_vo['key']]=$formfield_vo['text'];
        }
        $table_data=[];//初始化表内数据
        //构造表内数据
        foreach ($arr as $arr_vo) {
            $row_data=[];
            //循环字段配置
            foreach ($formfield as $formfield_vo) {
                $val='nod_initial';//初始化数据
                //循环匹配数据源
                foreach (explode('|',$formfield_vo['data']) as $source) {
                    $val=$val=='nod_initial'?$arr_vo[$source]:(isset($val[$source])?$val[$source]:'');
                }
                $row_data[$formfield_vo['key']]=$val;//数据赋值
            }
            array_push($table_data,$row_data);//加入行数据
        }
        array_push($excel,['type'=>'table','info'=>['cell'=>$table_cell,'data'=>$table_data]]);//填充表内数据
        //3.导出execl
        push_log('导出服务项目信息');//日志
        export_excel('服务项目列表',$excel);
    }
}