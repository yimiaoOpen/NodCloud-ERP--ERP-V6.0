<?php
namespace app\index\controller;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Opurchaseclass;
use app\index\model\Opurchaseinfo;
use app\index\model\Rpurchaseclass;
use app\index\model\Rpurchaseinfo;
use app\index\model\Attr;
class Orpurchase extends Acl {
    //采购入库详情单模块
    //---------------(^_^)---------------//
    //报表视图
    public function form(){
        return $this->fetch();
    }
    //报表列表
    public function form_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'continue',
                'number'=>'full_like',
                'storage'=>'full_dec_1',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'user'=>'full_division_in',
                'data'=>'full_like'
            ],'opurchaseclass');//构造SQL
            //处理名称搜索
            if(isset_full($input,'name')){
                $goods=get_db_field('goods',['name|py'=>['like','%'.$input['name'].'%']],'id');//取出商品表信息
                if(empty($goods)){
                    sql_assign($sql,'id',[]);//多表查询赋值处理
                }else{
                    $info=get_db_field('opurchaseinfo',['goods'=>['in',$goods]],'pid');//取出详情表数据
                    sql_assign($sql,'id',$info);//多表查询赋值处理
                }
            }
            $sql['type']=1;//补充审核状态
            $sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            $sql=auth('opurchaseclass',$sql);//数据鉴权
            $count = Opurchaseclass::where ($sql)->count();//获取总条数
            $arr = Opurchaseclass::with('merchantinfo,userinfo')->where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //详情
    public function info(){
        $input=input('get.');
        //数据完整性判断
        if(isset_full($input,'id')){
            $class=Opurchaseclass::where(['id'=>$input['id']])->find();
            $info=Opurchaseinfo::with('goodsinfo')->where(['pid'=>$input['id']])->select()->ToArray();
            foreach ($info as $info_key=>$info_vo){
                //补充差异数量
                $info[$info_key]['surplusnums']=opt_decimal(bcsub($info_vo['nums'],$info_vo['readynums'],config('decimal')));//计算差异数[高精度]
                //根据辅助属性重新赋值价格信息
                if(!empty($info_vo['attr']['nod']) && $info_vo['attr']['name']!='辅助属性丢失'){
                    $attr=Attr::where(['pid'=>$info_vo['goods'],'nod'=>$info_vo['attr']['nod']])->find();
                    $info[$info_key]['price']=$attr['buy'];
                }else{
                    $info[$info_key]['price']=$info_vo['goodsinfo']['buy'];
                }
            }
            $this->assign('class',$class);
            $this->assign('info',$info);
            return $this->fetch('main');
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //新增信息
    public function set(){
        $input=input('post.');
        if(isset_full($input,'oid')){
            //验证采购订单操作详情
            if(isset_full($input,'tab')){
                foreach ($input['tab'] as $tab_key=>$tab_vo) {
                    $tab_vali = $this->validate($tab_vo,'Orpurchaseinfo');//详情验证
                    if($tab_vali!==true){
                        return json(['state'=>'error','info'=>'[ 数据表格 ]第'.($tab_key+1).'行'.$tab_vali]);
                        exit;
                    }
                }
            }else{
                return json(['state'=>'error','info'=>'数据表格不可为空!']);
                exit;
            }
            //新增操作
            $input['merchant']=Session('is_merchant_id');//补充商户信息
            $vali = $this->validate($input,'Orpurchaseclass');
            if($vali===true){
                $create_info=Rpurchaseclass::create(syn_sql($input,'rpurchaseclass'));
                Hook::listen('create_rpurchase',$create_info);//采购入库单新增行为
                push_log('新增采购入库单[ '.$create_info['number'].' ]');//日志
                $resule=['state'=>'success'];
            }else{
                $resule=['state'=>'error','info'=>$vali];
            }
            //添加采购订单详情
            if($resule['state']=='success'){
                $info=Opurchaseinfo::where(['pid'=>$input['oid']])->select()->ToArray();//查询采购订单详情表数据
                foreach ($input['tab'] as $info_vo) {
                    $info_vo['pid']=$create_info['id'];
                    //转存采购订单详情数据
                    $data=searchdata($info,['id'=>['eq',$info_vo['oid']]]);
                    $info_vo['goods']=$data[0]['goods'];//商品ID数据
                    empty($data[0]['attr']['nod'])||($info_vo['attr']=$data[0]['attr']['nod']);//转存辅助属性
                    Rpurchaseinfo::create(syn_sql($info_vo,'rpurchaseinfo'));
                }
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        //兼容自动审核[新增操作]
        if($resule['state']=='success'&&empty($input['id'])){
            $rpurchase=controller('Rpurchase');
            empty(get_sys(['auto_auditing']))||($rpurchase->auditing([$create_info['id']],true));
        }
        return json($resule);
    }
    //导出报表信息
    public function exports(){
        $input=input('get.');
        if(isset($input['mode'])){
            push_log('导出采购入库详情单数据');//日志
            $sql=get_sql($input,[
                'name'=>'continue',
                'number'=>'full_like',
                'storage'=>'full_dec_1',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'user'=>'full_division_in',
                'data'=>'full_like'
            ],'opurchaseclass');//构造SQL
            //处理名称搜索
            if(isset_full($input,'name')){
                $goods=get_db_field('goods',['name|py'=>['like','%'.$input['name'].'%']],'id');//取出商品表信息
                if(empty($goods)){
                    sql_assign($sql,'id',[]);//多表查询赋值处理
                }else{
                    $info=get_db_field('opurchaseinfo',['goods'=>['in',$goods]],'pid');//取出详情表数据
                    sql_assign($sql,'id',$info);//多表查询赋值处理
                }
            }
            $sql['type']=1;//补充审核状态
            $sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            $sql=auth('opurchaseclass',$sql);//数据鉴权
            $arr = Opurchaseclass::with('merchantinfo,userinfo')->where($sql)->order('id desc')->select();//查询数据
            //判断报表类型
            if(empty($input['mode'])){
                //简易报表
                $formfield=get_formfield('orpurchase_export','array');//获取字段配置
                //开始构造导出数据
                $excel=[];//初始化导出数据
                //1.填充标题数据
                array_push($excel,['type'=>'title','info'=>'采购入库详情单列表']);
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
                export_excel('采购订单列表',$excel);
            }else{
                //详细报表
                $files=[];//初始化文件列表
                $formfield=get_formfield('orpurchase_exports','array');//获取字段配置
                //循环CLASS数据
                foreach ($arr as $arr_vo) {
                    $excel=[];//初始化导出数据
                    //1.填充标题数据
                    array_push($excel,['type'=>'title','info'=>'采购入库详情单']);
                    //2.添加基础字段
                    array_push($excel,['type'=>'node','info'=>[
                        '单据日期:'.$arr_vo['time'],
                        '',
                        '单据编号:'.$arr_vo['number'],
                    ]]);
                    //3.构造表格数据
                    $info=Opurchaseinfo::where(['pid'=>$arr_vo['id']])->select();
                    foreach ($info as $info_key=>$info_vo) {
                        $info[$info_key]['surplusnums']=bcsub($info_vo['nums'],$info_vo['readynums'],config('decimal'));//计算差异数[高精度]
                    }
                    $table_cell=[];//初始化表头数据
                    //构造表头数据
                    foreach ($formfield as $formfield_vo) {
                        $table_cell[$formfield_vo['key']]=$formfield_vo['text'];
                    }
                    $table_data=[];//初始化表内数据
                    //构造表内数据
                    foreach ($info as $info_vo) {
                        $row_data=[];
                        //循环字段配置
                        foreach ($formfield as $formfield_vo) {
                            $val='nod_initial';//初始化数据
                            //循环匹配数据源
                            foreach (explode('|',$formfield_vo['data']) as $source) {
                                $val=$val=='nod_initial'?$info_vo[$source]:(isset($val[$source])?$val[$source]:'');
                            }
                            $row_data[$formfield_vo['key']]=$val;//数据赋值
                        }
                        array_push($table_data,$row_data);//加入行数据
                    }
                    array_push($excel,['type'=>'table','info'=>['cell'=>$table_cell,'data'=>$table_data]]);//填充表内数据
                    //4.添加基础字段
                    array_push($excel,['type'=>'node','info'=>[
                        '制单人:'.$arr_vo['userinfo']['name'],
                        '',
                        '备注信息:'.$arr_vo['data'],
                    ]]);
                    $path=export_excel($arr_vo['number'],$excel,false);//生成文件
                    array_push($files,$path);//添加文件路径数据
                }
                file_to_zip('采购入库详情单明细',$files);//打包输出数据
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //打印
    public function prints(){
        $input=input('get.');
        if(isset_full($input,'id')){
            $print_name='orpurchase';//模板标识
            $class=Opurchaseclass::where(['id'=>$input['id']])->find();
            $info=Opurchaseinfo::where(['pid'=>$input['id']])->select();
            foreach ($info as $info_key=>$info_vo) {
                $info[$info_key]['surplusnums']=bcsub($info_vo['nums'],$info_vo['readynums'],config('decimal'));//计算差异数[高精度]
            }
            $sys=get_sys(['print_paper']);
            //1.获取字段信息
            $formfield=get_formfield('orpurchase_print','array');//获取字段配置
            //2.构造表格数据
            $tab_html=get_print_tab($formfield,$info);
            //3.获取模板代码
            $print=get_print($print_name);
            $print_text=$print[empty($sys)?'paper4':'paper2'];
            //4.赋值数据
            $this->assign('class',$class);
            $this->assign('tab_html',$tab_html);
            $this->assign('print_name',$print_name);
            $this->assign('paper_type',$sys);
            $this->assign('print_text',$print_text);
            return $this->fetch();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}