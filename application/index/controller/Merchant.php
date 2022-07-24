<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Merchant as Merchants;
use app\index\controller\Formfield;
use app\index\model\Log;
class Merchant extends Acl {
    //商户模块
    //---------------(^_^)---------------//
    //商户视图
    public function main(){
        return $this->fetch();
    }
    //商户列表
    public function merchant_list(){
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
            ],'merchant');//构造SQL
            $sql=auth('merchant',$sql);//数据鉴权
            $count = Merchants::where ($sql)->count();//获取总条数
            $arr = Merchants::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新商户信息
    public function set_merchant(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'Merchant');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Merchants::create(syn_sql($input,'merchant'));
                    
                    Hook::listen('create_merchant',$create_info);//商户新增行为
                    push_log('新增商户信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'Merchant.update');
                if($vali===true){
                    $input['id']==1&&($input['type']=0);//兼容主商户
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Merchants::update(syn_sql($input,'merchant'));
                    Hook::listen('update_merchant',$update_info);//商户更新行为
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
    //获取商户信息
    public function get_merchant(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Merchants::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除商户信息
    public function del_merchant(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据是否存在
            $exist=more_table_find([
            	['table'=>'allocationclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'cashierclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'eftclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'exchangeclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'gatherclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'itemorderclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'opurchaseclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'otgatherclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'otpaymentclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'otpurchaseclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'otsaleclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'paymentclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'purchaseclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'recashierclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'repurchaseclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'resaleclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'saleclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'rpurchaseclass','where'=>['merchant'=>['in',$input['arr']]]],
            	['table'=>'user','where'=>['merchant'=>['in',$input['arr']]]],
            ]);
            //判断数据是否存在
            if(!$exist){
            	//判断主商户不可删除
                if(in_array(1,$input['arr'])){
                    $resule=['state'=>'error','info'=>'主商户不可删除!'];
                }else{
                    $info=db('merchant')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                    foreach ($info as $info_vo) {
                        push_log('删除商户信息[ '.$info_vo['name'].' ]');//日志
                        Hook::listen('del_merchant',$info_vo['id']);//客户删除行为
                    }
                    Merchants::where(['id'=>['in',$input['arr']]])->delete();
                    Log::where(['merchant'=>['in',$input['arr']]])->delete();//删除日志信息
                    $resule=['state'=>'success'];
                }
            }else{
            	$resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出商户信息
    public function export_merchant(){
        $input=input('get.');
        $sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'number'=>'full_like',
            'contacts'=>'full_like',
            'tel'=>'full_like',
            'add'=>'full_like',
            'data'=>'full_like'
        ],'merchant');//构造SQL
        $sql=auth('merchant',$sql);//数据鉴权
        $arr = Merchants::where($sql)->order('id desc')->select();//查询数据
        $formfield=get_formfield('merchant_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'商户列表']);
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
        export_excel('商户列表',$excel);
    }
}