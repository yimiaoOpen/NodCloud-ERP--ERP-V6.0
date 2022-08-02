<?php
namespace app \index \controller ;
use think\Hook;
use think\Request;
use app\index\controller\Acl;
use app\index\model\Customer as Customers;
use app\index\controller\Formfield;
use app\index\model\Integral;
class Customer extends Acl {
    //客户模块
    //---------------(^_^)---------------//
    //客户视图
    public function main(){
        return $this->fetch();
    }
    //客户列表
    public function customer_list(){
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
            ],'customer');//构造SQL
            $sql=auth('customer',$sql);//数据鉴权
            $count = Customers::where ($sql)->count();//获取总条数
            $arr = Customers::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新客户信息
    public function set_customer(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'Customer');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Customers::create(syn_sql($input,'customer'));
                    Hook::listen('create_customer',$create_info);//客户新增行为
                    push_log('新增客户信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'Customer.update');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Customers::update(syn_sql($input,'customer'));
                    Hook::listen('update_customer',$update_info);//客户更新行为
                    push_log('更新客户信息[ '.$update_info['name'].' ]');//日志
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
    //获取客户信息
    public function get_customer(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Customers::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除客户信息
    public function del_customer(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据关联是否存在
            $exist=more_table_find([
                ['table'=>'saleclass','where'=>['customer'=>['in',$input['arr']]]],
                ['table'=>'resaleclass','where'=>['customer'=>['in',$input['arr']]]],
                ['table'=>'cashierclass','where'=>['customer'=>['in',$input['arr']]]],
                ['table'=>'recashierclass','where'=>['customer'=>['in',$input['arr']]]],
                ['table'=>'itemorderclass','where'=>['customer'=>['in',$input['arr']]]],
                ['table'=>'exchangeclass','where'=>['customer'=>['in',$input['arr']]]],
                ['table'=>'gatherclass','where'=>['customer'=>['in',$input['arr']]]]
            ]);
            //判断数据关联是否存在
            if(!$exist){
                $info=db('customer')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                foreach ($info as $info_vo) {
                    push_log('删除客户信息[ '.$info_vo['name'].' ]');//日志
                    Hook::listen('del_customer',$info_vo['id']);//客户删除行为
                }
                Customers::where(['id'=>['in',$input['arr']]])->delete();
                $resule=['state'=>'success'];
            }else{
                $resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出客户信息
    public function export_customer(){
        $input=input('get.');
        $sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'number'=>'full_like',
            'contacts'=>'full_like',
            'tel'=>'full_like',
            'add'=>'full_like',
            'data'=>'full_like'
        ],'customer');//构造SQL
        $sql=auth('customer',$sql);//数据鉴权
        $arr = Customers::where($sql)->order('id desc')->select();//查询数据
        $formfield=get_formfield('customer_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'客户列表']);
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
        push_log('导出客户信息');//日志
        export_excel('客户列表',$excel);
    }
    //导入客户信息
    public function import_customer(Request $request){
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
		            $sql['bank']=$vo['G'];
		            $sql['account']=$vo['H'];
		            $sql['tax']=$vo['I'];
		            $sql['other']=$vo['J'];
		            $sql['email']=$vo['K'];
		            $sql['data']=$vo['L'];
		            //数据合法性验证
		            $vali = $this->validate($sql,'Customer');
		            if($vali===true){
		                $sql['birthday']=strtotime($vo['F']);
		                push_log('导入客户信息[ '.$sql['name'].' ]');//日志
		                array_push($create_sql,$sql);//加入SQL
		            }else{
		                //返回错误信息
		                return json(['state'=>'error','info'=>'模板文件第[ '.$key.' ]行'.$vali]);
		            }
		        }
		        $insert_count=db('customer')->insertAll($create_sql);
		        $resule=['state'=>'success','info'=>$insert_count];
		    }else{
		        $resule=['state'=>'error','info'=>$file->getError()];
		    }
		}
        return json($resule);
    }
    //---------------(^_^)---------------//
    //积分视图
    public function integral(){
        return $this->fetch();
    }
    //积分列表
    public function integral_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'pid') && isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'type'=>'full_eq',
                'set'=>'full_dec_1',
                'user'=>'full_eq',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'data'=>'full_like'
            ],'integral');//构造SQL
            $sql=auth('integral',$sql);//数据鉴权
            $count = Integral::where ($sql)->count();//获取总条数
            $arr = Integral::with('typedata,userinfo')->where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //设置积分信息
    public function set_integral(){
        $input=input('post.');
        if(isset($input['id'])){
            $info=db('customer')->where(['id'=>$input['id']])->find();//获取客户信息
            if(empty($info)){
                $resule=['state'=>'error','info'=>'客户不存在!'];
            }else{
                $sql['pid']=$input['id'];
                $sql['set']=$input['set']=='inc'?1:0;
        	    $sql['integral']=$input['integral'];
        	    $sql['type']=3;
        	    $sql['time']=time();
        	    $sql['user']=Session('is_user_id');
        	    $sql['class']=1;
        	    $sql['data']=$input['data'];
        	    $create_info=Integral::create($sql);
        	    if(empty($sql['set'])){
        	        Customers::where (['id'=>$input['id']])->setDec ('integral',$sql['integral']);//减少积分
        	        push_log('减少客户[ '.$info['name'].' ]积分[ '.$sql['integral'].' ]');//日志
        	    }else{
        	        Customers::where (['id'=>$input['id']])->setInc ('integral',$sql['integral']);//增加积分
        	        push_log('增加客户[ '.$info['name'].' ]积分[ '.$sql['integral'].' ]');//日志
        	    }
        	    Hook::listen('set_integral',$create_info);//设置积分行为
                $resule=['state'=>'success'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出积分信息
    public function export_integral(){
        $input=input('get.');
        if(isset_full($input,'pid')){
            $sql=get_sql($input,[
                'type'=>'full_eq',
                'set'=>'full_dec_1',
                'user'=>'full_eq',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'data'=>'full_like'
            ],'integral');//构造SQL
            $sql=auth('integral',$sql);//数据鉴权
            $info=db('customer')->where(['id'=>$input['pid']])->find();//获取客户信息
            $arr = Integral::with('userinfo,typedata')->where($sql)->order('id desc')->select();//查询数据
            $formfield=get_formfield('integral_export','array');//获取字段配置
            //开始构造导出数据
            $excel=[];//初始化导出数据
            //1.填充标题数据
            array_push($excel,['type'=>'title','info'=>'客户['.$info['name'].']积分信息']);
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
            //3.构造文本数据
            array_push($excel,['type'=>'node','info'=>['剩余积分:',$info['integral']]]);
            //4.导出execl
            push_log('导出客户['.$info['name'].']积分信息');//日志
            export_excel('客户['.$info['name'].']积分信息',$excel);
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
}