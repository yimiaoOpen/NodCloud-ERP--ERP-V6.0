<?php
namespace app \index \controller ;
use think\Request;
use app\index\controller\Acl;
use app\index\model\Formfield;
use app\index\model\User;
use app\index\model\Customer;
use app\index\model\Account;
use app\index\model\Merchant;
use app\index\model\Supplier;
use app\index\model\Warehouse;
use app\index\model\Unit;
use app\index\model\Brand;
use app\index\model\Goods;
use app\index\model\Attr;
use app\index\model\Room;
use app\index\model\Roominfo;
use app\index\model\Serial;
use app\index\model\Serialinfo;
use app\index\model\Serve;
use app\index\model\Summary;
use app\index\model\Saleclass;
use app\index\model\Purchaseclass;
use app\index\model\Rpurchaseclass;
class Service extends Acl {
    //获取系统版本信息
    public function get_sys_ver(){
        $re['state']="success";
        $re['info']=[
            'now_ver'=>get_ver()
        ];
        return json($re);
    }
    //职员数据[selectpage]
    public function user_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'data'=>'full_name_py_link'
            ],'user');//构造SQL
            isset_full($input,'notauth')||($sql=auth('user',$sql));//判断是否需要数据鉴权
            $count = User::where ($sql)->count();//获取总条数
            $arr = User::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'state'=>'success',
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
    //客户数据[selectpage]
    public function customer_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'data'=>'full_name_py_link'
            ],'customer');//构造SQL
            isset_full($input,'notauth')||($sql=auth('customer',$sql));//判断是否需要数据鉴权
            $count = Customer::where ($sql)->count();//获取总条数
            $arr = Customer::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'state'=>'success',
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
    //资金账户数据[selectpage]
    public function account_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'data'=>'full_name_py_link'
            ],'account');//构造SQL
            isset_full($input,'notauth')||($sql=auth('account',$sql));//判断是否需要数据鉴权
            $count = Account::where ($sql)->count();//获取总条数
            $arr = Account::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'state'=>'success',
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
    //商户数据[selectpage]
    public function merchant_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'data'=>'full_name_py_link'
            ],'merchant');//构造SQL
            isset_full($input,'notauth')||($sql=auth('merchant',$sql));//判断是否需要数据鉴权
            $count = Merchant::where ($sql)->count();//获取总条数
            $arr = Merchant::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'state'=>'success',
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
    //供应商数据[selectpage]
    public function supplier_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'data'=>'full_name_py_link'
            ],'supplier');//构造SQL
            isset_full($input,'notauth')||($sql=auth('supplier',$sql));//判断是否需要数据鉴权
            $count = Supplier::where ($sql)->count();//获取总条数
            $arr = Supplier::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'state'=>'success',
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
    //仓库数据[selectpage]
    public function warehouse_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'data'=>'full_name_py_link'
            ],'warehouse');//构造SQL
            isset_full($input,'notauth')||($sql=auth('warehouse',$sql));//判断是否需要数据鉴权
            $count = Warehouse::where ($sql)->count();//获取总条数
            $arr = Warehouse::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'state'=>'success',
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
    //单位数据[selectpage]
    public function unit_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'data'=>'full_name_py_link'
            ],'unit');//构造SQL
            $count = Unit::where ($sql)->count();//获取总条数
            $arr = Unit::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'state'=>'success',
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
    //品牌数据[selectpage]
    public function brand_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'data'=>'full_name_py_link'
            ],'brand');//构造SQL
            $count = Brand::where ($sql)->count();//获取总条数
            $arr = Brand::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            $resule=[
                'state'=>'success',
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
    //获取系统商户
    public function get_merchant(){
        $nod=get_sys_merchant();//获取系统参数
        $info=gets_selectpage('merchant',$nod);//数据解析
        return json(['state'=>'success','info'=>$info]);
    }
    //设置系统商户
    public function set_merchant(){
        $input=input('post.');
        //数据完整性判断
        if(isset($input['merchant'])){
            if(empty($input['merchant'])){
                Session('is_merchant_info',[]);
            }else{
                Session('is_merchant_info',explode(',',$input['merchant']));
            }
            $resule=['state'=>'success','info'=>'设置成功!'];
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //基础商品列表
    public function base_goods_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'code'=>'full_eq',
                'spec'=>'full_like',
                'class'=>'full_goodsclass_tree_sub',
                'brand'=>'full_eq',
                'unit'=>'full_eq',
                'number'=>'full_like',
                'location'=>'full_like',
                'data'=>'full_like'
            ],'goods');//构造SQL
            $whereor_sql=[];//或查询SQL
            //处理辅助属性条码
            if(isset_full($input,'code')){
                $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性表条码信息
                empty($attr)||(sql_assign($whereor_sql,'id',arraychange($attr,'pid')));//多表查询赋值处理
            }
            $count = Goods::where ($sql)->whereor($whereor_sql)->count();//获取总条数
            $arr = Goods::with('classinfo,unitinfo,brandinfo,warehouseinfo,attrinfo')->where($sql)->whereor($whereor_sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
            //二次处理数据
            foreach ($arr as $key=>$vo) {
                $arr[$key]['img']=empty($vo['imgs'])?'/skin/images/main/none.png':$vo['imgs'][0];//增加图像信息
                //判断是否赋值辅助属性数据
                if(isset($attr) && !empty($attr)){
                    $code=searchdata($attr,['pid'=>['eq',$vo['id']]]);
                    if(count($code)==1){
                        $arr[$key]['attr']=$code[0];
                    }
                }
            }
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
    //单据通用上传
    public function upload_file(Request $request){
        $file=$request->file('file');//获取表单上传文件
		if (empty($file)){
		    $resule=['state'=>'error','info'=>'传入数据不完整!'];
		}else{
            //单文件限制2MB
            $nod=$file->validate (['size'=>6097152,'ext'=>'png,jpg,pdf,zip,7z,rar,doc,docs,xls,xlsx'])->rule ('uniqid')->move (ROOT_PATH .'skin'.DS .'upload'.DS .'file');
            if ($nod){
                $file_name=$nod->getSaveName ();
                $file_path='/skin/upload/file/'.$file_name;
                $resule=['state'=>'success','info'=>$file_path];
            }else {
                $resule=['state'=>'error','info'=>$file->getError()];//返回错误信息
            }
		}
		return json ($resule);
    }
    //仓储商品列表
    public function room_goods_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $room_sql=[];//初始化仓储SQL数据
            //1.匹配商品表数据
            $goods_sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'code'=>'full_eq',
                'spec'=>'full_like',
                'class'=>'full_goodsclass_tree_sub',
                'brand'=>'full_eq',
                'number'=>'full_like',
                'warehouse'=>'continue',
                'data'=>'full_like'
            ],'goods');//构造SQL
            $goods_whereor_sql=[];//或查询SQL
            //处理辅助属性条码
            if(isset_full($input,'code')){
                $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性条码信息
                if(!empty($attr)){
                    sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid'));//多表查询赋值处理[商品SQL-OR条件]
                    sql_assign($room_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[仓储SQL-附加条件]
                }
            }
            $goods=Goods::where($goods_sql)->whereor($goods_whereor_sql)->select()->ToArray();//取出商品表信息[包含辅助属性条码]
            sql_assign($room_sql,'goods',arraychange($goods,'id'));//多表查询赋值处理
            //2.匹配串码表数据
            if(isset_full($input,'serial')){
                $serial=get_db_field('serial',['code'=>['like','%'.$input['serial'].'%'],'type'=>0],'room');//取出串码表条码信息
                sql_assign($room_sql,'id',$serial);//多表查询赋值处理
            }
            //3.查询仓储数据
            isset_full($input,'warehouse')&&($room_sql['warehouse']=$input['warehouse']);//匹配仓库数据
            isset_full($input,'batch')&&($room_sql['batch']=$input['batch']);//匹配批次数据
            $room_sql=auth('room',$room_sql);//数据鉴权
            $count = Room::where ($room_sql)->count();//获取总条数
            $arr = Room::with('warehouseinfo,goodsinfo,serialinfo')->where($room_sql)->page($input['page'],$input['limit'])->order('goods desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
            //二次处理数据
            foreach ($arr as $key=>$vo) {
                $arr[$key]['goodsinfo']['img']=empty($vo['goodsinfo']['imgs'])?'/skin/images/main/none.png':$vo['goodsinfo']['imgs'][0];//增加图像信息
                //根据辅助属性重新赋值价格信息
                if(!empty($vo['attr']['nod']) && $vo['attr']['name']!='辅助属性丢失'){
                    $attr=Attr::where(['pid'=>$vo['goods'],'nod'=>$vo['attr']['nod']])->find();
                    $arr[$key]['goodsinfo']['buy']=$attr['buy'];
                    $arr[$key]['goodsinfo']['sell']=$attr['sell'];
                    $arr[$key]['goodsinfo']['retail']=$attr['retail'];
                }
                //改造串码数据
                $serial=searchdata($vo['serialinfo'],['type|nod'=>['eq', isset_full($input,'serialtype')?$input['serialtype']:0]]);//获取指定类型串码数据
                $arr[$key]['serialinfo']=implode(",",arraychange($serial,'code'));
            }
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
    //零售单-获取仓储商品数据
    public function cashier_room_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page')&& isset_full($input,'limit') && isset($input['info'])){
            $room_sql=[];//初始化SQL
            //查询数据
            if(!empty($input['info'])){
                $goods_sql=['name|py|code'=>['like','%'.$input['info'].'%']];
                $goods_whereor_sql=[];
                //处理辅助属性条码
                $attr=Attr::where(['code'=>$input['info'],'enable'=>1])->select()->ToArray();//取出辅助属性表条码信息
                empty($attr)||(sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid')));//多表查询赋值处理
                //判断辅助属性条码是否存在
                if(empty($attr)){
                    //排除辅助属性查询
                    $goods=Goods::where($goods_sql)->select()->ToArray();//获取商品数据
                }else{
                    //匹配辅助属性查询
                    $goods=Goods::where(['id'=>['in',arraychange($attr,'pid')]])->select()->ToArray();//获取商品数据
                    sql_assign($room_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[仓储SQL-附加条件]
                }
                sql_assign($room_sql,'goods',arraychange($goods,'id'));
            }
            $room_sql=auth('room',$room_sql);//数据鉴权
            $count = Room::where ($room_sql)->count();//获取总条数
            $arr = Room::with('warehouseinfo,goodsinfo,serialinfo')->where($room_sql)->page($input['page'],$input['limit'])->order('goods desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
            //二次处理数据
            foreach ($arr as $key=>$vo) {
                $arr[$key]['goodsinfo']['img']=empty($vo['goodsinfo']['imgs'])?'/skin/images/main/none.png':$vo['goodsinfo']['imgs'][0];//增加图像信息
                //根据辅助属性重新赋值价格信息
                if(!empty($vo['attr']['nod']) && $vo['attr']['name']!='辅助属性丢失'){
                    $attr=Attr::where(['pid'=>$vo['goods'],'nod'=>$vo['attr']['nod']])->find();
                    $arr[$key]['goodsinfo']['buy']=$attr['buy'];
                    $arr[$key]['goodsinfo']['sell']=$attr['sell'];
                    $arr[$key]['goodsinfo']['retail']=$attr['retail'];
                }
                //改造串码数据
                $serial=searchdata($vo['serialinfo'],['type|nod'=>['eq',0]]);//获取指定类型串码数据
                $arr[$key]['serialinfo']=implode(",",arraychange($serial,'code'));
            }
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
    //服务列表
    public function serve_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'data'=>'full_like'
            ],'serve');//构造SQL
            $count = Serve::where ($sql)->count();//获取总条数
            $arr = Serve::where($sql)->page($input['page'],$input['limit'])->order('id desc')->select();//查询分页数据
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
    //库存查询
    public function room_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $room_sql=[];//初始化仓储SQL数据
            //1.匹配商品表数据
            $goods_sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'code'=>'full_eq',
                'spec'=>'full_like',
                'class'=>'full_goodsclass_tree_sub',
                'brand'=>'full_eq',
                'number'=>'full_like',
                'warehouse'=>'continue',
                'data'=>'full_like'
            ],'goods');//构造SQL
            $goods_whereor_sql=[];//或查询SQL
            //处理辅助属性条码
            if(isset_full($input,'code')){
                $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性条码信息
                if(!empty($attr)){
                    sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid'));//多表查询赋值处理[商品SQL-OR条件]
                    sql_assign($room_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[仓储SQL-附加条件]
                }
            }
            $goods=Goods::where($goods_sql)->whereor($goods_whereor_sql)->select()->ToArray();//取出商品表信息[包含辅助属性条码]
            sql_assign($room_sql,'goods',arraychange($goods,'id'));//多表查询赋值处理
            //2.查询仓储数据
            isset_full($input,'warehouse')&&($room_sql['warehouse']=$input['warehouse']);//匹配仓库数据
            isset_full($input,'batch')&&($room_sql['batch']=$input['batch']);//匹配批次数据
            $room_sql=auth('room',$room_sql);//数据鉴权
            $count = Room::where ($room_sql)->count();//获取总条数
            $arr = Room::with('warehouseinfo,goodsinfo')->where($room_sql)->page($input['page'],$input['limit'])->order('goods desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
            //二次处理数据
            foreach ($arr as $key=>$vo) {
                $arr[$key]['goodsinfo']['img']=empty($vo['goodsinfo']['imgs'])?'/skin/images/main/none.png':$vo['goodsinfo']['imgs'][0];//增加图像信息
                //根据辅助属性重新赋值价格信息
                if(!empty($vo['attr']['nod']) && $vo['attr']['name']!='辅助属性丢失'){
                    $attr=Attr::where(['pid'=>$vo['goods'],'nod'=>$vo['attr']['nod']])->find();
                    $arr[$key]['goodsinfo']['buy']=$attr['buy'];
                    $arr[$key]['goodsinfo']['sell']=$attr['sell'];
                    $arr[$key]['goodsinfo']['retail']=$attr['retail'];
                }
            }
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
    //导出库存信息
    public function export_room(){
        $input=input('get.');
        $room_sql=[];//初始化仓储SQL数据
        //1.匹配商品表数据
        $goods_sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'code'=>'full_eq',
            'spec'=>'full_like',
            'class'=>'full_goodsclass_tree_sub',
            'brand'=>'full_eq',
            'number'=>'full_like',
            'warehouse'=>'continue',
            'data'=>'full_like'
        ],'goods');//构造SQL
        $goods_whereor_sql=[];//或查询SQL
        //处理辅助属性条码
        if(isset_full($input,'code')){
            $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性条码信息
            if(!empty($attr)){
                sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid'));//多表查询赋值处理[商品SQL-OR条件]
                sql_assign($room_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[仓储SQL-附加条件]
            }
        }
        $goods=Goods::where($goods_sql)->whereor($goods_whereor_sql)->select()->ToArray();//取出商品表信息[包含辅助属性条码]
        sql_assign($room_sql,'goods',arraychange($goods,'id'));//多表查询赋值处理
        //2.查询仓储数据
        isset_full($input,'warehouse')&&($room_sql['warehouse']=$input['warehouse']);//匹配仓库数据
        isset_full($input,'batch')&&($room_sql['batch']=$input['batch']);//匹配批次数据
        $room_sql=auth('room',$room_sql);//数据鉴权
        $count = Room::where ($room_sql)->count();//获取总条数
        $arr = Room::with('warehouseinfo,goodsinfo')->where($room_sql)->order('goods desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
        //二次处理数据
        foreach ($arr as $key=>$vo) {
            //根据辅助属性重新赋值价格信息
            if(!empty($vo['attr']['nod']) && $vo['attr']['name']!='辅助属性丢失'){
                $attr=Attr::where(['pid'=>$vo['goods'],'nod'=>$vo['attr']['nod']])->find();
                $arr[$key]['goodsinfo']['buy']=$attr['buy'];
                $arr[$key]['goodsinfo']['sell']=$attr['sell'];
                $arr[$key]['goodsinfo']['retail']=$attr['retail'];
            }
        }
        $formfield=get_formfield('room_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'库存列表']);
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
        $sum_arr=get_sums($table_data,['nums']);
        array_push($excel,['type'=>'node','info'=>[
            '库存总数量:'.$sum_arr['nums'],
        ]]);//填充汇总信息
        //4.导出execl
        push_log('导出库存信息');//日志
        export_excel('库存列表',$excel);
    }
    //库存详情
    public function roominfo_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit') && isset_full($input,'pid')){
            //1.查询ROOMINFO数据
            $sql=get_sql($input,[
                'pid'=>'full_eq',
                'type'=>'full_eq',
                'start_time'=>'continue',
                'end_time'=>'continue'
            ],'roominfo');//构造SQL
            $data = Roominfo::with('typedata')->where($sql)->order('id desc')->select();//查询分页数据
            //2.多态查询
            $morphto_sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            isset_full($input,'number')&&($morphto_sql['number']=['like','%'.$input['number'].'%']);//单据编号
            isset_full($input,'user')&&($morphto_sql['user']=['in',explode(',',$input['user'])]);//制单人
            isset_full($input,'data')&&($morphto_sql['data']=['like','%'.$input['data'].'%']);//备注信息
            //按照时间搜索
            if(isset_full($input,'start_time') && !isset_full($input,'end_time')){
                //开始时间不为空,结束时间为空
                $morphto_sql['time']=['egt',strtotime($input['start_time'])];//大于等于
            }elseif(isset_full($input,'end_time') && !isset_full($input,'start_time')){
                //结束时间不为空,开始时间为空
                $morphto_sql['time']=['elt',strtotime($input['end_time'])+86399];//小于等于[结束加一天]
            }elseif(isset_full($input,'start_time') && isset_full($input,'end_time')){
                //开始时间不为空,结束时间不为空
                $morphto_sql['time']=[
                    ['egt',strtotime($input['start_time'])],//大于等于
                    ['elt',strtotime($input['end_time'])+86399]//小于等于[结束加一天]
                ];
            }
            $arr=[];//初始化新数据
            foreach ($data as $vo) {
                $morphto_sql['id']=$vo['typedata']['id'];
                $find=$vo['typedata']->where($morphto_sql)->find();
                if(!empty($find)){
                    $vo['typedata']['merchantinfo']=$vo->typedata->merchantinfo;//补全商户
                    $vo['typedata']['userinfo']=$vo->typedata->userinfo;//补全制单人
                    array_push($arr,$vo);
                }
            }
            //截取分页数据
            $count =count($arr);
            $page=$input['page'];
            $limit=$input['limit'];
            $arr = array_slice($arr,$limit*($page-1),$limit);
            $resule=[
                'state'=>'success',
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
    //导出库存详情
    public function export_roominfo(){
        $input=input('get.');
        if(isset_full($input,'pid')){
            //1.查询ROOMINFO数据
            $sql=get_sql($input,[
                'pid'=>'full_eq',
                'type'=>'full_eq',
                'start_time'=>'continue',
                'end_time'=>'continue'
            ],'roominfo');//构造SQL
            $data = Roominfo::with('typedata')->where($sql)->order('id desc')->select();//查询分页数据
            //2.多态查询
            $morphto_sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            isset_full($input,'number')&&($morphto_sql['number']=['like','%'.$input['number'].'%']);//单据编号
            isset_full($input,'user')&&($morphto_sql['user']=['in',explode(',',$input['user'])]);//制单人
            isset_full($input,'data')&&($morphto_sql['data']=['like','%'.$input['data'].'%']);//备注信息
            //按照时间搜索
            if(isset_full($input,'start_time') && !isset_full($input,'end_time')){
                //开始时间不为空,结束时间为空
                $morphto_sql['time']=['egt',strtotime($input['start_time'])];//大于等于
            }elseif(isset_full($input,'end_time') && !isset_full($input,'start_time')){
                //结束时间不为空,开始时间为空
                $morphto_sql['time']=['elt',strtotime($input['end_time'])+86399];//小于等于[结束加一天]
            }elseif(isset_full($input,'start_time') && isset_full($input,'end_time')){
                //开始时间不为空,结束时间不为空
                $morphto_sql['time']=[
                    ['egt',strtotime($input['start_time'])],//大于等于
                    ['elt',strtotime($input['end_time'])+86399]//小于等于[结束加一天]
                ];
            }
            $arr=[];//初始化新数据
            foreach ($data as $vo) {
                $morphto_sql['id']=$vo['typedata']['id'];
                $find=$vo['typedata']->where($morphto_sql)->find();
                if(!empty($find)){
                    $vo['typedata']['merchantinfo']=$vo->typedata->merchantinfo;//补全商户
                    $vo['typedata']['userinfo']=$vo->typedata->userinfo;//补全制单人
                    array_push($arr,$vo);
                }
            }
            $room=Room::where(['id'=>$input['pid']])->find();
            $formfield=get_formfield('roominfo_export','array');//获取字段配置
            //开始构造导出数据
            $excel=[];//初始化导出数据
            //1.填充标题数据
            array_push($excel,['type'=>'title','info'=>'商品['.$room['goodsinfo']['name'].']库存详情']);
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
            push_log('导出库存信息');//日志
            export_excel('库存列表',$excel);
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
    //库存预警
    public function roomwarning_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $room_sql=[];//初始化仓储SQL数据
            //1.匹配商品表数据
            $goods_sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'code'=>'full_eq',
                'spec'=>'full_like',
                'class'=>'full_goodsclass_tree_sub',
                'brand'=>'full_eq',
                'number'=>'full_like',
                'warehouse'=>'continue',
                'data'=>'full_like'
            ],'goods');//构造SQL
            $goods_whereor_sql=[];//或查询SQL
            //处理辅助属性条码
            if(isset_full($input,'code')){
                $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性条码信息
                if(!empty($attr)){
                    sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid'));//多表查询赋值处理[商品SQL-OR条件]
                    sql_assign($room_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[仓储SQL-附加条件]
                }
            }
            $goods=Goods::where($goods_sql)->whereor($goods_whereor_sql)->select()->ToArray();//取出商品表信息[包含辅助属性条码]
            sql_assign($room_sql,'goods',arraychange($goods,'id'));//多表查询赋值处理
            //2.查询仓储数据
            isset_full($input,'warehouse')&&($room_sql['warehouse']=$input['warehouse']);//匹配仓库数据
            isset_full($input,'batch')&&($room_sql['batch']=$input['batch']);//匹配批次数据
            $room_sql=auth('room',$room_sql);//数据鉴权
            $arr = Room::with('warehouseinfo,goodsinfo')->where($room_sql)->order('goods desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
            //二次处理数据
            foreach ($arr as $key=>$vo) {
                //判断库存数是否达到预警数值
                if(bccomp($vo['nums'],$vo['goodsinfo']['stocktip'],config('decimal'))==-1){
                    //现库存小于预警值
                    $arr[$key]['goodsinfo']['img']=empty($vo['goodsinfo']['imgs'])?'/skin/images/main/none.png':$vo['goodsinfo']['imgs'][0];//增加图像信息
                    //根据辅助属性重新赋值价格信息
                    if(!empty($vo['attr']['nod']) && $vo['attr']['name']!='辅助属性丢失'){
                        $attr=Attr::where(['pid'=>$vo['goods'],'nod'=>$vo['attr']['nod']])->find();
                        $arr[$key]['goodsinfo']['buy']=$attr['buy'];
                        $arr[$key]['goodsinfo']['sell']=$attr['sell'];
                        $arr[$key]['goodsinfo']['retail']=$attr['retail'];
                    }
                }else{
                    unset($arr[$key]);//删除数据
                }
            }
            //截取分页数据
            $count =count($arr);
            $page=$input['page'];
            $limit=$input['limit'];
            $arr = array_slice($arr,$limit*($page-1),$limit);
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
    //导出库存预警
    public function export_roomwarning(){
        $input=input('get.');
        $room_sql=[];//初始化仓储SQL数据
        //1.匹配商品表数据
        $goods_sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'code'=>'full_eq',
            'spec'=>'full_like',
            'class'=>'full_goodsclass_tree_sub',
            'brand'=>'full_eq',
            'number'=>'full_like',
            'warehouse'=>'continue',
            'data'=>'full_like'
        ],'goods');//构造SQL
        $goods_whereor_sql=[];//或查询SQL
        //处理辅助属性条码
        if(isset_full($input,'code')){
            $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性条码信息
            if(!empty($attr)){
                sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid'));//多表查询赋值处理[商品SQL-OR条件]
                sql_assign($room_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[仓储SQL-附加条件]
            }
        }
        $goods=Goods::where($goods_sql)->whereor($goods_whereor_sql)->select()->ToArray();//取出商品表信息[包含辅助属性条码]
        sql_assign($room_sql,'goods',arraychange($goods,'id'));//多表查询赋值处理
        //2.查询仓储数据
        isset_full($input,'warehouse')&&($room_sql['warehouse']=$input['warehouse']);//匹配仓库数据
        isset_full($input,'batch')&&($room_sql['batch']=$input['batch']);//匹配批次数据
        $room_sql=auth('room',$room_sql);//数据鉴权
        $count = Room::where ($room_sql)->count();//获取总条数
        $arr = Room::with('warehouseinfo,goodsinfo')->where($room_sql)->order('goods desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
        //二次处理数据
        foreach ($arr as $key=>$vo) {
            //判断库存数是否达到预警数值
            if(bccomp($vo['nums'],$vo['goodsinfo']['stocktip'],config('decimal'))==-1){
                //现库存小于预警值
                $arr[$key]['goodsinfo']['img']=empty($vo['goodsinfo']['imgs'])?'/skin/images/main/none.png':$vo['goodsinfo']['imgs'][0];//增加图像信息
                //根据辅助属性重新赋值价格信息
                if(!empty($vo['attr']['nod']) && $vo['attr']['name']!='辅助属性丢失'){
                    $attr=Attr::where(['pid'=>$vo['goods'],'nod'=>$vo['attr']['nod']])->find();
                    $arr[$key]['goodsinfo']['buy']=$attr['buy'];
                    $arr[$key]['goodsinfo']['sell']=$attr['sell'];
                    $arr[$key]['goodsinfo']['retail']=$attr['retail'];
                }
            }else{
                unset($arr[$key]);//删除数据
            }
        }
        $formfield=get_formfield('roomwarning_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'库存预警列表']);
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
        push_log('导出库存预警信息');//日志
        export_excel('库存预警列表',$excel);
    }
    //库存盘点查询
    public function roomcheck_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'rows')){
            $room_sql=[];//初始化仓储SQL数据
            //1.匹配商品表数据
            $goods_sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'code'=>'full_eq',
                'spec'=>'full_like',
                'class'=>'full_goodsclass_tree_sub',
                'brand'=>'full_eq',
                'number'=>'full_like',
                'warehouse'=>'continue',
                'data'=>'full_like'
            ],'goods');//构造SQL
            $goods_whereor_sql=[];//或查询SQL
            //处理辅助属性条码
            if(isset_full($input,'code')){
                $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性条码信息
                if(!empty($attr)){
                    sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid'));//多表查询赋值处理[商品SQL-OR条件]
                    sql_assign($room_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[仓储SQL-附加条件]
                }
            }
            $goods=Goods::where($goods_sql)->whereor($goods_whereor_sql)->select()->ToArray();//取出商品表信息[包含辅助属性条码]
            sql_assign($room_sql,'goods',arraychange($goods,'id'));//多表查询赋值处理
            //2.查询仓储数据
            isset_full($input,'warehouse')&&($room_sql['warehouse']=$input['warehouse']);//匹配仓库数据
            isset_full($input,'batch')&&($room_sql['batch']=$input['batch']);//匹配批次数据
            $room_sql=auth('room',$room_sql);//数据鉴权
            $count = Room::where ($room_sql)->count();//获取总条数
            $arr = Room::with('warehouseinfo,goodsinfo')->where($room_sql)->page($input['page'],$input['rows'])->order('goods desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
            $resule=[
                'total'=>ceil($count/$input['rows']),//总页数=总条数/每页个数
                'page'=>$input['page'],
                'records'=>$count,
                'rows'=>$arr
            ];//返回数据
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
    }
    //导出库存盘点
    public function export_roomcheck(){
        $input=input('get.');
        $room_sql=[];//初始化仓储SQL数据
        //1.匹配商品表数据
        $goods_sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'code'=>'full_eq',
            'spec'=>'full_like',
            'class'=>'full_goodsclass_tree_sub',
            'brand'=>'full_eq',
            'number'=>'full_like',
            'warehouse'=>'continue',
            'data'=>'full_like'
        ],'goods');//构造SQL
        $goods_whereor_sql=[];//或查询SQL
        //处理辅助属性条码
        if(isset_full($input,'code')){
            $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性条码信息
            if(!empty($attr)){
                sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid'));//多表查询赋值处理[商品SQL-OR条件]
                sql_assign($room_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[仓储SQL-附加条件]
            }
        }
        $goods=Goods::where($goods_sql)->whereor($goods_whereor_sql)->select()->ToArray();//取出商品表信息[包含辅助属性条码]
        sql_assign($room_sql,'goods',arraychange($goods,'id'));//多表查询赋值处理
        //2.查询仓储数据
        isset_full($input,'warehouse')&&($room_sql['warehouse']=$input['warehouse']);//匹配仓库数据
        isset_full($input,'batch')&&($room_sql['batch']=$input['batch']);//匹配批次数据
        $room_sql=auth('room',$room_sql);//数据鉴权
        $count = Room::where ($room_sql)->count();//获取总条数
        $arr = Room::with('warehouseinfo,goodsinfo')->where($room_sql)->order('goods desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
        //二次处理数据
        foreach ($arr as $key=>$vo) {
            $arr[$key]['stock']='';
        }
        $formfield=get_formfield('roomcheck_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'库存盘点列表']);
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
        push_log('导出库存盘点信息');//日志
        export_excel('库存盘点列表',$excel);
    }
    //统计初始化获取单据数量
	public function  summary_forms(){
	    Summary::where(['id'=>['gt',0]])->delete();//清空数据表
	    $form=[
            'purchase'=>'购货单',
            'rpurchase'=>'采购入库单',
            'repurchase'=>'购货退货单',
            'sale'=>'销货单',
            'resale'=>'销货退货单',
            'cashier'=>'零售单',
            'recashier'=>'零售退货单',
            'exchange'=>'积分兑换单',
            'allocation'=>'调拨单',
            'otpurchase'=>'其他入库单',
            'otsale'=>'其他出库单'
        ];
        $resule=[];
        foreach ($form as $key => $vo) {
            array_push($resule,[$vo,$key,db($key.'class')->where(['type'=>1])->count()]);
        }
        return json($resule);
	}
	//初始化报表数据
	public function cal_summary(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'name') && isset_full($input,'form') && isset_full($input,'classcur') && isset_full($input,'infocur')){
            $base=30;//每次更新个数
            $class=db($input['form'].'class')->where(['type'=>1])->field('id')->page($input['infocur'],$base)->select();
            foreach ($class as $vo) {
                set_summary($input['form'],$vo['id'],true);
            }
            $resule=$input;//转存数据2
            $resule['start']=($input['infocur']-1)*$base+1;
	        $resule['end']=$resule['start']+count($class)-1;
        }else{
            $resule=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($resule);
	}
	//商品利润表
    public function goodsprofit_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            $summary_sql=[];//初始化统计数据SQL数据
            //1.匹配商品表数据
            $goods_sql=get_sql($input,[
                'name'=>'full_name_py_link',
                'start_time'=>'continue',
                'end_time'=>'continue',
                'code'=>'full_eq',
                'spec'=>'full_like',
                'class'=>'full_goodsclass_tree_sub',
                'brand'=>'full_eq',
                'number'=>'full_like',
                'warehouse'=>'continue',
                'data'=>'full_like'
            ],'goods');//构造SQL
            $goods_whereor_sql=[];//或查询SQL
            //处理辅助属性条码
            if(isset_full($input,'code')){
                $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性条码信息
                if(!empty($attr)){
                    sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid'));//多表查询赋值处理[商品SQL-OR条件]
                    sql_assign($summary_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[统计数据SQL-附加条件]
                }
            }
            $goods=Goods::where($goods_sql)->whereor($goods_whereor_sql)->select()->ToArray();//取出商品表信息[包含辅助属性条码]
            sql_assign($summary_sql,'goods',arraychange($goods,'id'));//多表查询赋值处理
            //2.查询统计数据
            $summary_sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
            isset_full($input,'warehouse')&&($summary_sql['warehouse']=$input['warehouse']);//匹配仓库数据
            isset_full($input,'batch')&&($summary_sql['batch']=$input['batch']);//匹配批次数据
            //按照时间搜索
            if(isset_full($input,'start_time') && !isset_full($input,'end_time')){
                //开始时间不为空,结束时间为空
                $summary_sql['time']=['egt',strtotime($input['start_time'])];//大于等于
            }elseif(isset_full($input,'end_time') && !isset_full($input,'start_time')){
                //结束时间不为空,开始时间为空
                $summary_sql['time']=['elt',strtotime($input['end_time'])+86399];//小于等于[结束加一天]
            }elseif(isset_full($input,'start_time') && isset_full($input,'end_time')){
                //开始时间不为空,结束时间不为空
                $summary_sql['time']=[
                    ['egt',strtotime($input['start_time'])],//大于等于
                    ['elt',strtotime($input['end_time'])+86399]//小于等于[结束加一天]
                ];
            }
            $summary_sql=auth('summary',$summary_sql);//数据鉴权
            $count = Summary::where ($summary_sql)->group('room')->count();//获取总条数[去除重复数据]
            $arr = Summary::with('warehouseinfo,goodsinfo,roominfo')->where($summary_sql)->page($input['page'],$input['limit'])->order('goods desc')->group('room')->select()->ToArray();//查询分页数据[去除重复数据|转数组方便二维数组赋值]
            //二次处理数据
            foreach ($arr as $key=>$vo) {
                $arr[$key]['goodsinfo']['img']=empty($vo['goodsinfo']['imgs'])?'/skin/images/main/none.png':$vo['goodsinfo']['imgs'][0];//增加图像信息
                //根据辅助属性重新赋值价格信息
                if(!empty($vo['attr']['nod']) && $vo['attr']['name']!='辅助属性丢失'){
                    $attr=Attr::where(['pid'=>$vo['goods'],'nod'=>$vo['attr']['nod']])->find();
                    $arr[$key]['goodsinfo']['buy']=$attr['buy'];
                    $arr[$key]['goodsinfo']['sell']=$attr['sell'];
                    $arr[$key]['goodsinfo']['retail']=$attr['retail'];
                }
                //统计数据
                $arr[$key]['sale']=db('summary')->where($summary_sql)->where(['type'=>4,'room'=>$vo['room']])->sum('total');//获取销货总金额
                $arr[$key]['cashier']=db('summary')->where($summary_sql)->where(['type'=>6,'room'=>$vo['room']])->sum('total');//获取零售总金额
                $arr[$key]['sales_revenue']=opt_decimal(bcadd($arr[$key]['sale'],$arr[$key]['cashier'],config('decimal')));//计算销售收入[高精度]
                $avg=db('summary')->where(['type'=>['in',[1,2],'OR'],'room'=>$vo['room'],'price'=>['gt',0]])->avg('price');//购货单|采购入库单|不为零的平均价
                $allnums=db('summary')->where($summary_sql)->where(['type'=>['in',[4,6],'OR'],'room'=>$vo['room']])->sum('nums');//取出购货单和采购入库单的总数
                $arr[$key]['sales_cost']=opt_decimal(bcmul($avg,$allnums,config('decimal')));//计算销售成本[高精度]
                $arr[$key]['sales_maori']=opt_decimal(bcsub($arr[$key]['sales_revenue'],$arr[$key]['sales_cost'],config('decimal')));//计算销售毛利[高精度]
                $arr[$key]['gross_interest_rate']=@opt_decimal(bcmul(bcdiv($arr[$key]['sales_maori'],$arr[$key]['sales_revenue'],2),100,config('decimal'))).'%';//计算销售毛利[高精度]
            }
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
    //导出商品利润表
    public function export_goodsprofit(){
        $input=input('get.');
        $summary_sql=[];//初始化统计数据SQL数据
        //1.匹配商品表数据
        $goods_sql=get_sql($input,[
            'name'=>'full_name_py_link',
            'start_time'=>'continue',
            'end_time'=>'continue',
            'code'=>'full_eq',
            'spec'=>'full_like',
            'class'=>'full_goodsclass_tree_sub',
            'brand'=>'full_eq',
            'number'=>'full_like',
            'warehouse'=>'continue',
            'data'=>'full_like'
        ],'goods');//构造SQL
        $goods_whereor_sql=[];//或查询SQL
        //处理辅助属性条码
        if(isset_full($input,'code')){
            $attr=Attr::where(['code'=>$input['code'],'enable'=>1])->select()->ToArray();//取出辅助属性条码信息
            if(!empty($attr)){
                sql_assign($goods_whereor_sql,'id',arraychange($attr,'pid'));//多表查询赋值处理[商品SQL-OR条件]
                sql_assign($summary_sql,'attr',arraychange($attr,'nod'));//多表查询赋值处理[统计数据SQL-附加条件]
            }
        }
        $goods=Goods::where($goods_sql)->whereor($goods_whereor_sql)->select()->ToArray();//取出商品表信息[包含辅助属性条码]
        sql_assign($summary_sql,'goods',arraychange($goods,'id'));//多表查询赋值处理
        //2.查询统计数据
        $summary_sql['merchant']=['in',get_auth_merchant()];//补全授权商户数据
        isset_full($input,'warehouse')&&($summary_sql['warehouse']=$input['warehouse']);//匹配仓库数据
        isset_full($input,'batch')&&($summary_sql['batch']=$input['batch']);//匹配批次数据
        //按照时间搜索
        if(isset_full($input,'start_time') && !isset_full($input,'end_time')){
            //开始时间不为空,结束时间为空
            $summary_sql['time']=['egt',strtotime($input['start_time'])];//大于等于
        }elseif(isset_full($input,'end_time') && !isset_full($input,'start_time')){
            //结束时间不为空,开始时间为空
            $summary_sql['time']=['elt',strtotime($input['end_time'])+86399];//小于等于[结束加一天]
        }elseif(isset_full($input,'start_time') && isset_full($input,'end_time')){
            //开始时间不为空,结束时间不为空
            $summary_sql['time']=[
                ['egt',strtotime($input['start_time'])],//大于等于
                ['elt',strtotime($input['end_time'])+86399]//小于等于[结束加一天]
            ];
        }
        $summary_sql=auth('summary',$summary_sql);//数据鉴权
        $arr = Summary::with('warehouseinfo,goodsinfo,roominfo')->where($summary_sql)->order('goods desc')->group('room')->select()->ToArray();//查询分页数据[去除重复数据|转数组方便二维数组赋值]
        //二次处理数据
        foreach ($arr as $key=>$vo) {
            $arr[$key]['goodsinfo']['img']=empty($vo['goodsinfo']['imgs'])?'/skin/images/main/none.png':$vo['goodsinfo']['imgs'][0];//增加图像信息
            //根据辅助属性重新赋值价格信息
            if(!empty($vo['attr']['nod']) && $vo['attr']['name']!='辅助属性丢失'){
                $attr=Attr::where(['pid'=>$vo['goods'],'nod'=>$vo['attr']['nod']])->find();
                $arr[$key]['goodsinfo']['buy']=$attr['buy'];
                $arr[$key]['goodsinfo']['sell']=$attr['sell'];
                $arr[$key]['goodsinfo']['retail']=$attr['retail'];
            }
            //统计数据
            $arr[$key]['sale']=db('summary')->where($summary_sql)->where(['type'=>4,'room'=>$vo['room']])->sum('total');//获取销货总金额
            $arr[$key]['cashier']=db('summary')->where($summary_sql)->where(['type'=>6,'room'=>$vo['room']])->sum('total');//获取零售总金额
            $arr[$key]['sales_revenue']=opt_decimal(bcadd($arr[$key]['sale'],$arr[$key]['cashier'],config('decimal')));//计算销售收入[高精度]
            $avg=db('summary')->where(['type'=>['in',[1,2],'OR'],'room'=>$vo['room'],'price'=>['gt',0]])->avg('price');//购货单|采购入库单|不为零的平均价
            $allnums=db('summary')->where($summary_sql)->where(['type'=>['in',[4,6],'OR'],'room'=>$vo['room']])->sum('nums');//取出购货单和采购入库单的总数
            $arr[$key]['sales_cost']=opt_decimal(bcmul($avg,$allnums,config('decimal')));//计算销售成本[高精度]
            $arr[$key]['sales_maori']=opt_decimal(bcsub($arr[$key]['sales_revenue'],$arr[$key]['sales_cost'],config('decimal')));//计算销售毛利[高精度]
            $arr[$key]['gross_interest_rate']=@opt_decimal(bcmul(bcdiv($arr[$key]['sales_maori'],$arr[$key]['sales_revenue'],2),100,config('decimal'))).'%';//计算销售毛利[高精度]
        }
        $formfield=get_formfield('goodsprofit_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'商品利润表']);
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
        $sum_arr=get_sums($table_data,['sale','cashier','sales_revenue','sales_cost','sales_maori']);
        array_push($excel,['type'=>'node','info'=>[
            '总销货金额:'.$sum_arr['sale'],
            '总零售金额:'.$sum_arr['cashier'],
            '总销售收入:'.$sum_arr['sales_revenue'],
            '总销售成本:'.$sum_arr['sales_cost'],
            '总销售毛利:'.$sum_arr['sales_maori'],
        ]]);//填充汇总信息
        //4.导出execl
        push_log('导出商品利润表');//日志
        export_excel('商品利润表',$excel);
    }
    //销售利润表
    public function billprofit_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            //1.构造统计表SQL
            $summary_sql=get_sql($input,[
                'type'=>'continue',
                'customer'=>'full_division_in',
                'user'=>'full_division_in',
                'start_time'=>'stime',
                'end_time'=>'etime',
                'number'=>'full_like',
            ],'summary');//构造SQL
            //2.补充单据类型
            $summary_sql['type']=['in',[4,6]];//初始化单据类型
            if(isset_full($input,'type')){
                if($input['type']==1){
                    $summary_sql['type']=4;//销货单
                }elseif($input['type']==2){
                    $summary_sql['type']=6;//零售单
                }
            }
            //3.查询数据
            $summary_sql=auth('summary',$summary_sql);//数据鉴权
            $count = Summary::where ($summary_sql)->group('number')->count();//获取总条数[去除重复数据]
            $arr = Summary::with('merchantinfo,typedata,customerinfo,userinfo')->where($summary_sql)->page($input['page'],$input['limit'])->order('id desc')->group('number')->select()->ToArray();//查询分页数据[去除重复数据|转数组方便二维数组赋值]
            //4.重新构造数据
            foreach ($arr as $key=>$vo) {
                $info=db('summary')->where(['number'=>$vo['number']])->select()->ToArray();//查询该订单下所有的INFO数据
                $sum_arr=get_sums($info,['nums','total']);//统计数据
                $arr[$key]['allnums']=$sum_arr['nums'];//总数量
                $arr[$key]['sales_revenue']=opt_decimal($sum_arr['total']);//销售收入
                $arr[$key]['selling_cost']=0;//初始化销售成本
                foreach ($info as $info_vo) {
                    $avg=db('summary')->where(['type'=>['in',[1,2]],'room'=>$info_vo['room'],'price'=>['gt',0]])->avg('price');//购货单|采购入库单|不为零的平均价
                    $arr[$key]['selling_cost']=$arr[$key]['selling_cost']+opt_decimal(bcmul($avg,$info_vo['nums'],config('decimal')));//累加销售成本[高精度]
                }
                $arr[$key]['gross_margin']=opt_decimal(bcsub($arr[$key]['sales_revenue'],$arr[$key]['selling_cost'],config('decimal')));//销售毛利=(销售收入-销售成本)[高精度]
                $arr[$key]['gross_profit_margin']=@opt_decimal(bcmul(bcdiv($arr[$key]['gross_margin'],$arr[$key]['sales_revenue'],2),100,config('decimal'))).'%';//毛利率=(销售毛利/销售收入)*100[高精度]
                $arr[$key]['discount']=opt_decimal(bcsub($vo['typedata']['total'],$vo['typedata']['actual'],config('decimal')));//优惠金额=单据金额-实际金额[高精度]
                $arr[$key]['net_profit']=opt_decimal(bcsub($arr[$key]['gross_margin'],$arr[$key]['discount'],config('decimal')));//销售净利润=(销售毛利-优惠金额)[高精度]
                $arr[$key]['net_profit_margin']=@opt_decimal(bcmul(bcdiv($arr[$key]['net_profit'],$arr[$key]['sales_revenue'],2),100,config('decimal'))).'%';//净利润率=(销售净利润/销售收入)*100[高精度]
                $arr[$key]['receivable']=opt_decimal($vo['typedata']['actual']);//实际金额
                $arr[$key]['money']=opt_decimal($vo['typedata']['money']);//实收金额
            }
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
    //导出销售利润表
    public function export_billprofit(){
        $input=input('get.');
        //1.构造统计表SQL
        $summary_sql=get_sql($input,[
            'type'=>'continue',
            'customer'=>'full_division_in',
            'user'=>'full_division_in',
            'start_time'=>'stime',
            'end_time'=>'etime',
            'number'=>'full_like',
        ],'summary');//构造SQL
        //2.补充单据类型
        $summary_sql['type']=['in',[4,6]];//初始化单据类型
        if(isset_full($input,'type')){
            if($input['type']==1){
                $summary_sql['type']=4;//销货单
            }elseif($input['type']==2){
                $summary_sql['type']=6;//零售单
            }
        }
        //3.查询数据
        $summary_sql=auth('summary',$summary_sql);//数据鉴权
        $arr = Summary::with('merchantinfo,typedata,customerinfo,userinfo')->where($summary_sql)->order('id desc')->group('number')->select()->ToArray();//查询分页数据[去除重复数据|转数组方便二维数组赋值]
        //4.重新构造数据
        foreach ($arr as $key=>$vo) {
            $info=db('summary')->where(['number'=>$vo['number']])->select()->ToArray();//查询该订单下所有的INFO数据
            $sum_arr=get_sums($info,['nums','total']);//统计数据
            $arr[$key]['allnums']=$sum_arr['nums'];//总数量
            $arr[$key]['sales_revenue']=opt_decimal($sum_arr['total']);//销售收入
            $arr[$key]['selling_cost']=0;//初始化销售成本
            foreach ($info as $info_vo) {
                $avg=db('summary')->where(['type'=>['in',[1,2]],'room'=>$info_vo['room'],'price'=>['gt',0]])->avg('price');//购货单|采购入库单|不为零的平均价
                $arr[$key]['selling_cost']=$arr[$key]['selling_cost']+opt_decimal(bcmul($avg,$info_vo['nums'],config('decimal')));//累加销售成本[高精度]
            }
            $arr[$key]['gross_margin']=opt_decimal(bcsub($arr[$key]['sales_revenue'],$arr[$key]['selling_cost'],config('decimal')));//销售毛利=(销售收入-销售成本)[高精度]
            $arr[$key]['gross_profit_margin']=@opt_decimal(bcmul(bcdiv($arr[$key]['gross_margin'],$arr[$key]['sales_revenue'],2),100,config('decimal'))).'%';//毛利率=(销售毛利/销售收入)*100[高精度]
            $arr[$key]['discount']=opt_decimal(bcsub($vo['typedata']['total'],$vo['typedata']['actual'],config('decimal')));//优惠金额=单据金额-实际金额[高精度]
            $arr[$key]['net_profit']=opt_decimal(bcsub($arr[$key]['gross_margin'],$arr[$key]['discount'],config('decimal')));//销售净利润=(销售毛利-优惠金额)[高精度]
            $arr[$key]['net_profit_margin']=@opt_decimal(bcmul(bcdiv($arr[$key]['net_profit'],$arr[$key]['sales_revenue'],2),100,config('decimal'))).'%';//净利润率=(销售净利润/销售收入)*100[高精度]
            $arr[$key]['receivable']=opt_decimal($vo['typedata']['actual']);//实际金额
            $arr[$key]['money']=opt_decimal($vo['typedata']['money']);//实收金额
        }
        $formfield=get_formfield('billprofit_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'销售利润表']);
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
        $sum_arr=get_sums($table_data,['sales_revenue','selling_cost','gross_margin','discount','net_profit','receivable','money']);
        array_push($excel,['type'=>'node','info'=>[
            '总销售收入:'.$sum_arr['sales_revenue'],
            '总销售成本:'.$sum_arr['selling_cost'],
            '总销售毛利:'.$sum_arr['gross_margin'],
            '总优惠金额:'.$sum_arr['discount'],
            '总销售净利润:'.$sum_arr['net_profit'],
            '总应收金额:'.$sum_arr['receivable'],
            '总实收金额:'.$sum_arr['money'],
        ]]);//填充汇总信息
        //4.导出execl
        push_log('导出销售利润表');//日志
        export_excel('销售利润表',$excel);
    }
    //串码跟踪表
    public function serial_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit')){
            //1.构造串码表SQL
            $serial_sql=get_sql($input,[
                'code'=>'full_like',
                'type'=>'full_dec_1',
            ],'serial');//构造SQL
            //2.补充商品搜索
            if(isset_full($input,'name')){
                $goods=get_db_field('goods',['name|py'=>['like','%'.$input['name'].'%']]);//取出商品表信息
                sql_assign($serial_sql,'goods',$goods);//多表查询赋值处理
            }
            //3.查询数据
			$serial_sql['type']=['neq',2];//补充串码状态
            $count = Serial::where($serial_sql)->count();//获取总条数
            $arr = Serial::with('goodsinfo,roominfo')->where($serial_sql)->page($input['page'],$input['limit'])->order('goods desc')->select();//查询分页数据
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
    //导出串码跟踪表
    public function export_serial(){
        $input=input('get.');
        //1.构造串码表SQL
        $serial_sql=get_sql($input,[
            'code'=>'full_like',
            'type'=>'full_dec_1',
        ],'serial');//构造SQL
        //2.补充商品搜索
        if(isset_full($input,'name')){
            $goods=get_db_field('goods',['name|py'=>['like','%'.$input['name'].'%']]);//取出商品表信息
            sql_assign($serial_sql,'goods',$goods);//多表查询赋值处理
        }
        //3.查询数据
		$serial_sql['type']=['neq',2];//补充串码状态
        $arr = Serial::with('goodsinfo,roominfo')->where($serial_sql)->order('goods desc')->select();//查询分页数据
        $formfield=get_formfield('serial_export','array');//获取字段配置
        //开始构造导出数据
        $excel=[];//初始化导出数据
        //1.填充标题数据
        array_push($excel,['type'=>'title','info'=>'串码跟踪表']);
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
        push_log('导出串码跟踪表');//日志
        export_excel('串码跟踪表',$excel);
    }
    //串码跟踪详情表
    public function serialinfo_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit') && isset_full($input,'pid')){
            //1.构造串码详情表SQL
            $serialinfo_sql=get_sql($input,[
                'pid'=>'full_eq',
                'type'=>'full_eq',
            ],'serialinfo');//构造SQL
            //3.查询数据
            $count = Serialinfo::where($serialinfo_sql)->count();//获取总条数
            $arr = Serialinfo::with('typedata')->where($serialinfo_sql)->page($input['page'],$input['limit'])->order('id desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
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
    //导出串码跟踪详情表
    public function export_serialinfo(){
        $input=input('get.');
        if(isset_full($input,'pid')){
            //1.构造串码表SQL
            $serialinfo_sql=get_sql($input,[
                'pid'=>'full_eq',
                'type'=>'full_eq',
            ],'serialinfo');//构造SQL
            //2.查询数据
            $arr = Serialinfo::with('typedata')->where($serialinfo_sql)->order('id desc')->select()->ToArray();//查询分页数据[转数组方便二维数组赋值]
            $formfield=get_formfield('serialinfo_export','array');//获取字段配置
            //开始构造导出数据
            $serial=db('serial')->where(['id'=>$input['pid']])->find();
            $excel=[];//初始化导出数据
            //1.填充标题数据
            array_push($excel,['type'=>'title','info'=>'串码['.$serial['code'].']跟踪详情表']);
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
            push_log('导出串码跟踪详情表');//日志
            export_excel('串码跟踪详情表',$excel);
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
    //往来单位欠款表
    public function arrears_list(){
        $input=input('post.');
        //数据完整性判断
        if(isset_full($input,'page') && isset_full($input,'limit') && isset_full($input,'type') && in_array($input['type'],[1,2])){
            $arr=[];//初始化返回数据
            //判断类型
            if($input['type']=='1'){
                //客户
                $sql=get_sql($input,[
                    'name'=>'full_name_py_link',
                    'number'=>'full_like',
                ],'customer');//构造SQL
                $sql=auth('customer',$sql);//数据鉴权
                $customer=Customer::where($sql)->select();
                foreach ($customer as $vo) {
                    $money=0;
                    $sale_sql['billtype']=['in',[0,1],'OR'];
                    $sale_sql['customer']=$vo['id'];
                    $sale_sql=auth('saleclass',$sale_sql);//数据鉴权
                    $sale=Saleclass::where($sale_sql)->select();
                    //统计欠款金额
                    foreach ($sale as $sale_vo) {
                        $difference=bcsub($sale_vo['actual'],$sale_vo['money'],config('decimal'));//计算欠款金额
                        $money=opt_decimal(bcadd($money,$difference,config('decimal')));//累加欠款金额[高精度]
                    }
                    //转存数据
                    $info=[];
                    $info['type']='客户';
                    $info['name']=$vo['name'];
                    $info['number']=$vo['number'];
                    $info['money']=$money;
                    array_push($arr,$info);
                }
            }else{
                //供应商
                $sql=get_sql($input,[
                    'name'=>'full_name_py_link',
                    'number'=>'full_like',
                ],'supplier');//构造SQL
                $sql=auth('supplier',$sql);//数据鉴权
                $supplier=Supplier::where($sql)->select();
                foreach ($supplier as $vo) {
                    $money=0;
                    //1.购货单
                    $purchase_sql['billtype']=['in',[0,1],'OR'];
                    $purchase_sql['supplier']=$vo['id'];
                    $purchase_sql=auth('purchaseclass',$purchase_sql);//数据鉴权
                    $purchase=Purchaseclass::where($purchase_sql)->select();
                    //统计欠款金额
                    foreach ($purchase as $purchase_vo) {
                        $difference=bcsub($purchase_vo['actual'],$purchase_vo['money'],config('decimal'));//计算欠款金额
                        $money=opt_decimal(bcadd($money,$difference,config('decimal')));//累加欠款金额[高精度]
                    }
                    //2.采购入库单
                    $rpurchase_sql['billtype']=['in',[0,1],'OR'];
                    $rpurchase_sql['supplier']=$vo['id'];
                    $rpurchase_sql=auth('rpurchaseclass',$rpurchase_sql);//数据鉴权
                    $rpurchase=Rpurchaseclass::where($rpurchase_sql)->select();
                    //统计欠款金额
                    foreach ($rpurchase as $rpurchase_vo){
                        $difference=bcsub($rpurchase_vo['actual'],$rpurchase_vo['money'],config('decimal'));//计算欠款金额
                        $money=opt_decimal(bcadd($money,$difference,config('decimal')));//累加欠款金额[高精度]
                    }
                    //转存数据
                    $info=[];
                    $info['type']='供应商';
                    $info['name']=$vo['name'];
                    $info['number']=$vo['number'];
                    $info['money']=$money;
                    array_push($arr,$info);
                }
            }
            //截取分页数据
            $count =count($arr);
            $page=$input['page'];
            $limit=$input['limit'];
            $arr = array_slice($arr,$limit*($page-1),$limit);
            $resule=[
                'state'=>'success',
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
    //导出往来单位欠款表
    public function export_arrears(){
        $input=input('get.');
        if(isset_full($input,'type') && in_array($input['type'],[1,2])){
            $arr=[];//初始化数据
            //判断类型
            if($input['type']=='1'){
                //客户
                $sql=get_sql($input,[
                    'name'=>'full_name_py_link',
                    'number'=>'full_like',
                ],'customer');//构造SQL
                $sql=auth('customer',$sql);//数据鉴权
                $customer=Customer::where($sql)->select();
                foreach ($customer as $vo) {
                    $money=0;
                    $sale_sql['billtype']=['in',[0,1],'OR'];
                    $sale_sql['customer']=$vo['id'];
                    $sale_sql=auth('saleclass',$sale_sql);//数据鉴权
                    $sale=Saleclass::where($sale_sql)->select();
                    //统计欠款金额
                    foreach ($sale as $sale_vo) {
                        $difference=bcsub($sale_vo['actual'],$sale_vo['money'],config('decimal'));//计算欠款金额
                        $money=opt_decimal(bcadd($money,$difference,config('decimal')));//累加欠款金额[高精度]
                    }
                    //转存数据
                    $info=[];
                    $info['type']='客户';
                    $info['name']=$vo['name'];
                    $info['number']=$vo['number'];
                    $info['money']=$money;
                    array_push($arr,$info);
                }
            }else{
                //供应商
                $sql=get_sql($input,[
                    'name'=>'full_name_py_link',
                    'number'=>'full_like',
                ],'supplier');//构造SQL
                $sql=auth('supplier',$sql);//数据鉴权
                $supplier=Supplier::where($sql)->select();
                foreach ($supplier as $vo) {
                    $money=0;
                    //1.购货单
                    $purchase_sql['billtype']=['in',[0,1],'OR'];
                    $purchase_sql['supplier']=$vo['id'];
                    $purchase_sql=auth('purchaseclass',$purchase_sql);//数据鉴权
                    $purchase=Purchaseclass::where($purchase_sql)->select();
                    //统计欠款金额
                    foreach ($purchase as $purchase_vo) {
                        $difference=bcsub($purchase_vo['actual'],$purchase_vo['money'],config('decimal'));//计算欠款金额
                        $money=opt_decimal(bcadd($money,$difference,config('decimal')));//累加欠款金额[高精度]
                    }
                    //2.采购入库单
                    $rpurchase_sql['billtype']=['in',[0,1],'OR'];
                    $rpurchase_sql['supplier']=$vo['id'];
                    $rpurchase_sql=auth('rpurchaseclass',$rpurchase_sql);//数据鉴权
                    $rpurchase=Rpurchaseclass::where($rpurchase_sql)->select();
                    //统计欠款金额
                    foreach ($rpurchase as $rpurchase_vo){
                        $difference=bcsub($rpurchase_vo['actual'],$rpurchase_vo['money'],config('decimal'));//计算欠款金额
                        $money=opt_decimal(bcadd($money,$difference,config('decimal')));//累加欠款金额[高精度]
                    }
                    //转存数据
                    $info=[];
                    $info['type']='供应商';
                    $info['name']=$vo['name'];
                    $info['number']=$vo['number'];
                    $info['money']=$money;
                    array_push($arr,$info);
                }
            }
            $formfield=get_formfield('arrears_export','array');//获取字段配置
            //开始构造导出数据
            $excel=[];//初始化导出数据
            //1.填充标题数据
            array_push($excel,['type'=>'title','info'=>'往来单位欠款表']);
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
            $sum_arr=get_sums($table_data,['money']);
            array_push($excel,['type'=>'node','info'=>[
                '总欠款金额:'.$sum_arr['money'],
            ]]);//填充汇总信息
            //4.导出execl
            push_log('导出往来单位欠款表');//日志
            export_excel('往来单位欠款表',$excel);
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
}
