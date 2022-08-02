<?php
namespace app\index\controller;
use think\Hook;
use app\index\controller\Acl;
use app\index\model\Exchangeclass;
use app\index\model\Exchangeinfo;
use app\index\model\Room;
use app\index\model\Roominfo;
use app\index\model\Serial;
use app\index\model\Serialinfo;
use app\index\model\Customer;
use app\index\model\Integral;
class Exchange extends Acl {
    //积分兑换模块
    //---------------(^_^)---------------//
    //主视图
    public function main(){
        return $this->fetch();
    }
    //新增|更新信息
    public function set(){
        $input=input('post.');
        if(isset($input['id'])){
            //验证积分兑换单详情
            if(isset_full($input,'tab')){
                foreach ($input['tab'] as $tab_key=>$tab_vo) {
                    $tab_vali = $this->validate($tab_vo,'Exchangeinfo');//详情验证
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
                $vali = $this->validate($input,'Exchangeclass');
                if($vali===true){
                    $create_info=Exchangeclass::create(syn_sql($input,'exchangeclass'));
                    Hook::listen('create_exchange',$create_info);//积分兑换单新增行为
                    push_log('新增积分兑换单[ '.$create_info['number'].' ]');//日志
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }else{
                //更新
                $vali = $this->validate($input,'Exchangeclass.update');
                if($vali===true){
                    $update_info=Exchangeclass::update(syn_sql($input,'exchangeclass'));
                    Hook::listen('update_exchange',$update_info);//积分兑换单更新行为
                    push_log('更新积分兑换单[ '.$update_info['number'].' ]');//日志
                    Exchangeinfo::where(['pid'=>$update_info['id']])->delete();
                    $resule=['state'=>'success'];
                }else{
                    $resule=['state'=>'error','info'=>$vali];
                }
            }
            //添加积分兑换单详情
            if($resule['state']=='success'){
                $info_pid=empty($input['id'])?$create_info['id']:$update_info['id'];
                foreach ($input['tab'] as $info_vo) {
                    $info_vo['pid']=$info_pid;
                    (isset_full($info_vo,'serial')&& $info_vo['serial']=='&amp;nbsp;')&&($info_vo['serial']='');//兼容串码
                    Exchangeinfo::create(syn_sql($info_vo,'exchangeinfo'));
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
                'warehouse'=>'continue',
                'user'=>'full_division_in',
                'data'=>'full_like',
            ],'exchangeclass');//构造SQL
            //处理名称搜索
            if(isset_full($input,'name')){
                $goods=get_db_field('goods',['name|py'=>['like','%'.$input['name'].'%']],'id');//取出商品表信息
                if(empty($goods)){
                    sql_assign($sql,'id',[]);//多表查询赋值处理
                }else{
                    $info=get_db_field('exchangeinfo',['goods'=>['in',$goods]],'pid');//取出详情表数据
                    sql_assign($sql,'id',$info);//多表查询赋值处理
                }
            }
            //处理仓库搜索
            if(isset_full($input,'warehouse')){
                $info=get_db_field('exchangeinfo',['warehouse'=>['in',explode(",",$input['warehouse'])]],'pid');//取出详情表数据
                sql_assign($sql,'id',$info,'intersect');//多表查询赋值处理
            }
            $sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            $sql=auth('exchangeclass',$sql);//数据鉴权
            $count = Exchangeclass::where ($sql)->count();//获取总条数
            $arr = Exchangeclass::with('merchantinfo,customerinfo,userinfo')->where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
            $class=Exchangeclass::where(['id'=>$input['id']])->find();
            $info=Exchangeinfo::with('roominfo,goodsinfo,warehouseinfo')->where(['pid'=>$input['id']])->select()->toarray();
            foreach ($info as $info_key=>$info_vo) {
                //改造串码数据
                $info[$info_key]['roominfo']['serialinfo']=implode(",",arraychange(searchdata($info_vo['roominfo']['serialinfo'],['type|nod'=>['eq',0]]),'code'));
            }
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
            //数据检验
            foreach ($arr as $arr_vo) {
                $class=Exchangeclass::where(['id'=>$arr_vo])->find();
                $info=Exchangeinfo::where(['pid'=>$arr_vo])->select();
                //判断操作类型
                if(empty($class['type']['nod'])){
                    //审核操作
                    foreach ($info as $info_key=>$info_vo) {
                        if(!empty($info_vo['serial'])){
                            $serial_sql=['code'=>['in',explode(',',$info_vo['serial'])],'type'=>['neq',0]];
                            $serial=Serial::where($serial_sql)->find();//查找串码状态为非未销售
                            if(!empty($serial)){
                                $auto&&(push_log('自动审核积分兑换单[ '.$class['number'].' ]失败,原因:第'.($info_key+1).'行串码状态不正确!'));//日志
                                return json(['state'=>'error','info'=>'审核-积分兑换单[ '.$class['number'].' ]失败,原因:第'.($info_key+1).'行串码状态不正确!']);
                                exit;
                            }
                        }
                    }
                }else{
                    //反审核操作
                    foreach ($info as $info_key=>$info_vo) {
                        if(!empty($info_vo['serial'])){
                            $serial_sql=['code'=>['in',explode(',',$info_vo['serial'])],'type'=>['neq',1]];
                            $serial=Serial::where($serial_sql)->find();//查找串码状态为非已销售
                            if(!empty($serial)){
                                return json(['state'=>'error','info'=>'反审核-积分兑换单[ '.$class['number'].' ]第'.($info_key+1).'行串码状态不正确!']);
                                exit;
                            }
                        }
                    }
                }
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
                    foreach ($info as $info_vo) {
                        //设置仓储信息
                        Room::where(['id'=>$info_vo['room']])->setDec('nums',$info_vo['nums']);//更新仓储数据[-]
                        //新增仓储详情
                        $roominfo_sql['pid']=$info_vo['room'];
                        $roominfo_sql['type']=12;
                        $roominfo_sql['class']=$arr_vo;
                        $roominfo_sql['info']=$info_vo['id'];
                        $roominfo_sql['nums']=$info_vo['nums'];
                        Roominfo::create($roominfo_sql);
                        //操作串码信息
                        if (!empty($info_vo['serial'])){
                            $serial_arr=explode(',',$info_vo['serial']);//分割串码信息
                            foreach ($serial_arr as $serial_arr_vo) {
                                //设置串码信息
                                $serial=Serial::where(['code'=>$serial_arr_vo])->find();//获取串码信息
                                Serial::update(['id'=>$serial['id'],'type'=>1]);
                                //新增串码详情
                                Serialinfo::create (['pid'=>$serial['id'],'type'=>11,'class'=>$arr_vo]);
                            }
                        }
                    }
                    //处理客户积分
                    if(!empty($class['integral'])){
                        Customer::where (['id'=>$class['customer']])->setDec('integral',$class['integral']);//操作客户积分[-]
                        Integral::create(['pid'=>$class['customer'],'set'=>0,'integral'=>$class['integral'],'type'=>4,'time'=>time(),'user'=>Session('is_user_id'),'class'=>$arr_vo]);//新增积分详情
                    }
                    Exchangeclass::update(['id'=>$arr_vo,'type'=>1,'auditinguser'=>Session('is_user_id'),'auditingtime'=>time()]);//更新CLASS数据
                    set_summary('exchange',$arr_vo,true);//更新统计表
                    push_log(($auto?'自动':'').'审核积分兑换单[ '.$class['number'].' ]');
                }else{
                    //反审核操作
                    foreach ($info as $info_vo){
                        Room::where (['id'=>$info_vo['room']])->setInc('nums',$info_vo['nums']);//更新仓储数据[+]
                        if(!empty($info_vo['serial'])){
                            $serial=Serial::where(['code'=>['in',explode(',',$info_vo['serial'])]])->select();//获取串码数据
                            foreach ($serial as $serial_vo) {
                                //设置串码数据
                                Serial::update(['id'=>$serial_vo['id'],'type'=>0]);
                                Serialinfo::where(['pid'=>$serial_vo['id'],'type'=>11,'class'=>$arr_vo])->delete();//删除串码详情
                            }
                        }
                    }
                    Roominfo::where(['type'=>12,'class'=>$arr_vo])->delete();//删除仓储详情
                    //处理客户积分
                    if(!empty($class['integral'])){
                        Customer::where (['id'=>$class['customer']])->setInc('integral',$class['integral']);//操作客户积分[+]
                        Integral::destroy(['type'=>4,'class'=>$arr_vo]);//删除积分详情
                    }
                    Exchangeclass::update(['id'=>$arr_vo,'type'=>0,'auditinguser'=>0,'auditingtime'=>0]);//更新CLASS数据
                    set_summary('exchange',$arr_vo,false);//更新统计表
                    push_log ('反审核积分兑换单[ '.$class['number'].' ]');
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
            $class=db('exchangeclass')->where(['id'=>['in',$input['arr']]])->select()->ToArray();//获取CLASS数据
            $data = searchdata($class,['type'=>['eq',1]]);//查询已审核单据
            //数据检验
            if(empty($data)){
                foreach ($class as $class_vo) {
                    push_log('删除积分兑换单[ '.$class_vo['number'].' ]');//日志
                    Hook::listen('del_exchange',$class_vo['id']);//积分兑换单删除行为
                }
                Exchangeclass::where(['id'=>['in',$input['arr']]])->delete();
                Exchangeinfo::where(['pid'=>['in',$input['arr']]])->delete();
                $resule=['state'=>'success'];
            }else{
                $resule=['state'=>'error','info'=>'积分兑换单[ '.$data[0]['number'].' ]已审核,不可删除!'];
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
            push_log('导出积分兑换单数据');//日志
            $sql=get_sql($input,[
                'name'=>'continue',
                'number'=>'full_like',
                'customer'=>'full_division_in',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'type'=>'full_dec_1',
                'warehouse'=>'continue',
                'user'=>'full_division_in',
                'data'=>'full_like',
            ],'exchangeclass');//构造SQL
            //处理名称搜索
            if(isset_full($input,'name')){
                $goods=get_db_field('goods',['name|py'=>['like','%'.$input['name'].'%']],'id');//取出商品表信息
                if(empty($goods)){
                    sql_assign($sql,'id',[]);//多表查询赋值处理
                }else{
                    $info=get_db_field('exchangeinfo',['goods'=>['in',$goods]],'pid');//取出详情表数据
                    sql_assign($sql,'id',$info);//多表查询赋值处理
                }
            }
            //处理仓库搜索
            if(isset_full($input,'warehouse')){
                $info=get_db_field('exchangeinfo',['warehouse'=>['in',explode(",",$input['warehouse'])]],'pid');//取出详情表数据
                sql_assign($sql,'id',$info,'intersect');//多表查询赋值处理
            }
            $sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            $sql=auth('exchangeclass',$sql);//数据鉴权
            $arr = Exchangeclass::with('merchantinfo,customerinfo,userinfo')->where($sql)->order('id desc')->select();//查询数据
            //判断报表类型
            if(empty($input['mode'])){
                //简易报表
                $formfield=get_formfield('exchange_export','array');//获取字段配置
                //开始构造导出数据
                $excel=[];//初始化导出数据
                //1.填充标题数据
                array_push($excel,['type'=>'title','info'=>'积分兑换单列表']);
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
                $sum_arr=get_sums($table_data,['total','actual','integral']);
                array_push($excel,['type'=>'node','info'=>[
                    '单据总积分:'.$sum_arr['total'],
                    '实际总积分:'.$sum_arr['actual'],
                    '实收总积分:'.$sum_arr['integral'],
                ]]);//填充汇总信息
                //4.导出execl
                export_excel('积分兑换单列表',$excel);
            }else{
                //详细报表
                $files=[];//初始化文件列表
                $formfield=get_formfield('exchange_exports','array');//获取字段配置
                //配置字段
                $sys=get_sys(['enable_batch','enable_serial']);
                empty($sys['enable_batch'])&&(arrs_key_del($formfield,['key','batch']));
                empty($sys['enable_serial'])&&(arrs_key_del($formfield,['key','serial']));
                //循环CLASS数据
                foreach ($arr as $arr_vo) {
                    $excel=[];//初始化导出数据
                    //1.填充标题数据
                    array_push($excel,['type'=>'title','info'=>'积分兑换单']);
                    //2.添加基础字段
                    array_push($excel,['type'=>'node','info'=>[
                        '客户:'.$arr_vo['customerinfo']['name'],
                        '',
                        '单据日期:'.$arr_vo['time'],
                        '',
                        '单据编号:'.$arr_vo['number'],
                    ]]);
                    //3.构造表格数据
                    $info=Exchangeinfo::where(['pid'=>$arr_vo['id']])->select();
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
                        '单据积分:'.$arr_vo['total'],
                        '',
                        '实际积分:'.$arr_vo['actual'],
                        '',
                        '实收积分:'.$arr_vo['integral'],
                    ]]);
                    //5.添加基础字段
                    array_push($excel,['type'=>'node','info'=>[
                        '客户积分:'.$arr_vo['customerinfo']['integral'],
                        '',
                        '制单人:'.$arr_vo['userinfo']['name'],
                        '',
                        '备注信息:'.$arr_vo['data'],
                    ]]);
                    $path=export_excel($arr_vo['number'],$excel,false);//生成文件
                    array_push($files,$path);//添加文件路径数据
                }
                file_to_zip('积分兑换单明细',$files);//打包输出数据
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
            $print_name='exchange';//模板标识
            $class=Exchangeclass::where(['id'=>$input['id']])->find();
            $info=Exchangeinfo::where(['pid'=>$input['id']])->select();
            $sys=get_sys(['enable_batch','enable_serial','print_paper']);
            //1.获取字段信息
            $formfield=get_formfield('exchange_print','array');//获取字段配置
            //2.配置字段
            empty($sys['enable_batch'])&&(arrs_key_del($formfield,['key','batch']));
            empty($sys['enable_serial'])&&(arrs_key_del($formfield,['key','serial']));
            //3.构造表格数据
            $tab_html=get_print_tab($formfield,$info);
            //4.获取模板代码
            $print=get_print($print_name);
            $print_text=$print[empty($sys['print_paper'])?'paper4':'paper2'];
            //5.赋值数据
            $this->assign('class',$class);
            $this->assign('tab_html',$tab_html);
            $this->assign('print_name',$print_name);
            $this->assign('paper_type',$sys['print_paper']);
            $this->assign('print_text',$print_text);
            return $this->fetch();
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
}