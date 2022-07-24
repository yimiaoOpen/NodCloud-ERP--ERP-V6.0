<?php
namespace app\index\controller;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Itemorderclass;
use app\index\model\Itemorderinfo;
use app\index\model\Itemorderbill;
use app\index\model\Room;
use app\index\model\Roominfo;
use app\index\model\Account;
use app\index\model\Accountinfo;
use app\index\model\Serial;
use app\index\model\Serialinfo;
class Itemorder extends Acl {
    //服务模块
    //---------------(^_^)---------------//
    //主视图
    public function main(){
        return $this->fetch();
    }
    //新增|更新信息
    public function set(){
        $input=input('post.');
        if(isset($input['id'])){
            //验证服务单详情
            if(isset_full($input,'tab')){
                foreach ($input['tab'] as $tab_key=>$tab_vo) {
                    $tab_vali = $this->validate($tab_vo,'Itemorderinfo');//详情验证
                    if($tab_vali!==true){
                        return json(['state'=>'error','info'=>'[ 数据表格 ]第'.($tab_key+1).'行'.$tab_vali]);
                        exit;
                    }
                }
            }else{
                return json(['state'=>'error','info'=>'数据表格不可为空!']);
                exit;
            }
            //验证操作类型
            if(empty($input['id'])){
                //新增
                $input['merchant']=Session('is_merchant_id');//补充商户信息
                $vali = $this->validate($input,'Itemorderclass');
                if($vali===true){
                    $create_info=Itemorderclass::create(syn_sql($input,'itemorderclass'));
                    Hook::listen('create_itemorder',$create_info);//服务单新增行为
                    push_log('新增服务单[ '.$create_info['number'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'Itemorderclass.update');
                if($vali===true){
                    $update_info=Itemorderclass::update(syn_sql($input,'itemorderclass'));
                    Hook::listen('update_itemorder',$update_info);//服务单更新行为
                    push_log('更新服务单[ '.$update_info['number'].' ]');//日志
                    Itemorderinfo::where(['pid'=>$update_info['id']])->delete();
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }
            //添加服务单详情
            if($resule['state']=='success'){
                $info_pid=empty($input['id'])?$create_info['id']:$update_info['id'];
                foreach ($input['tab'] as $info_vo) {
                    $info_vo['pid']=$info_pid;
                    Itemorderinfo::create(syn_sql($info_vo,'itemorderinfo'));
                }
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        //兼容自动审核[新增操作]
        if($resule['state']=='success'&&empty($input['id'])){
            empty(get_sys(['auto_auditing']))||($this->auditing([$create_info['id']],true));
        }
        return json($resule);
    }
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
                'customer'=>'full_division_in',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'type'=>'full_dec_1',
                'user'=>'full_division_in',
                'account'=>'full_division_in',
                'data'=>'full_like',
            ],'itemorderclass');//构造SQL
            //处理名称搜索
            if(isset_full($input,'name')){
                $serve=get_db_field('serve',['name|py'=>['like','%'.$input['name'].'%']],'id');//取出商品表信息
                if(empty($serve)){
                    sql_assign($sql,'id',[]);//多表查询赋值处理
                }else{
                    $info=get_db_field('itemorderinfo',['serve'=>['in',$serve]],'pid');//取出详情表数据
                    sql_assign($sql,'id',$info);//多表查询赋值处理
                }
            }
            $sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            $sql=auth('itemorderclass',$sql);//数据鉴权
            $count = Itemorderclass::where ($sql)->count();//获取总条数
            $arr = Itemorderclass::with('merchantinfo,customerinfo,userinfo,accountinfo')->where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
            $class=Itemorderclass::where(['id'=>$input['id']])->find();
            $info=Itemorderinfo::with('serveinfo')->where(['pid'=>$input['id']])->select()->toarray();
            $this->assign('class',$class);
            $this->assign('info',$info);
            return $this->fetch('main');
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //审核
    public function auditing($arr=[],$auto=false){
        (empty($arr))&&($arr=input('post.arr'));//兼容多态审核
        if(empty($arr)){
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }else{
            $class_data=[];//初始化CLASS数据
            $info_data=[];//初始化INFO数据
            //初始化数据
            foreach ($arr as $arr_vo) {
                $class=Itemorderclass::where(['id'=>$arr_vo])->find();
                $info=Itemorderinfo::where(['pid'=>$arr_vo])->select();
                $class_data[$arr_vo]=$class;//转存CLASS数据
                $info_data[$arr_vo]=$info;//转存INFO数据
            }
            //实际操作
            foreach ($arr as $arr_vo) {
                $class=$class_data[$arr_vo];//读取CLASS数据
                $info=$info_data[$arr_vo];//读取INFO数据
                //判断操作类型
                if(empty($class['type']['nod'])){
                    //审核操作
                    //获取核销状态
                    if($class['money']==$class['actual']){
                        $billtype=2;//已核销
                    }elseif($class['money']==0){
                        $billtype=0;//未核销
                    }else {
                        $billtype=1;//部分核销
                    }
                    //操作核销信息
                    if (!empty($class['money'])){
                        //新增对账单
                        $bill=Itemorderbill::create(['pid'=>$arr_vo,'account'=>$class['account'],'money'=>$class['money'],'data'=>'系统自动生成','user'=>Session('is_user_id'),'time'=>time()]);
                        Account::where (['id'=>$class['account']])->setInc('balance',$class['money']);//操作资金账户[+]
                        Accountinfo::create (['pid'=>$class['account'],'set'=>1,'money'=>$class['money'],'type'=>12,'time'=>time(),'user'=>Session('is_user_id'),'class'=>$arr_vo,'bill'=>$bill['id']]);//新增资金详情
                    }
                    Itemorderclass::update(['id'=>$arr_vo,'type'=>1,'auditinguser'=>Session('is_user_id'),'auditingtime'=>time(),'billtype'=>$billtype]);//更新CLASS数据
                    push_log(($auto?'自动':'').'审核服务单[ '.$class['number'].' ]');
                }else{
                    //反审核操作
                    //操作核销信息
                    if (!empty($class['money'])){
                        $bill=Itemorderbill::where(['pid'=>$arr_vo])->select();
                        foreach ($bill as $bill_vo){
                            Account::where(['id'=>$bill_vo['account']])->setDec('balance',$bill_vo['money']);//操作资金账户[-]
                        }
                        Accountinfo::destroy (['type'=>12,'class'=>$arr_vo]);//删除资金详情
                        Itemorderbill::destroy(['pid'=>$arr_vo]);//删除对账单信息
                    }
                    Itemorderclass::update(['id'=>$arr_vo,'type'=>0,'money'=>0,'auditinguser'=>0,'auditingtime'=>0,'billtype'=>-1]);//更新CLASS数据
                    push_log ('反审核服务单[ '.$class['number'].' ]');
                }
            }
            $resule=['state'=>'success'];
        }
        return $auto?true:json($resule);
    }
    //删除信息
    public function del(){
        $input=input('post.');
        if(isset_full($input,'arr') && is_array($input['arr'])){
            $class=db('itemorderclass')->where(['id'=>['in',$input['arr']]])->select()->ToArray();//获取CLASS数据
            $data = searchdata($class,['type'=>['eq',1]]);//查询已审核单据
            //数据检验
            if(empty($data)){
                foreach ($class as $class_vo) {
                    push_log('删除服务单[ '.$class_vo['number'].' ]');//日志
                    Hook::listen('del_itemorder',$class_vo['id']);//服务单删除行为
                }
                Itemorderclass::where(['id'=>['in',$input['arr']]])->delete();
                Itemorderinfo::where(['pid'=>['in',$input['arr']]])->delete();
                $resule=['state'=>'success'];
            }else{
                $resule=['state'=>'error','info'=>'服务单[ '.$data[0]['number'].' ]已审核,不可删除!'];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出报表信息
    public function exports(){
        $input=input('get.');
        if(isset($input['mode'])){
            push_log('导出服务单数据');//日志
            $sql=get_sql($input,[
                'name'=>'continue',
                'number'=>'full_like',
                'customer'=>'full_division_in',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'type'=>'full_dec_1',
                'user'=>'full_division_in',
                'account'=>'full_division_in',
                'data'=>'full_like',
            ],'itemorderclass');//构造SQL
            //处理名称搜索
            if(isset_full($input,'name')){
                $serve=get_db_field('serve',['name|py'=>['like','%'.$input['name'].'%']],'id');//取出商品表信息
                if(empty($serve)){
                    sql_assign($sql,'id',[]);//多表查询赋值处理
                }else{
                    $info=get_db_field('itemorderinfo',['serve'=>['in',$serve]],'pid');//取出详情表数据
                    sql_assign($sql,'id',$info);//多表查询赋值处理
                }
            }
            $sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            $sql=auth('itemorderclass',$sql);//数据鉴权
            $arr = Itemorderclass::with('merchantinfo,customerinfo,userinfo,accountinfo')->where($sql)->order('id desc')->select();//查询数据
            //判断报表类型
            if(empty($input['mode'])){
                //简易报表
                $formfield=get_formfield('itemorder_export','array');//获取字段配置
                //开始构造导出数据
                $excel=[];//初始化导出数据
                //1.填充标题数据
                array_push($excel,['type'=>'title','info'=>'服务单列表']);
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
                $sum_arr=get_sums($table_data,['total','actual','money']);
                array_push($excel,['type'=>'node','info'=>[
                    '单据总金额:'.$sum_arr['total'],
                    '实际总金额:'.$sum_arr['actual'],
                    '实收总金额:'.$sum_arr['money'],
                ]]);//填充汇总信息
                //4.导出execl
                export_excel('服务单列表',$excel);
            }else{
                //详细报表
                $files=[];//初始化文件列表
                $formfield=get_formfield('itemorder_exports','array');//获取字段配置
                //循环CLASS数据
                foreach ($arr as $arr_vo) {
                    $excel=[];//初始化导出数据
                    //1.填充标题数据
                    array_push($excel,['type'=>'title','info'=>'服务单']);
                    //2.添加基础字段
                    array_push($excel,['type'=>'node','info'=>[
                        '客户:'.$arr_vo['customerinfo']['name'],
                        '',
                        '单据日期:'.$arr_vo['time'],
                        '',
                        '单据编号:'.$arr_vo['number'],
                    ]]);
                    //3.构造表格数据
                    $info=Itemorderinfo::where(['pid'=>$arr_vo['id']])->select();
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
                        '单据金额:'.$arr_vo['total'],
                        '',
                        '实际金额:'.$arr_vo['actual'],
                        '',
                        '实收金额:'.$arr_vo['money'],
                    ]]);
                    //5.添加基础字段
                    array_push($excel,['type'=>'node','info'=>[
                        '制单人:'.$arr_vo['userinfo']['name'],
                        '',
                        '结算账户:'.$arr_vo['accountinfo']['name'],
                        '',
                        '备注信息:'.$arr_vo['data'],
                    ]]);
                    $path=export_excel($arr_vo['number'],$excel,false);//生成文件
                    array_push($files,$path);//添加文件路径数据
                }
                file_to_zip('服务单明细',$files);//打包输出数据
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
            $print_name='itemorder';//模板标识
            $class=Itemorderclass::where(['id'=>$input['id']])->find();
            $info=Itemorderinfo::where(['pid'=>$input['id']])->select();
            $print_paper=get_sys(['print_paper']);
            //1.获取字段信息
            $formfield=get_formfield('itemorder_print','array');//获取字段配置
            //2.构造表格数据
            $tab_html=get_print_tab($formfield,$info);
            //3.获取模板代码
            $print=get_print($print_name);
            $print_text=$print[empty($print_paper)?'paper4':'paper2'];
            //4.赋值数据
            $this->assign('class',$class);
            $this->assign('tab_html',$tab_html);
            $this->assign('print_name',$print_name);
            $this->assign('paper_type',$print_paper);
            $this->assign('print_text',$print_text);
            return $this->fetch();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //---------------(^_^)---------------//
    //核销单
    public function bill(){
        return $this->fetch();
    }
    //核销单列表
    public function bill_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'number'=>'full_like',
                'customer'=>'full_division_in',
                'billtype'=>'full_dec_1',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'account'=>'full_division_in',
                'user'=>'full_division_in',
                'data'=>'full_like',
            ],'itemorderclass');//构造SQL
            $whereor=[];//初始化OR条件
            //处理结算账户搜索
            if(isset_full($input,'account')){
                $info=get_db_field('itemorderbill',['account'=>['in',explode(",",$input['account'])]],'pid');//取出表数据
                sql_assign($whereor,'id',$info);//多表查询赋值处理
            }
            $sql['type']=1;//补充条件
            $sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            $sql=auth('itemorderclass',$sql);//数据鉴权
            $count = Itemorderclass::where($sql)->whereor($whereor)->count();//获取总条数
            $arr = Itemorderclass::with('merchantinfo,customerinfo,userinfo,accountinfo')->where($sql)->whereor($whereor)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //获取核销信息
    public function bill_info(){
        $input=input('post.');
        if(isset_full($input,'id')){
            $resule=Itemorderbill::with('accountinfo,userinfo')->where(['pid'=>$input['id']])->order('id desc')->select();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //新增核销信息
    public function add_bill(){
        $input=input('post.');
        if(isset_full($input,'pid') && isset_full($input,'account') && isset_full($input,'money')){
            $class=Itemorderclass::where(['id'=>$input['pid']])->find();//获取CLASS数据
            $plus=bcadd($class['money'],$input['money'],config('decimal'));//初始化新金额[高精度]
            if($plus>$class['actual']){
                $resule=['state'=>'error','info'=>'结算金额不可超出未结算金额!'];
            }else{
                //1.操作CLASS数据
                $billtype=($plus==$class['actual'])?2:1;//获取核销状态
                Itemorderclass::where(['id'=>$input['pid']])->update(['billtype'=>$billtype,'money'=>$plus]);//更新CLASS数据
                //2.操作核销数据
                $input['time']=time();
                $input['user']=Session('is_user_id');
                $create_info=Itemorderbill::create(syn_sql($input,'itemorderbill'));
                Hook::listen('create_itemorderbill',$create_info);//服务核销单新增行为
                //3.操作资金账户
                Account::where (['id'=>$input['account']])->setInc('balance',$input['money']);//操作资金账户[+]
                Accountinfo::create (['pid'=>$input['account'],'set'=>1,'money'=>$input['money'],'type'=>12,'time'=>time(),'user'=>Session('is_user_id'),'class'=>$class['id'],'bill'=>$create_info['id']]);//新增资金详情
                //4.返回数据
                push_log('添加服务核销单信息[ '.$class['number'].' ]');//日志
                $bill=Itemorderbill::with('accountinfo,userinfo')->where(['id'=>$create_info['id']])->find();
                $resule=['state'=>'success','info'=>$bill];
            }
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //删除核销信息
    public function del_bill(){
        $input=input('post.');
        if(isset_full($input,'id')){
            //1.操作BILL数据
            $bill=Itemorderbill::where(['id'=>$input['id']])->find();//获取BILL数据
            Itemorderbill::where(['id'=>$input['id']])->delete();//删除BILL数据
            Hook::listen('del_itemorderbill',$bill);//服务核销单删除行为
            //2.操作资金账户
            Account::where(['id'=>$bill['account']])->setDec('balance',$bill['money']);//操作资金账户[-]
            Accountinfo::where(['type'=>12,'bill'=>$bill['id']])->delete();//删除资金详情
            //3.操作CLASS数据
            $class=Itemorderclass::where(['id'=>$bill['pid']])->find();//获取CLASS数据
            $reduce=bcsub($class['money'],$bill['money'],config('decimal'));//初始化新金额[高精度]
            $billtype=($reduce>0)?1:0;//获取核销状态
            Itemorderclass::where(['id'=>$bill['pid']])->update(['billtype'=>$billtype,'money'=>$reduce]);//更新CLASS数据
            //4.返回数据
            push_log('删除服务核销单信息[ '.$class['number'].' ]');//日志
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //强制核销
    public function force_bill(){
        $input=input('post.');
        if(isset_full($input,'id')){
            //1.操作CLASS数据
            $class=Itemorderclass::where(['id'=>$input['id']])->find();//获取CLASS数据
            Itemorderclass::where(['id'=>$input['id']])->update(['billtype'=>3]);//更新CLASS数据
            //2.返回数据
            push_log('强制核销服务核销单[ '.$class['number'].' ]');//日志
            $resule=['state'=>'success'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //批量核销
    public function set_bills(){
        $input=input('post.');
        if(isset_full($input,'arr') && isset_full($input,'account') && isset_full($input,'money')){
            $number_arr=[];//初始化单据号数组
            $tip='批量核销服务单[ '.date('YmdHi',time()).' ]';
            $account=$input['account'];//获取结算账户
            $money=$input['money'];//初始获取总结算金额
            $data=isset_full($input,'data')?$input['data']:$tip;
            $class=Itemorderclass::where(['id'=>['in',$input['arr']]])->select();//获取CLASS数据
            foreach ($class as $class_vo){
                //判断总结算金额[高精度]
                if(bccomp($money,0,config('decimal'))==0){
                    break;//跳出循环
                }elseif(!in_array($class_vo['billtype']['nod'],[0,1])){
                    continue;//跳过当前循环
                }else{
                    //1.初始化相关数据
                    $difference=bcsub($class_vo['actual'],$class_vo['money'],config('decimal'));//获取当前单据未结算金额[高精度]
                    $this_money=(bccomp($money,$difference,config('decimal'))==1)?$difference:$money;//获取本次结算金额[高精度]
                    $plus=bcadd($class_vo['money'],$this_money,config('decimal'));//初始化新金额[高精度]
                    //2.操作CLASS数据
                    $billtype=($plus==$class_vo['actual'])?2:1;//获取核销状态
                    Itemorderclass::where(['id'=>$class_vo['id']])->update(['billtype'=>$billtype,'money'=>$plus]);//更新CLASS数据
                    //3.操作核销数据
                	$create_bill=Itemorderbill::create([
                	    'pid'=>$class_vo['id'],
                	    'account'=>$account,
                	    'money'=>$this_money,
                	    'data'=>$data,
                	    'user'=>Session('is_user_id'),
                	    'time'=>time()
                	]);
                	Hook::listen('create_itemorderbill',$create_bill);//服务核销单新增行为
                	//4.操作资金账户
                	Account::where (['id'=>$account])->setInc('balance',$this_money);//操作资金账户[+]
                	Accountinfo::create (['pid'=>$account,'set'=>1,'money'=>$this_money,'type'=>12,'time'=>time(),'user'=>Session('is_user_id'),'class'=>$class_vo['id'],'bill'=>$create_bill['id'],'data'=>$data]);//新增资金详情
                	//5.更新数据
                	array_push($number_arr,$class_vo['number']);
                    $money=bcsub($money,$this_money,config('decimal'));//递减未结算总额[高精度]
                }
            }
            if(!empty($number_arr)){
                push_log($tip.' - 批量核销总金额为[ '.$input['money'].' ] - 实际核销单据号为[ '.implode(' | ',$number_arr).' ]');//日志
            }
        	$resule=['state'=>'success','info'=>$number_arr];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出核销信息
    public function bill_export(){
        $input=input('get.');
        $sql=get_sql($input,[
            'number'=>'full_like',
            'customer'=>'full_division_in',
            'billtype'=>'full_dec_1',
            'start_time'=>'stime',
            'end_time'=>'etime',
            'account'=>'full_division_in',
            'user'=>'full_division_in',
            'data'=>'full_like',
        ],'itemorderclass');//构造SQL
        $whereor=[];//初始化OR条件
        //处理结算账户搜索
        if(isset_full($input,'account')){
            $info=get_db_field('itemorderbill',['account'=>['in',explode(",",$input['account'])]],'pid');//取出表数据
            sql_assign($whereor,'id',$info);//多表查询赋值处理
        }
        $sql['type']=1;//补充条件
        $sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
        $sql=auth('itemorderclass',$sql);//数据鉴权
        $arr = Itemorderclass::with('merchantinfo,customerinfo,userinfo,accountinfo')->where($sql)->order('id desc')->select();//查询数据
        $formfield=get_formfield('itemorderbill_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'服务核销单信息']);
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
        $sum_arr=get_sums($table_data,['total','actual','money']);
        array_push($excel,['type'=>'node','info'=>[
            '单据总金额:'.$sum_arr['total'],
            '实际总金额:'.$sum_arr['actual'],
            '实收总金额:'.$sum_arr['money'],
        ]]);//填充汇总信息
        //4.导出execl
        push_log('导出服务核销单信息');//日志
        export_excel('服务核销单信息',$excel);
    }
}