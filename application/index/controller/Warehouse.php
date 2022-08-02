<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Warehouse as Warehouses;
use app\index\controller\Formfield;
class Warehouse extends Acl {
    //仓库模块
    //---------------(^_^)---------------//
    //仓库视图
    public function main(){
        return $this->fetch();
    }
    //仓库列表
    public function warehouse_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'number'=>'full_like',
                'contacts'=>'full_like',
                'tel'=>'full_like',
                'add'=>'full_like',
                'data'=>'full_like'
            ],'warehouse');//构造SQL
            $sql=auth('warehouse',$sql);//数据鉴权
            $count = Warehouses::where ($sql)->count();//获取总条数
            $arr = Warehouses::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新仓库信息
    public function set_warehouse(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'warehouse');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Warehouses::create(syn_sql($input,'warehouse'));
                    Hook::listen('create_warehouse',$create_info);//仓库新增行为
                    push_log('新增仓库信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'warehouse.update');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Warehouses::update(syn_sql($input,'warehouse'));
                    Hook::listen('update_warehouse',$update_info);//仓库更新行为
                    push_log('更新仓库信息[ '.$update_info['name'].' ]');//日志
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
    //获取仓库信息
    public function get_warehouse(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Warehouses::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除仓库信息
    public function del_warehouse(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据是否存在
            $exist=more_table_find([
            	['table'=>'allocationinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'cashierinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'exchangeinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'goods','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'otpurchaseinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'otsaleinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'purchaseinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'recashierinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'repurchaseinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'resaleinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'room','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'rpurchaseinfo','where'=>['warehouse'=>['in',$input['arr']]]],
            	['table'=>'saleinfo','where'=>['warehouse'=>['in',$input['arr']]]]
            ]);
            //判断数据是否存在
            if(!$exist){
            	 $info=db('warehouse')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                foreach ($info as $info_vo) {
                    push_log('删除仓库信息[ '.$info_vo['name'].' ]');//日志
                    Hook::listen('del_warehouse',$info_vo['id']);//仓库删除行为
                }
                Warehouses::where(['id'=>['in',$input['arr']]])->delete();
                $resule=['state'=>'success'];
            }else{
            	$resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出仓库信息
    public function export_warehouse(){
        $input=input('get.');
        $sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'number'=>'full_like',
            'contacts'=>'full_like',
            'tel'=>'full_like',
            'add'=>'full_like',
            'data'=>'full_like'
        ],'warehouse');//构造SQL
        $sql=auth('warehouse',$sql);//数据鉴权
        $arr = Warehouses::where($sql)->order('id desc')->select();//查询数据
        $formfield=get_formfield('warehouse_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'仓库列表']);
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
        push_log('导出仓库信息');//日志
        export_excel('仓库列表',$excel);
    }
}