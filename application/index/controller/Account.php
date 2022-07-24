<?php
namespace app \index \controller ;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Account as Accounts;
use app\index\controller\Formfield;
use app\index\model\Accountinfo;
class Account extends Acl {
    //资金账户模块
    //---------------(^_^)---------------//
    //资金账户视图
    public function main(){
        return $this->fetch();
    }
    //资金账户列表
    public function account_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'number'=>'full_like',
                'data'=>'full_like'
            ],'account');//构造SQL
            $sql=auth('account',$sql);//数据鉴权
            $count = Accounts::where ($sql)->count();//获取总条数
            $arr = Accounts::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //新增|更新资金账户信息
    public function set_account(){
        $input=input('post.');
        if(isset($input['id'])){
            if(empty($input['id'])){
                //新增
                $vali = $this->validate($input,'account');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $create_info=Accounts::create(syn_sql($input,'account'));
                    Hook::listen('create_account',$create_info);//资金账户新增行为
                    push_log('新增资金账户信息[ '.$create_info['name'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'account.update');
                if($vali===true){
                    $input['py']=zh2py($input['name']);//首拼信息
                    $update_info=Accounts::update(syn_sql($input,'account'));
                    Hook::listen('update_account',$update_info);//资金账户更新行为
                    push_log('更新资金账户信息[ '.$update_info['name'].' ]');//日志
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
    //获取资金账户信息
    public function get_account(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Accounts::where(['id'=>$input['id']])->find();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除资金账户信息
    public function del_account(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            //查询数据是否存在
            $exist=more_table_find([
            	['table'=>'cashierclass','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'eftinfo','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'gatherinfo','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'itemorderbill','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'itemorderclass','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'otgatherinfo','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'otpaymentinfo','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'paymentinfo','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'purchasebill','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'purchaseclass','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'recashierclass','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'repurchasebill','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'repurchaseclass','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'resalebill','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'resaleclass','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'rpurchasebill','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'rpurchaseclass','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'salebill','where'=>['account'=>['in',$input['arr']]]],
            	['table'=>'saleclass','where'=>['account'=>['in',$input['arr']]]]
            ]);
            //判断数据是否存在
            if(!$exist){
            	$info=db('account')->where(['id'=>['in',$input['arr']]])->select();//获取删除信息
                foreach ($info as $info_vo) {
                    push_log('删除资金账户信息[ '.$info_vo['name'].' ]');//日志
                    Hook::listen('del_account',$info_vo['id']);//资金账户删除行为
                }
                Accounts::where(['id'=>['in',$input['arr']]])->delete();
                $resule=['state'=>'success'];
            }else{
            	$resule=['state'=>'error','info'=>'存在数据关联,删除失败!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出资金账户信息
    public function export_account(){
        $input=input('get.');
        $sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'number'=>'full_like',
            'data'=>'full_like'
        ],'account');//构造SQL
        $arr = Accounts::where($sql)->order('id desc')->select();//查询数据
        $formfield=get_formfield('account_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'资金账户列表']);
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
        //3.添加汇总信息
        $sum_arr=get_sums($table_data,['balance']);
        array_push($excel,['type'=>'node','info'=>['资金余额汇总:'.$sum_arr['balance']]]);//填充汇总信息
        //4.导出execl
        push_log('导出资金账户信息');//日志
        export_excel('资金账户列表',$excel);
    }
    //---------------(^_^)---------------//
    //资金明细视图
    public function accountinfo(){
        return $this->fetch();
    }
    //资金明细列表
    public function accountinfo_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'pid') && isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'type'=>'full_eq',
                'set'=>'full_dec_1',
                'user'=>'full_division_in',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'data'=>'full_like'
            ],'accountinfo');//构造SQL
            $sql=auth('accountinfo',$sql);//数据鉴权
            $count = Accountinfo::where ($sql)->count();//获取总条数
            $arr = Accountinfo::with('userinfo,typedata')->where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //导出资金明细信息
    public function export_accountinfo(){
        $input=input('get.');
        $sql=get_sql($input,[
            'type'=>'full_eq',
            'set'=>'full_dec_1',
            'user'=>'full_division_in',
            'start_time'=>'stime',
            'end_time'=>'etime',
            'data'=>'full_like'
        ],'accountinfo');//构造SQL
        $sql=auth('accountinfo',$sql);//数据鉴权
        $info=Accounts::where(['id'=>$input['pid']])->find();//获取资金账户信息
        $arr = Accountinfo::with('userinfo,typedata')->where($sql)->order('id desc')->select();//查询数据
        $formfield=get_formfield('accountinfo_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'资金账户['.$info['name'].']明细信息']);
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
        array_push($excel,[
            'type'=>'node',
            'info'=>[
                '开账日期:',$info['createtime'],
                '期初余额:',$info['initial'],
                '剩余资余额:',$info['balance']
            ]
        ]);
        //4.导出execl
        push_log('导出资金账户['.$info['name'].']明细信息');//日志
        export_excel('资金账户['.$info['name'].']明细信息',$excel);
    }
}