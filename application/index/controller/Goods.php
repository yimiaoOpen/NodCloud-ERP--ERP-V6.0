<?php
namespace app \index \controller ;
use think\Hook;
use think\Request;
use app\index\controller\Acl;
use app\index\model\Goods as Goodss;
use app\index\model\Goodsclass;
use app\index\controller\Formfield;
use app\index\model\Attribute;
use app\index\model\Attr;
use app\index\model\Brand;
use app\index\model\Unit;
use app\index\model\Warehouse;
class Goods extends Acl {
    //商品模块
    //---------------(^_^)---------------//
    //商品视图
    public function main(){
        $goodsclass=Goodsclass::select();
        if(!empty($goodsclass)){
            $tree=new \org\tree();
            $goodsclass=$tree::vTree($goodsclass);//按照关联排序
        }
        $this->assign('goodsclass',$goodsclass);
        $this->assign('attribute',Attribute::with('subinfo')->where(['pid'=>0])->select());
        return $this->fetch();
    }
    //商品列表
    public function goods_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'number'=>'full_like',
                'spec'=>'full_like',
                'class'=>'full_goodsclass_tree_sub',
                'brand'=>'full_eq',
                'unit'=>'full_eq',
                'code'=>'full_like',
                'location'=>'full_like',
                'data'=>'full_like'
            ],'goods');//构造SQL
            $count = Goodss::where ($sql)->count();//获取总条数
            $arr = Goodss::with('classinfo,unitinfo,brandinfo,warehouseinfo')->where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新商品信息
    public function set_goods(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $goods_vali = $this->validate($input,'Goods');//商品信息验证
                if($goods_vali===true){
                    //验证辅助属性
                    if(isset_full($input,'attr')){
                        foreach ($input['attr'] as $attr_vo) {
                            $attr_vali = $this->validate($attr_vo,'Attr');//辅助属性验证
                            if($attr_vali!==true){
                                return json(['state'=>'error','info'=>'[ 辅助属性 ]第'.($attr_key+1).'行'.$attr_vali]);
                                exit;
                            }
                        }
                    }
                    //写入数据
                    $input['py']=zh2py($input['name']);//首拼信息
                    isset($input['imgs'])||($input['imgs']=[]);//图像信息
                    $create_info=Goodss::create(syn_sql($input,'goods'));
                    Hook::listen('create_goods',$create_info);//商品新增行为
                    push_log('新增商品信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$goods_vali];
                }
            }else{
                //更新
                $goods_vali = $this->validate($input,'Goods.update');
                if($goods_vali===true){
                    //验证辅助属性
                    if(isset_full($input,'attr')){
                        foreach ($input['attr'] as $attr_key=>$attr_vo) {
                            $attr_vali = $this->validate($attr_vo,'Attr');//辅助属性验证
                            if($attr_vali!==true){
                                return json(['state'=>'error','info'=>'[ 辅助属性 ]第'.($attr_key+1).'行'.$attr_vali]);
                                exit;
                            }
                        }
                    }
                    $input['py']=zh2py($input['name']);//首拼信息
                    isset($input['imgs'])||($input['imgs']=[]);//图像信息
                    $update_info=Goodss::update(syn_sql($input,'goods'));
                    Hook::listen('update_goods',$update_info);//商品更新行为
                    push_log('更新商品信息[ '.$update_info['name'].' ]');//日志
                    Attr::where(['pid'=>$update_info['id']])->delete();
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$goods_vali];
                }
            }
            //添加辅助属性
            if(isset_full($input,'attr') && $resule['state']='success'){
                $attr_pid=empty($input['id'])?$create_info['id']:$update_info['id'];
                foreach ($input['attr'] as $attr_vo) {
                    $attr_vo['pid']=$attr_pid;
                    Attr::create(syn_sql($attr_vo,'attr'));
                }
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //获取商品信息
    public function get_goods(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Goodss::with('classinfo,unitinfo,brandinfo,warehouseinfo,attrinfo')->where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除商品信息
    public function del_goods(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据是否存在
            $exist=more_table_find([
            	['table'=>'allocationinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'cashierinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'exchangeinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'opurchaseinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'otpurchaseinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'otsaleinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'purchaseinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'recashierinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'repurchaseinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'resaleinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'room','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'rpurchaseinfo','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'serial','where'=>['goods'=>['in',$input['arr']]]],
            	['table'=>'saleinfo','where'=>['goods'=>['in',$input['arr']]]]
            ]);
            //判断数据是否存在
            if(!$exist){
            	$info=db('goods')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                foreach ($info as $info_vo) {
                    push_log('删除商品信息[ '.$info_vo['name'].' ]');//日志
                    Hook::listen('del_goods',$info_vo['id']);//商品删除行为
                }
                Goodss::where(['id'=>['in',$input['arr']]])->delete();
                Attr::where(['pid'=>['in',$input['arr']]])->delete();
                $resule=['state'=>'success'];
            }else{
            	$resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //上传商品图像
    public function upload_img(Request $request){
		$file=$request->file('file');//获取表单上传文件
		if (empty($file)){
		    $resule=['state'=>'error','info'=>'传入数据不完整!'];
		}else{
            //单文件限制2MB
            $nod=$file->validate (['size'=>2097152,'ext'=>'png,gif,jpg,jpeg,bmp'])->rule ('uniqid')->move (ROOT_PATH .'skin'.DS .'upload'.DS .'goods'.DS .'img');
            if ($nod){
                $file_name=$nod->getSaveName ();
                $file_path='/skin/upload/goods/img/'.$file_name;
                $resule=['state'=>'success','info'=>$file_path];
            }else {
                $resule=['state'=>'error','info'=>$file->getError()];//返回错误信息
            }
		}
		return json ($resule);
    }
    //导出商品信息
    public function export_goods(){
        $input=input('get.');
        $sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'number'=>'full_like',
            'spec'=>'full_like',
            'class'=>'full_goodsclass_tree_sub',
            'brand'=>'full_eq',
            'unit'=>'full_eq',
            'code'=>'full_like',
            'location'=>'full_like',
            'data'=>'full_like'
        ],'goods');//构造SQL
        $arr = Goodss::with('classinfo,unitinfo,brandinfo,warehouseinfo,attrinfo')->where($sql)->order('id desc')->select();//查询数据
        $formfield=get_formfield('goods_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'商品列表']);
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
        push_log('导出商品信息');//日志
        export_excel('商品列表',$excel);
    }
    //导入商品信息
    public function import_goods(Request $request){
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
		            $sql['spec']=$vo['C'];
		            $sql['class']=$vo['D'];
		            $sql['brand']=$vo['E'];
		            $sql['unit']=$vo['F'];
		            $sql['buy']=$vo['G'];
		            $sql['sell']=$vo['H'];
		            $sql['retail']=$vo['I'];
		            $sql['integral']=$vo['J'];
		            $sql['code']=$vo['K'];
		            $sql['warehouse']=$vo['L'];
		            $sql['location']=$vo['M'];
		            $sql['stocktip']=$vo['N'];
		            $sql['data']=$vo['O'];
		            //数据合法性验证
		            $vali = $this->validate($sql,'Goods');
		            if($vali===true){
		                push_log('导入商品信息[ '.$sql['name'].' ]');//日志
		                array_push($create_sql,$sql);//加入SQL
		            }else{
		                //返回错误信息
		                return json(['state'=>'error','info'=>'模板文件第[ '.$key.' ]行'.$vali]);
		            }
		        }
		        foreach ($create_sql as $create_key=>$create_vo) {
		            //商品分类判断
		            $class=Goodsclass::where(['name'=>$create_vo['class']])->find();
		            if(empty($class)){
		                $class=Goodsclass::create(['pid'=>0,'name'=>$create_vo['class'],'data'=>'批量导入']);
		            }
		            $create_sql[$create_key]['class']=$class['id'];
		            //商品品牌
		            if(empty($create_vo['brand'])){
		                $brand['id']=0;
		            }else{
		                $brand=Brand::where(['name'=>$create_vo['brand']])->find();
		                if(empty($brand)){
		                    $brand=Brand::create(['name'=>$create_vo['brand'],'data'=>'批量导入']);
		                }
		            }
		            $create_sql[$create_key]['brand']=$brand['id'];
		            //商品单位
		            if(empty($create_vo['unit'])){
		                $unit['id']=0;
		            }else{
		                $unit=Unit::where(['name'=>$create_vo['unit']])->find();
		                if(empty($unit)){
		                    $unit=Unit::create(['name'=>$create_vo['unit'],'data'=>'批量导入']);
		                }
		            }
		            $create_sql[$create_key]['unit']=$unit['id'];
		            //默认仓库
		            if(empty($create_vo['warehouse'])){
		                $warehouse['id']=0;
		            }else{
		                $warehouse=Warehouse::where(['name'=>$create_vo['warehouse']])->find();
		                if(empty($warehouse)){
		                    $warehouse=Warehouse::create(['name'=>$create_vo['warehouse'],'data'=>'批量导入']);
		                }
		            }
		            $create_sql[$create_key]['warehouse']=$warehouse['id'];
		        }
		        $insert_count=db('goods')->insertAll($create_sql);
		        $resule=['state'=>'success','info'=>$insert_count];
		    }else{
		        $resule=['state'=>'error','info'=>$file->getError()];
		    }
		}
        return json($resule);
    }
}