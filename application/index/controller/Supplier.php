<?php
namespace app \index \controller ;
use think\Hook;
use think\Request;
use app\index\controller\Acl;
use app\index\model\Supplier as Suppliers;
use app\index\controller\Formfield;
class Supplier extends Acl {
    //供应商模块
    //---------------(^_^)---------------//
    //供应商视图
    public function main(){
        return $this->fetch();
    }
    //供应商列表
    public function supplier_list(){
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
            ],'supplier');//构造SQL
            $sql=auth('supplier',$sql);//数据鉴权
            $count = Suppliers::where ($sql)->count();//获取总条数
            $arr = Suppliers::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新供应商信息
    public function set_supplier(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'supplier');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Suppliers::create(syn_sql($input,'supplier'));
                    Hook::listen('create_supplier',$create_info);//供应商新增行为
                    push_log('新增供应商信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'supplier.update');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Suppliers::update(syn_sql($input,'supplier'));
                    Hook::listen('update_supplier',$update_info);//供应商更新行为
                    push_log('更新供应商信息[ '.$update_info['name'].' ]');//日志
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
    //获取供应商信息
    public function get_supplier(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Suppliers::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除供应商信息
    public function del_supplier(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据关联是否存在
            $exist=more_table_find([
            	['table'=>'purchaseclass','where'=>['supplier'=>['in',$input['arr']]]],
            	['table'=>'repurchaseclass','where'=>['supplier'=>['in',$input['arr']]]],
            	['table'=>'rpurchaseclass','where'=>['supplier'=>['in',$input['arr']]]],
            	['table'=>'paymentclass','where'=>['supplier'=>['in',$input['arr']]]],
            ]);
            //判断数据关联是否存在
            if(!$exist){
            	$info=db('supplier')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                foreach ($info as $info_vo) {
                    push_log('删除供应商信息[ '.$info_vo['name'].' ]');//日志
                    Hook::listen('del_supplier',$info_vo['id']);//供应商删除行为
                }
                Suppliers::where(['id'=>['in',$input['arr']]])->delete();
                $resule=['state'=>'success'];
            }else{
            	$resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出供应商信息
    public function export_supplier(){
        $input=input('get.');
        $sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'number'=>'full_like',
            'contacts'=>'full_like',
            'tel'=>'full_like',
            'add'=>'full_like',
            'data'=>'full_like'
        ],'supplier');//构造SQL
        $sql=auth('supplier',$sql);//数据鉴权
        $arr = Suppliers::where($sql)->order('id desc')->select();//查询数据
        $formfield=get_formfield('supplier_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'供应商列表']);
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
        push_log('导出供应商信息');//日志
        export_excel('供应商列表',$excel);
    }
    //导入供应商信息
    public function import_supplier(Request $request){
		$file=$request->file('file');//获取表单上传文件
		if (empty($file)){
		    $resule=['state'=>'error','info'=>'传入数据不完整!'];
		}else{
		    $nod=$file->validate (['ext'=>'xlsx'])->rule ('uniqid')->move (ROOT_PATH.'skin'.DS.'upload'.DS.'xlsx');//验证且重命名并移动文件
		    if($nod){
		        $path=ROOT_PATH .'skin'.DS .'upload'.DS .'xlsx'.DS.$nod->getSaveName();
		        $arr=get_xlsx($path);
		        unset($arr[1]);//删除标题行
		        $create_sql=[];//初始化SQL
		        foreach ($arr as $key=>$vo) {
		            $sql=[];//初始化数据SQL
		            $sql['name']=$vo['A'];
		            $sql['py']=zh2py($vo['A']);//首拼信息
		            $sql['number']=$vo['B'];
		            $sql['contacts']=$vo['C'];
		            $sql['tel']=$vo['D'];
		            $sql['add']=$vo['E'];
		            $sql['bank']=$vo['F'];
		            $sql['account']=$vo['G'];
		            $sql['tax']=$vo['H'];
		            $sql['other']=$vo['I'];
		            $sql['email']=$vo['J'];
		            $sql['data']=$vo['K'];
		            //数据合法性验证
		            $vali = $this->validate($sql,'supplier');
		            if($vali===true){
		                push_log('导入供应商信息[ '.$sql['name'].' ]');//日志
		                array_push($create_sql,$sql);//加入SQL
		            }else{
		                //返回错误信息
		                return json(['state'=>'error','info'=>'模板文件第[ '.$key.' ]行'.$vali]);
		            }
		        }
		        $insert_count=db('supplier')->insertAll($create_sql);
		        $resule=['state'=>'success','info'=>$insert_count];
		    }else{
		        $resule=['state'=>'error','info'=>$file->getError()];
		    }
		}
        return json($resule);
    }
}