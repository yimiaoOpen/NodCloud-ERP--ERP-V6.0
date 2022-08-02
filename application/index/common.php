<?php
//通用函数库
//登录认证
function  checklogin (){
	$userid=cookie ('Nod_User_Id');
	$usertoken=cookie ('Nod_User_Token');
	if (empty($userid) || empty($usertoken)){
		return false;
	}else {
		$sql['id']=$userid;
		$sql['token']=$usertoken;
		$m=db('user')->where($sql)->find();
		if (!empty($m)){
			Session ('is_user_id',$m['id']);
			Session ('is_merchant_id',$m['merchant']);
			return true;
		}else {
			return false;
		}
	}
}
//产生随机令牌
function  user_token (){
	$token='';
	$n='qwertyuioplkjhgfdsazxcvbnm+=-1234567890QWERTYUIOPASDFGHJKLZXCVBNM';
	for ($i=0;$i<30;$i++){
		$token.=$n[mt_rand(0,strlen($n)-1)];
	}
	return $token;
}
//操作日志
function  push_log ($text){
	$sql['text']=$text;
	$sql['user']=Session ('is_user_id');
	$sql['time']=time();
	$sql['merchant']=Session('is_merchant_id');
	db ('log')->insert ($sql);
}
//获取数据库大小
function get_mysql_size(){
    $database=config('database');
    $tabs = \think\db::query("SHOW TABLE STATUS FROM ".$database['database']);
    $size = 0;
    foreach($tabs as $vo) {
        $size += $vo["Data_length"] + $vo["Index_length"];
    }
    //转换为M
    return round(($size/1048576),2);
}
//获取授权菜单结构数据
function get_root_menu($menu){
    //1.数组改造-初始化菜单权限
    foreach ($menu as $menu_key=>$menu_vo) {
        $menu[$menu_key]['nod']=true;
    }
    //2.判断是否普通用户
    $user=user_info();//获取用户数据
    if($user['type']!=1){
        $root=db('root')->where(['pid'=>Session('is_user_id')])->select()->ToArray();//获取用户功能权限信息
        //排除空数据
        if(!empty($root)){
            //3.对菜单数据做权限标识
            //循环菜单数据
            foreach ($menu as $menu_key=>$menu_vo) {
                $root_info=searchdata($root,['name'=>['eq',$menu_vo['root']]]);//查找权限数据
                //判断用户权限数据是否存在
                if(!empty($root_info)){
                    $root_info[0]['info']==0&&($menu[$menu_key]['nod']=false);//设置权限标识
                }

            }
        }
    }
    //4.重组菜单数据[处理附属菜单数据]
    $data=searchdata($menu,['type'=>['eq',0]]);//初始化数据|内容为非附属菜单数据
    foreach ($data as $data_key=>$data_vo) {
        $sub=searchdata($menu,[
            'pid'=>['eq',$data_vo['id']],
            'type'=>['eq',1],
            'nod'=>['eq',true],
        ]);//查找附属菜单
        $data[$data_key]['subinfo']=empty($sub)?[]:$sub[0];//设置附属菜单
    }
    //5.菜单数据树结构处理
    $tree=new \org\tree();
    $menu=$tree::hTree($data);
    //6.结构化数据
    structure_menu($menu);
    return $menu;
}
//递归结构化菜单数据
function structure_menu(&$menu){
    $tag=[];
    foreach ($menu as $menu_key=>$menu_vo) {
        empty($menu_vo['subinfo'])||(array_push($tag,true));//处理附属菜单
        if(empty($menu_vo['sub'])){
            if($menu_vo['nod']){
                array_push($tag,true);
            }else{
                if(empty($menu_vo['subinfo'])){
                    unset($menu[$menu_key]);
                }
            }
        }else{
            $nod=structure_menu($menu[$menu_key]['sub']);
            if(!in_array(true,$nod)){
                unset($menu[$menu_key]);
            }
            $tag=array_merge($tag,$nod);
        }
    }
    if(!empty($tag) && !in_array(true,$tag)){
        $menu=[];
    }
    return $tag;
}
//递归首页菜单HTML
function recursion_menu($arr){
    $html='';
    foreach($arr as $k => $v){
        $html.=$v['pid']==0?'<li class="layui-nav-item">':'';//顶级菜单加入LI标签开头
        //判断是否存在子菜单
        if(empty($v['sub'])){
            if(empty($v['jump'])){
                if(isset($v['nod'])){
                    if($v['nod']){
                        $html.='<a lay-href="'.$v['url'].'"';
                    }else{
                        $html.='<a href="javascript:;"';
                    }
                }else{
                    $html.='<a lay-href="'.$v['url'].'"';
                }
                
            }else{
                if(isset($v['nod'])){
                    if($v['nod']){
                        $html.='<a href="'.$v['url'].'"target="_blank"';
                    }else{
                        $html.='<a href="javascript:;"';
                    }
                }else{
                    $html.='<a href="'.$v['url'].'"target="_blank"';
                }
            }
        }else{
            $html.='<a href="javascript:;"';//默认菜单
        }
        $html.=$v['pid']==0?' lay-tips="'.$v['name'].'" lay-direction="2">':'>';//顶级分类加入Tip提示
        $html.=empty($v['ico'])?'':'<i class="layui-icon '.$v['ico'].'"></i>';//判断是否存在图标
        $html.=$v['pid']==0?'<cite>'.$v['name'].'</cite>':$v['name'];//加入标签内容
        $html.='</a>';//加入A标签结尾
        //判断附属菜单|不存在子元素
        if(empty($v['sub'])){
            $subinfo=$v['subinfo'];
            if(!empty($subinfo)){
                $html.=empty($subinfo['jump'])?'<more><a lay-href="'.$subinfo['url'].'" lay-text="'.$subinfo['name'].'">'.$subinfo['more'].'</a></more>':'<more><a href="'.$subinfo['url'].'"target="_blank">'.$subinfo['name'].'</a></more>';
            }
        }
        if(!empty($v['sub'])){
            $html.='<dl class="layui-nav-child">';
            foreach ($v['sub'] as $sub_vo) {
                
                $html.='<dd>'.recursion_menu([$sub_vo]).'</dd>';
            }
            $html.='</dl>';
        }
        $html.=$v['pid']==0?'</li>':'';//顶级菜单加入li标签结尾
    }
    return $html;
}
//获取插件的VIEW文件
function get_plug_view($name,$html){
    return ROOT_PATH.'addons/'.$name.'/view/'.$html;
}
//监听HOOK操作
function hook_listen($nod,$params=''){
    \think\Hook::listen($nod,$params);
}
//获取表单字段配置
//传入表单标识和表单类型
function get_formfield($key,$type){
    $nod=db('formfield')->where(['key'=>$key])->find();
    empty($nod)&&(exception('[formfield]表未找到['.$key.']字段信息,请开发者核实!'));//空数据检验
    $info=arraychange(db('formfieldinfo')->where(['pid'=>$nod['id'],'show'=>1])->order('id asc')->select()->ToArray(),'info');
    //判断表单类型
    if($type=='layui'){
        $resule='['.implode(',',$info).']';//格式重组
    }elseif($type=='array'){
        $resule=[];//初始化返回值
        //循环表单字段信息
        foreach ($info as $info_vo) {
            $arr=[];
            $more_arr=explode('||',$info_vo);//分割字段配置
            foreach ($more_arr as $more_arr_vo) {
                $config=explode('//',$more_arr_vo);//分割数据配置
                $arr[$config[0]]=$config[1];
            }
            array_push($resule,$arr);
        }
    }elseif($type=='jqgrid'){
        //初始化返回值
        $colNames=[];
        $colModel=[];
        //循环表单字段信息
        foreach ($info as $info_vo) {
            $more_arr=explode('||',$info_vo);//分割字段配置
            foreach ($more_arr as $more_arr_vo) {
                $config=explode('//',$more_arr_vo);//分割数据配置
                if($config[0]=='name'){
                    array_push($colNames,"'".$config[1]."'");
                }elseif($config[0]=='model'){
                    array_push($colModel,$config[1]);
                }
            }
        }
        $resule='{"colNames":['.implode(',',$colNames).']'.',"colModel":'.'['.implode(',',$colModel).']'.'}';
    }elseif($type=='default'){
        $resule=implode('',$info);//原样输出
    }
    return $resule;
}
//获取系统配置
function get_sys($nod=[]){
    $sql=empty($nod)?[]:['name'=>['in',$nod]];
    $info=db('sys')->where($sql)->field('name,info')->select()->toarray();
    if(empty($info)){
        $resule="[ error ] 未查询到数据!";
    }else{
        if(count($info)==1){
            //单一数据
            $resule=$info[0]['info'];
        }else{
            //多数据
            $resule=[];
            foreach ($info as $vo) {
                $resule[$vo['name']]=$vo['info'];
            }
        }
    }
    return $resule;
}
//解析数据 - 字符串形式[selectpage]
function get_selectpage($model,$data){
    if(empty($data)){
        $info=[];
    }else{
        $sql['id']=['in',explode(',',$data)];
        $info=db($model)->where($sql)->field('id,name')->select()->toarray();
    }
    return json_encode($info);
}
//解析数据 - 数组形式[selectpage]
function gets_selectpage($model,$arr){
    if(empty($arr)){
        $info=[];
    }else{
        $sql['id']=['in',$arr];
        $info=db($model)->where($sql)->field('id,name')->select()->ToArray();
    }
    return $info;
}
//获取用户信息
function user_info($user_id=false){
    $sql['id']=$user_id==false?Session('is_user_id'):$user_id;
    return db('user')->where($sql)->find();
}
//SQL数据鉴权
function auth($model,$sql=[]){
    //初始化鉴权信息
    $tab=[
        'customer'=>['id'=>'customer'],
        'merchant'=>['id'=>'merchant'],
        'supplier'=>['id'=>'supplier'],
        'warehouse'=>['id'=>'warehouse'],
        'user'=>['id'=>'user','merchant'=>'merchant'],
        'account'=>['id'=>'account'],
        'log'=>['user'=>'user'],
        'accountinfo'=>['user'=>'user'],
        'integral'=>['user'=>'user'],
        'room'=>['warehouse'=>'warehouse'],
        'purchaseclass'=>['merchant'=>'merchant','supplier'=>'supplier','user'=>'user','account'=>'account'],
        'repurchaseclass'=>['merchant'=>'merchant','supplier'=>'supplier','user'=>'user','account'=>'account'],
        'opurchaseclass'=>['merchant'=>'merchant','user'=>'user'],
        'rpurchaseclass'=>['merchant'=>'merchant','supplier'=>'supplier','user'=>'user','account'=>'account'],
        'saleclass'=>['merchant'=>'merchant','customer'=>'customer','user'=>'user','account'=>'account'],
        'resaleclass'=>['merchant'=>'merchant','customer'=>'customer','user'=>'user','account'=>'account'],
        'cashierclass'=>['merchant'=>'merchant','customer'=>'customer','user'=>'user'],
        'recashierclass'=>['merchant'=>'merchant','customer'=>'customer','user'=>'user','account'=>'account'],
        'itemorderclass'=>['merchant'=>'merchant','customer'=>'customer','user'=>'user','account'=>'account'],
        'exchangeclass'=>['merchant'=>'merchant','customer'=>'customer','user'=>'user'],
        'allocationclass'=>['merchant'=>'merchant','user'=>'user'],
        'otpurchaseclass'=>['merchant'=>'merchant','user'=>'user'],
        'otsaleclass'=>['merchant'=>'merchant','user'=>'user'],
        'gatherclass'=>['merchant'=>'merchant','customer'=>'customer','user'=>'user'],
        'paymentclass'=>['merchant'=>'merchant','supplier'=>'supplier','user'=>'user'],
        'otgatherclass'=>['merchant'=>'merchant','user'=>'user'],
        'otpaymentclass'=>['merchant'=>'merchant','user'=>'user'],
        'eftclass'=>['merchant'=>'merchant','user'=>'user'],
        'summary'=>['merchant'=>'merchant','supplier'=>'supplier','customer'=>'customer','user'=>'user','account'=>'account','warehouse'=>'warehouse']
    ];
    $user=user_info();//获取用户信息
    //排除管理员
    if($user['type']!=1){
        $auth=db('auth')->where(['pid'=>Session('is_user_id')])->select()->ToArray();//获取用户数据授权信息
        //排除空数据
        if(!empty($auth)){
            $nod=$tab[$model];//获取数据鉴权项
            //循环数据鉴权项
            foreach ($nod as $nod_key => $nod_vo) {
                //排除鉴权KEY存在的情况
                //比如说当前要获取ID等于1的数据,但是ID=1是不在授权范围内的情况就跳过鉴权。
                if(!isset($sql[$nod_key])){
                    $user_auth=searchdata($auth,['name'=>['eq',$nod_vo]]);//获取用户鉴权设置信息
                    //排除鉴权数据为全部数据的情况
                    if(!empty($user_auth)){
                        $sql[$nod_key]=['in',json_decode($user_auth[0]['info'],true)];//设置SQL条件
                    }
                }
            }
        }
    }
    return $sql;
}
//获取当前商户
//返回数组形式
function get_sys_merchant(){
    $info=Session('is_merchant_info');//获取存储的商户
    return empty($info)?[]:$info;
}
//获取商户搜索数据
function get_auth_merchant(){
    $arr=[];//最后返回数据
    //1.获取系统商户信息
    $sys=get_sys_merchant();
    //2.判断数据
    //如果系统商户信息为空，则读取数据授权表数据
    //如果系统商户信息不为空，则直接读取系统商户信息
    if(empty($sys)){
        //查询数据授权数据
        $auth=db('auth')->where(['pid'=>Session('is_user_id'),'name'=>'merchant'])->find();
        //判断授权数据
        if(empty($auth)){
            //管理员和未设置权限的职员读取所有商户列表
            $arr=get_db_field('merchant');
        }else{
            //设置数据授权则直接读取
            $arr=json_decode($auth['info'],true);
        }
    }else{
        $arr=$sys;
    }
    return $arr;
}
//获取权限设置
function get_root($tag){
    $nod=true;
    $user=user_info();//获取用户信息
    //排除管理员
    if($user['type']!=1){
        $root=db('root')->where(['pid'=>Session('is_user_id')])->select()->ToArray();//获取用户权限设置信息
        if(!empty($root)){
            $user_root=searchdata($root,['name'=>['eq',$tag]]);//获取用户权限设置信息
            if(!empty($user_root)){
                if($user_root[0]['info']==0){
                    $nod=false;
                }
            }
        }
    }
    return $nod;
}
//获取仓库数据,兼容jqgrid
//{value:"FE:FedEx;IN:InTime;TN:TNT;AR:ARAMEX"}
//{value:"1:山西仓;2:北京仓;"}
function get_warehouse(){
    $jqgrid['value']='';
    $sql=auth('warehouse');
	$info=db('warehouse')->where($sql)->field('id,name')->select()->ToArray();
	if (!empty($info)){
		foreach ($info as $vo){
			$jqgrid['value'].=$vo['id'].':'.$vo['name'].';';
		}
		$jqgrid['value']=substr($jqgrid['value'],0,-1);
	}
	$resule['db']=$info;
	$resule['jqgrid']=$jqgrid;
	return json_encode($resule);
}
//获取资金账户数据,兼容jqgrid
//{value:"FE:FedEx;IN:InTime;TN:TNT;AR:ARAMEX"}
//{value:"1:支付宝;2:微信;"}
function get_account(){
    $jqgrid['value']='';
    $sql=auth('account');
	$info=db('account')->where($sql)->field('id,name')->select()->ToArray();
	if (!empty($info)){
		foreach ($info as $vo){
			$jqgrid['value'].=$vo['id'].':'.$vo['name'].';';
		}
		$jqgrid['value']=substr($jqgrid['value'],0,-1);
	}
	$resule['db']=$info;
	$resule['jqgrid']=$jqgrid;
	return json_encode($resule);
}
//查询数据库返回指定字段集
function get_db_field($model,$sql=[],$field='id'){
    $data=db($model)->where($sql)->field([$field])->select()->ToArray();
    return empty($data)?[]:arraychange($data,$field);
}
//获取单据编号
function get_number($type){
    $number=$type.date('Ymdhis',time());
    return $number;
}
//获取辅助属性名称
function attr_name($nod){
    $name='';
    $arr=explode('_',$nod);
    $info=db('attribute')->where(['id'=>['in',$arr]])->select()->ToArray();
    if(count($arr)==count($info)){
        $nod=[];
        foreach ($arr as $vo) {
            $data=searchdata($info,['id'=>['eq',$vo]]);
            array_push($nod,$data[0]['name']);
        }
        $name=implode('|', $nod);
    }else{
        $name='辅助属性丢失';
    }
    return $name;
}
//操作统计数据表
//$type单据类型
//$id类ID
//$set[true:新增|false:删除]
function set_summary($type,$id,$set){
    $summary=db('summary');
    if($type=='purchase'){
        //购货单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=1;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['supplier']=$class['supplier'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['account']=$class['account'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $sql['attr']=$vo['attr'];
                $sql['serial']=$vo['serial'];
                $sql['batch']=$vo['batch'];
                $sql['nums']=$vo['nums'];
                $sql['price']=$vo['price'];
                $sql['total']=$vo['total'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>1,'class'=>$id])->delete();
        }
    }elseif($type=='rpurchase'){
        //采购入库单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=2;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['supplier']=$class['supplier'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['account']=$class['account'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $sql['attr']=$vo['attr'];
                $sql['serial']=$vo['serial'];
                $sql['batch']=$vo['batch'];
                $sql['nums']=$vo['nums'];
                $sql['price']=$vo['price'];
                $sql['total']=$vo['total'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>2,'class'=>$id])->delete();
        }
    }elseif($type=='repurchase'){
        //购货退货单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=3;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['supplier']=$class['supplier'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['account']=$class['account'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                $sql['attr']=$room['attr'];
                $sql['batch']=$room['batch'];
                $sql['serial']=$vo['serial'];
                $sql['nums']=$vo['nums'];
                $sql['price']=$vo['price'];
                $sql['total']=$vo['total'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>3,'class'=>$id])->delete();
        }
    }elseif($type=='sale'){
        //销货单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=4;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['customer']=$class['customer'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['account']=$class['account'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                $sql['attr']=$room['attr'];
                $sql['batch']=$room['batch'];
                $sql['serial']=$vo['serial'];
                $sql['nums']=$vo['nums'];
                $sql['price']=$vo['price'];
                $sql['total']=$vo['total'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>4,'class'=>$id])->delete();
        }
    }elseif($type=='resale'){
        //销货退货单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=5;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['customer']=$class['customer'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['account']=$class['account'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                $sql['attr']=$room['attr'];
                $sql['batch']=$room['batch'];
                $sql['serial']=$vo['serial'];
                $sql['nums']=$vo['nums'];
                $sql['price']=$vo['price'];
                $sql['total']=$vo['total'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>5,'class'=>$id])->delete();
        }
    }elseif($type=='cashier'){
        //零售单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=6;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['customer']=$class['customer'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                $sql['attr']=$room['attr'];
                $sql['batch']=$room['batch'];
                $sql['serial']=$vo['serial'];
                $sql['nums']=$vo['nums'];
                $sql['price']=$vo['price'];
                $sql['total']=$vo['total'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>6,'class'=>$id])->delete();
        }
    }elseif($type=='recashier'){
        //零售退货单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=7;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['customer']=$class['customer'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['account']=$class['account'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                $sql['attr']=$room['attr'];
                $sql['batch']=$room['batch'];
                $sql['serial']=$vo['serial'];
                $sql['nums']=$vo['nums'];
                $sql['price']=$vo['price'];
                $sql['total']=$vo['total'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>7,'class'=>$id])->delete();
        }
    }elseif($type=='exchange'){
        //积分兑换单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=8;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['customer']=$class['customer'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                $sql['attr']=$room['attr'];
                $sql['batch']=$room['batch'];
                $sql['serial']=$vo['serial'];
                $sql['nums']=$vo['nums'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>8,'class'=>$id])->delete();
        }
    }elseif($type=='allocation'){
        //调拨单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=9;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                $sql['attr']=$room['attr'];
                $sql['batch']=$room['batch'];
                $sql['serial']=$vo['serial'];
                $sql['nums']=$vo['nums'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>9,'class'=>$id])->delete();
        }
    }elseif($type=='otpurchase'){
        //其他入库单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=10;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $sql['attr']=$vo['attr'];
                $sql['serial']=$vo['serial'];
                $sql['batch']=$vo['batch'];
                $sql['nums']=$vo['nums'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>10,'class'=>$id])->delete();
        }
    }elseif($type=='otsale'){
        //其他出库单-OK
        if($set){
            //新增
            $class=db($type.'class')->find($id);
            $info=db($type.'info')->where(['pid'=>$id])->select()->ToArray();
            foreach ($info as $vo) {
                $sql['merchant']=$class['merchant'];
                $sql['type']=11;
                $sql['class']=$id;
                $sql['info']=$vo['id'];
                $sql['time']=$class['time'];
                $sql['number']=$class['number'];
                $sql['user']=$class['user'];
                $sql['goods']=$vo['goods'];
                $sql['warehouse']=$vo['warehouse'];
                $sql['room']=$vo['room'];
                $room=db('room')->find(['id'=>$vo['room']]);
                $sql['attr']=$room['attr'];
                $sql['batch']=$room['batch'];
                $sql['serial']=$vo['serial'];
                $sql['nums']=$vo['nums'];
                $sql['data']=empty($vo['data'])?$class['data']:$vo['data'];
                $summary->insert($sql);
            }
        }else{
            //删除
            $summary->where(['type'=>11,'class'=>$id])->delete();
        }
    }
    return true;
}
//获取打印表格数据[HTML]
function get_print_tab($formfield,$info){
    $html='<table>';
    //1.操作表头数据
    $table_cell=[];//初始化表头数据
    //构造表头数据
    foreach ($formfield as $formfield_vo) {
        $table_cell[$formfield_vo['key']]=$formfield_vo['text'];
    }
    //2.操作表内数据
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
    //3.拼接表头HTML
    $html.='<thead><tr>';
    foreach ($table_cell as $cell_key=>$cell_vo) {
        $html.='<th nod="'.$cell_key.'">'.$cell_vo.'</th>';
    }
    $html.='</tr></thead>';
    //4.拼接表内HTML
    $html.='<tbody>';
    foreach ($table_data as $tab_data) {
        $html.='<tr>';
        foreach ($tab_data as $data_key=>$data_vo) {
            $html.='<td nod="'.$data_key.'">'.$data_vo.'</td>';
        }
        $html.='</tr>';
    }
    $html.='</tbody>';
    //5.结束标签
    $html.='</table>';
    return $html;
}
//获取打印模板
function get_print($type){
    $info=db('prints')->where(['name'=>$type])->find();
    return $info;
}
//计算当前天之前的时间-默认一周
function sum_old_day($day=7){
    $time=strtotime(date('Y-m-d',time()));//获取今天开始时间戳
    $tmp_time_arr=[];
    for ($i = 0; $i < $day; $i++) {
        array_push($tmp_time_arr,date('Y-m-d',$time-($i*86400)));
    }
    return array_reverse($tmp_time_arr);//返回反转的数组
}
//获取ECHARTS单据报表数据
function get_echarts_info($model,$field,$days){
    $data=[];//初始化返回数据
    $db=db($model);//初始化数据库
    array_walk($days,function(&$val){$val=strtotime($val);});//转换数据
    //循环查询天数
    $info=$db->where(['time'=>['in',$days],'type'=>1])->field(['time',$field])->select()->ToArray();//查询数据
    foreach ($days as $days_vo){
        $search=searchdata($info,['time'=>['eq',$days_vo]]);
        if(empty($search)){
            array_push($data,0);
        }else{
            $column=array_column($info,$field);
            array_push($data,array_sum($column));
        }
    }
    return $data;
}
//获取数据库存在指定字段的表名
function get_mysql_field($nod){
    $arr=[];
    $tables=\think\db::getTables();//获取所有数据库表名称
    foreach ($tables as $vo){
        $model=str_replace('is_','',$vo);
        $fields=db($model)->getTableFields();//读取数据表字段信息
        if(in_array($nod,$fields)){
            array_push($arr,$model);
        }
        
    }
    return $arr;
}
//获取最新版本
function new_ver(){
    $url='https://www.nodcloud.com/console/api/get_ver';
    $param=['ver'=>get_ver()];
    $resule=json_decode(http($url,$param,'POST'),true);
    return $resule['info'];
}
//获取订单编号
function auth_number(){
    $number='';
    $file = $_SERVER['DOCUMENT_ROOT'].'/application/index/key';
    if (file_exists($file)){
        $info = file_get_contents($file);
        if(!empty($info)){
            $arr=explode('|',$info);
            if(count($arr)==2){
                $number=$arr[0];
            }
        }
    }
    return $number;
}