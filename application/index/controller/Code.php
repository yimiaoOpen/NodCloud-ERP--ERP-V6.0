<?php
namespace app \index \controller ;
use think\Hook;
use think\Request;
use app\index\controller\Acl;
use app\index\model\Code as Codes;
use app\index\controller\Formfield;
class Code extends Acl {
    //条码模块
    //---------------(^_^)---------------//
    //条码视图
    public function main(){
        return $this->fetch();
    }
    //条码列表
    public function code_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'code'=>'full_like',
                'type'=>'full_dec_1'
            ],'code');//构造SQL
            $count = Codes::where ($sql)->count();//获取总条数
            $arr = Codes::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新条码信息
    public function set_code(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'code');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Codes::create(syn_sql($input,'code'));
                    
                    Hook::listen('create_code',$create_info);//条码新增行为
                    push_log('新增条码信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'code.update');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Codes::update(syn_sql($input,'code'));
                    Hook::listen('update_code',$update_info);//条码更新行为
                    push_log('更新条码信息[ '.$update_info['name'].' ]');//日志
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
    //获取条码信息
    public function get_code(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Codes::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除条码信息
    public function del_code(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            $info=db('code')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
            foreach ($info as $info_vo) {
                push_log('删除条码信息[ '.$info_vo['name'].' ]');//日志
                Hook::listen('del_code',$info_vo['id']);//条码删除行为
            }
            Codes::where(['id'=>['in',$input['arr']]])->delete();
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出条码信息
    public function export_code(){
        $input=input('get.');
        $sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'code'=>'full_like',
            'type'=>'full_dec_1'
        ],'code');//构造SQL
        $arr = Codes::where($sql)->order('id desc')->select();//查询数据
        //加入图像链接
        foreach ($arr as $key=>$vo) {
            if($vo['type']['nod']=='0'){
                $arr[$key]['img']=txm($vo['code'],false);
            }elseif($vo['type']['nod']=='1'){
                $arr[$key]['img']=ewm($vo['code'],false);
            }
        }
        $formfield=get_formfield('code_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'条码列表']);
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
                if($formfield_vo['key']=='img'){
                    $row_data[$formfield_vo['key']]=[
                        'type'=>'img',
                        'info'=>$val
                    ];//图像数据赋值
                }else{
                    $row_data[$formfield_vo['key']]=$val;//数据赋值
                }
            }
            array_push($table_data,$row_data);//加入行数据
        }
        array_push($excel,['type'=>'table','info'=>['cell'=>$table_cell,'data'=>$table_data]]);//填充表内数据
        //3.导出execl
        push_log('导出条码信息');//日志
        export_excel('条码列表',$excel);
    }
    //导入条码信息
    public function import_code(Request $request){
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
		        $type_arr=['0'=>'条形码','1'=>'二维码'];
		        foreach ($arr as $key=>$vo) {
		            $sql=[];//初始化数据SQL
		            $sql['name']=$vo['A'];
		            $sql['py']=zh2py($vo['A']);//首拼信息
		            $sql['code']=$vo['B'];
		            //条码类型判断
		            $type_search=array_search($vo['C'],$type_arr);
		            if($type_search!==false){
		                $sql['type']=$type_search;
		            }else{
		                //返回错误信息
		                return json(['state'=>'error','info'=>'模板文件第[ '.$key.' ]行条码类型可选项为[条形码|二维码]']);
		            }
		            $sql['data']=$vo['D'];
		            //数据合法性验证
		            $vali = $this->validate($sql,'code');
		            if($vali===true){
		                push_log('导入条码信息[ '.$sql['name'].' ]');//日志
		                array_push($create_sql,$sql);//加入SQL
		            }else{
		                //返回错误信息
		                return json(['state'=>'error','info'=>'模板文件第[ '.$key.' ]行'.$vali]);
		            }
		        }
		        $insert_count=db('code')->insertAll($create_sql);
		        $resule=['state'=>'success','info'=>$insert_count];
		    }else{
		        $resule=['state'=>'error','info'=>$file->getError()];
		    }
		}
        return json($resule);
    }
    //生成条码图像
    public function view(){
        $input=input('get.');
        if(isset_full($input,'text') && isset_full($input,'type') && in_array($input['type'],['txm','ewm'])){
            if($input['type']=='txm'){
                //条形码
                txm($input['text']);
            }else if($input['type']=='ewm'){
                //二维条
                ewm($input['text']);
            }
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
}