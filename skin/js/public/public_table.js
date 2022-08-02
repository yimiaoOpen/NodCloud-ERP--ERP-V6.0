var new_data = {set_id:'-1'};//默认数据
var sm_state=false;//初始化扫码状态|默认为模糊匹配
var run_reserve=false;//预留行执行情况
var lastrow=1;//最后编辑行
var lastcell=0;//最后编辑列
var select_attr={};//初始化辅助属性数据
//尺寸改变重设宽度
function jqgrid_resize(){
    $(window).resize(function(){
        $(grid_id).jqGrid("setGridWidth", $(".form_box").width());
    });
}
//预留空行
function reserve_line(){
    if(!run_reserve){
        for(var i=0;i<8;i++){
            $(grid_id).jqGrid('addRowData','nod', new_data);
        }
        run_reserve=true;
    }
}
//扫码枪切换
function set_sm(dom) {
    if(sm_state){
        sm_state=false;
        $(dom).removeClass().addClass('sm_false');
    }else{
        sm_state=true;
        $(dom).removeClass().addClass('sm_true');
        $(grid_id).jqGrid("nextCell",lastrow,0);//激活首行输入
    }
}
//设置操作列
function set_formatter(value, grid, rows, state){
    return "<p><i class='layui-icon layui-icon-add-1 add_row' title='新增'></i><i class='nod-icon nod-icon-jianhao del_row' title='删除'></i></p>";
}
//增加行
$(document).on("click", ".add_row", function() {
    $(grid_id).jqGrid('addRowData','nod', new_data);
});
//删除行
$(document).on("click", ".del_row", function() {
    var row_id = $(grid_id).jqGrid('getGridParam','selrow');//获取当前行ID
    $(grid_id).jqGrid('delRowData', row_id);//删除当前行
    $(grid_id).find('tr').length<2&&($(grid_id).jqGrid('addRowData','APE', new_data));//至少留一行
    cal_total();//统计数据
});
//设置商品名称
function name_elem (value, options) {
    return "<div class='goods_group'><input type='text' value='"+value+"'><i class='layui-icon layui-icon-list goods_info' onclick='get_goods();'></i></div>";
};
//保存商品名称
function name_value(elem, operation, value) {
    var scan=false;
    var val=$(elem).find('input').val();
    if(val!==""){
        var row_id = $(elem).parents('tr').attr('id');//获取当前行ID
        var row_info  = $(grid_id).jqGrid('getRowData',row_id);//获取当前行数据
        //判断是否非空行
        if(row_info.set_id=='-1'){
            $(elem).find('input').val('');
            scan_info(val);//不为空-扫码处理
            scan=true;//触发扫码-赋值空标识
        }
    }
    return scan?'':val;
};
//设置辅助属性
function attr_elem (value, options) {
    var html='';
    var row_id = $(grid_id).jqGrid('getGridParam','selrow');//获取当前行ID
    var row_info  = $(grid_id).jqGrid('getRowData',row_id);//获取当前行数据
    if(reg_test('empty',row_info.attr_nod) || row_info.attr_nod=='-1'){
        html += '<select lay-ignore><option value="-1"'
        html += price_type==false?'':' '+price_type+'="-1"';//兼容不显示价格字段
        html += '>无须选择</option></select>';
    }else{
        var attr=select_attr[row_info.set_id];
        html += "<select lay-ignore>"
        for (var i = 0; i < attr.length; i++) {
            html += '<option value="'+attr[i].nod+'"';
            html += price_type==false?'':' '+price_type+'="'+attr[i][price_type]+'"';//兼容不显示价格字段
            html += '>'+attr[i].name+'</option>';
        }
        html += '</select>';
    }
    return html;
};
//保存辅助属性
function attr_value(elem, operation, value) {
    var info='';
    var val=$(elem).val();
    var row_id=$(elem).parents('tr').attr('id');//获取当前行ID
    if(val!=='-1'){
        if(price_type!=false){
            $(grid_id).jqGrid('setCell',row_id,'price',$(elem).find("option:selected").attr(price_type));//设置辅助属性价格
            dump('辅助属性 - 商品单价已更新');
        }
        $(grid_id).jqGrid('setCell',row_id,'attr_nod',val);//设置辅助属性组合
        info=$(elem).find("option:selected").text();
    }
    return info;
};
//设置串码
function serial_elem (value, options) {
    var type='set_serial(this);';//基础商品信息
    goods_source=='room'&&(type='get_serial(this);');//仓储商品信息
    return "<div class='serial_group'><input type='text' value='"+value+"'><i class='layui-icon layui-icon-list serial_info' onclick='"+type+"'></i></div>";
};
//保存串码
function serial_value(elem, operation, value) {
    var val=$(elem).find('input').val();
    return val;
};
//弹出串码输入框
function set_serial(dom){
    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane"><textarea rows="10" placeholder="录入多个串码时每行一个" id="serial" class="layui-textarea"></textarea></div></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '串码详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['630px', '350px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    //获取现有数值
                    var val = $(dom).prev().val();
                    if(val!==""){
                        var arr = val.split(','); //字符分割
                        for (var i = 0; i < arr.length; i++) {
                            $('#serial').val($('#serial').val()+arr[i]+'\n');//追加赋值并换行
                        }
                    }
                },
                btn1: function(layero) {
                    //保存
                    var serial_arr=$('#serial').val().split('\n');
                    var data =[];
                    for (var s = 0; s < serial_arr.length; s++) {
                        //排除空白行
                        if(serial_arr[s]!==""){
                            if(!reg_test('serial',serial_arr[s])){
                                dump('第'+(s+1)+'行串码不正确');
                                return false;
                            }else{
                                data.push(serial_arr[s]);//转存数组
                            }
                        }
                    }
                    var row_id=$(grid_id).jqGrid('getGridParam','selrow');//获取当前行ID
                    var row_info=$(grid_id).jqGrid('getRowData',row_id);//获取当前行数据
                    $(grid_id).jqGrid('setCell',row_id,'serial',data.toString());//设置串码文本
                    $(grid_id).jqGrid('setCell',row_id,'nums',data.length);//设置数量
                    $(grid_id).jqGrid('setCell',row_id,'total',((data.length)*(row_info.price-0)));//设置金额
                    layer.closeAll();//关闭层
                    dump('已自动计算数量以及金额');
                }
            });
        });
    });
}
//弹出串码录入框
function get_serial(dom){
    $(grid_id).jqGrid("saveCell",lastrow,lastcell);//保存当前编辑的单元格
    var row_id = $(grid_id).jqGrid('getGridParam','selrow');//获取当前行ID
    var row_info  = $(grid_id).jqGrid('getRowData',row_id);//获取当前行数据
    if(reg_test('empty',row_info.serial_info)){
        dump('无需录入串码');
    }else{
        var html = '<div class="pop_box"><div class="layui-form layui-form-pane"><div class="layui-form-item"><label class="layui-form-label">串码内容</label><div class="layui-input-block"><select id="serial"class="layui-input"multiple lay-ignore></select></div></div></div></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                layer.open({
                    id:'pop_main',
                    type: 1,
                    title: '串码详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['630px', '200px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        var val = $(dom).prev().val();
                        //填充下拉数值
                        var serial_arr=row_info.serial_info.split(',');
                        for (var s = 0; s < serial_arr.length; s++) {
                            $('#serial').append('<option value="'+serial_arr[s]+'">'+serial_arr[s]+'</option>');
                        }
                        if(reg_test('empty',val)){
                            $('#serial').select2({placeholder: "请选择串码"});//初始化
                        }else{
                            $('#serial').val(val.split(',')).select2({placeholder: "请选择串码"});//赋值数据
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        var info=$('#serial').select2('val');
                        if(reg_test('empty',info)){
                            dump('串码内容不可为空');
                        }else{
                            $(grid_id).jqGrid('setCell',row_id,'serial',info.toString());//设置串码文本
                            $(grid_id).jqGrid('setCell',row_id,'nums',info.length);//设置数量
                            $(grid_id).jqGrid('setCell',row_id,'total',cal((info.length)*(row_info.price-0)));//设置金额
                            layer.closeAll();//关闭层
                            dump('已自动计算数量以及金额');
                        }
                    }
                });
            });
        });
    }
}
//批量设置仓库
function set_warehouse(){
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane"><div class="layui-form-item"><label class="layui-form-label">所入仓库</label><div class="layui-input-block"><select id="set_warehouse" class="layui-input" lay-search></select></div></div><blockquote class="layui-elem-quote layui-quote-nm">该操作可批量设置所有行的所入仓库</blockquote></div></div>';
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '批量设置',
                skin: 'layui-layer-rim', //加上边框
                area: ['460px', '260px'], //宽高
                offset: '6%',
                content: html, 
                btn: ['确定', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    for (var i = 0; i < warehouse_arr.db.length; i++) {
                        $('#set_warehouse').append('<option value="'+warehouse_arr.db[i].id+'">'+warehouse_arr.db[i].name+'<option>');
                    }
                    form.render();
                },
                btn1: function(layero) {
                    //批量设置
                    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
                    var rows = $(grid_id).jqGrid("getGridParam",'data'); //获取表格数据
                    var warehouse_id=$('#set_warehouse').val();
                    var warehouse_name=$('#set_warehouse').find("option:selected").text();
                    for (var i = 0; i < rows.length; i++) {
                        if(rows[i].set_id !== '-1'){
                            $(grid_id).jqGrid('setCell',rows[i].id,'warehouse_id',warehouse_id);//设置行仓库ID
                            $(grid_id).jqGrid('setCell',rows[i].id,'warehouse',warehouse_name);//设置行仓库名称
                        }
                    }
                    layer.closeAll();
                    dump('单据非空行所入仓库已批量设置为-'+warehouse_name);
                }
            });
        });
    });
}
//批量设置仓库
function set_towarehouse(){
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane"><div class="layui-form-item"><label class="layui-form-label">调拨仓库</label><div class="layui-input-block"><select id="set_towarehouse" class="layui-input" lay-search></select></div></div><blockquote class="layui-elem-quote layui-quote-nm">该操作可批量设置所有行的调拨仓库</blockquote></div></div>';
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '批量设置',
                skin: 'layui-layer-rim', //加上边框
                area: ['460px', '260px'], //宽高
                offset: '6%',
                content: html, 
                btn: ['确定', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    for (var i = 0; i < warehouse_arr.db.length; i++) {
                        $('#set_towarehouse').append('<option value="'+warehouse_arr.db[i].id+'">'+warehouse_arr.db[i].name+'<option>');
                    }
                    form.render();
                },
                btn1: function(layero) {
                    //批量设置
                    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
                    var rows = $(grid_id).jqGrid("getGridParam",'data'); //获取表格数据
                    var towarehouse_id=$('#set_towarehouse').val();
                    var towarehouse_name=$('#set_towarehouse').find("option:selected").text();
                    for (var i = 0; i < rows.length; i++) {
                        if(rows[i].set_id !== '-1'){
                            $(grid_id).jqGrid('setCell',rows[i].id,'towarehouse_id',towarehouse_id);//设置行仓库ID
                            $(grid_id).jqGrid('setCell',rows[i].id,'towarehouse',towarehouse_name);//设置行仓库名称
                        }
                    }
                    layer.closeAll();
                    dump('单据非空行调拨仓库已批量设置为-'+towarehouse_name);
                }
            });
        });
    });
}
//设置批次串码隐藏显示
function tab_hiddens(sys){
    //判断批次
    if(sys.enable_batch!="0"){
        $(grid_id).setGridParam().showCol('batch').trigger("reloadGrid");//显示批次
    }
    //判断串码
    if(sys.enable_serial!="0"){
        $(grid_id).setGridParam().showCol('serial').trigger("reloadGrid");//显示串码
    }
}
//获取商品信息
function get_goods(attr){
    var refer={};//初始化额外参数
    attr=arguments[0]||{};//初始化默认值
    refer = $.extend(true, refer, attr);//合并参数
    typeof hide_field!="undefined"&&(refer = $.extend(true, refer, hide_field));//合并参数
    typeof pop_parameter!="undefined"&&(refer = $.extend(true, refer, pop_parameter));//合并参数
    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var url='/index/main/base_goods.html';//基础商品信息
    goods_source=='room'&&(url='/index/main/room_goods');//仓储商品信息
    if(!$.isEmptyObject(refer)){
        for (var key in refer) {
            url+='&'+key+'='+refer[key];
        }
        url = url.replace(/&/,"?"); 
    }
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            var id = layer.open({
                id:'pop_main',
                type: 2,
                title: '商品信息',
                skin: 'layui-layer-rim', //加上边框
                area: ['860px', '520px'], //宽高
                offset: '6%',
                content: url,
                btn: ['确定', '取消'],
                fixed: false,
                shadeClose: true,
                btn1: function(layero) {
                    var name='layui-layer-iframe'+id;
                    var arr = $('iframe[name="'+name+'"]')[0].contentWindow.get_goods();
                    if(arr.length>0){
                        pop_data(arr,true);
                        layer.closeAll();//关闭层
                    }else{
                        dump('您还未选择数据!');
                    }
                }
            });
        });
    });
}
//添加数据到主表格中|删除空白行
//type:相同数据是否自增NUMS字段
function add_table_row(row,type){
    type=arguments[1]||false;//初始化默认值
    var eq={};//初始化相等数据
    var row_id;//初始化操作行
    var empty=[];//初始化空行数据
    var data = $(grid_id).jqGrid("getGridParam",'data'); //获取表格数据
    for (var i = 0; i < data.length; i++) {
        if(data[i].set_id == '-1'){
            empty.push(data[i].id);//转存空行ID
        }else if(row.set_id==data[i].set_id){
            eq=data[i];//如果相同则转存
        }
    }
    //删除空白行
    for (var s = 0; s < empty.length; s++) {
        $(grid_id).jqGrid('delRowData', empty[s]);
    }
    //判断是否相同数据递增数量字段
    if(type && !$.isEmptyObject(eq)){
        //自增数量字段
        $(grid_id).jqGrid('setCell',eq.id,'nums',((eq.nums-0)+1));
        row_id = eq.id;
    }else{
        row_id = $(grid_id).jqGrid('addRowData','nod', row);//添加新数据
    }
    return row_id;
}
//扫码状态
function scan_info(value){
    var url='/index/service/base_goods_list';//基础商品信息
    goods_source=='room'&&(url='/index/service/room_goods_list');//仓储商品信息
    var info=sm_state?{'code':value}:{'name':value};
    info['page']=1;info['limit']=2;//补充页数信息
    $.post(url,info, function(re) {
        if(re.data.length==0){
            dump('未查找到商品信息，请核实!');
        }else if(re.data.length==1){
            pop_data([re.data[0]],true);//处理返回数据
        }else if(re.data.length>1){
            //弹层
            var attr = sm_state?{code:value}:{name:value};
            get_goods(attr);//弹框|传入类型和条件
        }
        $(grid_id).jqGrid("nextCell",lastrow,0);
    });
}
//获取表格数据
function tab_info(relation){
    relation=arguments[0]||{};//初始化默认值
    var info = $(grid_id).jqGrid("getGridParam",'data'); //获取表格数据
    var arr=[];
    for (var i = 0; i < info.length; i++){
        if(info[i].set_id !== "-1"){
            var obj={};
            var more={};
            for (var key in info[i]) {
                //排除ID字段
                if(key!='id'){
                    var nod = key.split('_');//分割串码字符串
                    if(nod[0]=='more'){
                        //扩展属性
                        more[nod[1]]=info[i][key];//设置扩展属性
                    }else{
                        //正常属性
                        if(relation.hasOwnProperty(key)){
                            obj[relation[key]]=info[i][key];//替换标识赋值
                        }else{
                            obj.hasOwnProperty(key)||(obj[key]=info[i][key]);//常规标识赋值
                        }
                    }
                }
            }
            //判断是否加入扩展数据
            $.isEmptyObject(more)||(obj['more']=more);
            arr.push(obj);
        }
    }
    return arr;
}
//填充表格数据
function set_bill_info(){
    if(typeof bill_info!="undefined" && Object.keys(bill_info).length>0){
        pop_data(bill_info,false);
    }
}
//---------------服务单---------------//
//设置服务名称
function serve_elem (value, options) {
    return "<div class='goods_group'><input type='text' value='"+value+"'><i class='layui-icon layui-icon-list goods_info' onclick='get_serve();'></i></div>";
};
//保存服务名称
function serve_value(elem, operation, value) {
    var scan=false;
    var val=$(elem).find('input').val();
    if(val!==""){
        var row_id = $(elem).parents('tr').attr('id');//获取当前行ID
        var row_info  = $(grid_id).jqGrid('getRowData',row_id);//获取当前行数据
        //判断是否非空行
        if(row_info.set_id=='-1'){
            $(elem).find('input').val('');
            scan_serve(val);//不为空-扫码处理
            scan=true;//触发扫码-赋值空标识
        }
    }
    return scan?'':val;
};
//扫码状态
function scan_serve(value){
    var url='/index/service/serve_list';//基础商品信息
    var info={
        name:value,
        page:1,
        limit:2
    };
    $.post(url,info, function(re) {
        if(re.data.length==0){
            dump('未查找到服务信息，请核实!');
        }else if(re.data.length==1){
            pop_data([re.data[0]],true);//处理返回数据
        }else if(re.data.length>1){
            //弹层
            var attr = {name:value};
            get_serve(attr);//弹框|传入类型和条件
        }
        $(grid_id).jqGrid("nextCell",lastrow,0);
    });
}
//获取商品信息
function get_serve(attr){
    var refer={};//初始化额外参数
    attr=arguments[0]||{};//初始化默认值
    refer = $.extend(true, refer, attr);//合并参数
    typeof hide_field!="undefined"&&(refer = $.extend(true, refer, hide_field));//合并参数
    typeof pop_parameter!="undefined"&&(refer = $.extend(true, refer, pop_parameter));//合并参数
    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var url='/index/main/serve.html';//基础商品信息
    if(!$.isEmptyObject(refer)){
        for (var key in refer) {
            url+='&'+key+'='+refer[key];
        }
        url = url.replace(/&/,"?"); 
    }
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            var id = layer.open({
                id:'pop_main',
                type: 2,
                title: '商品信息',
                skin: 'layui-layer-rim', //加上边框
                area: ['860px', '520px'], //宽高
                offset: '6%',
                content: url,
                btn: ['确定', '取消'],
                fixed: false,
                shadeClose: true,
                btn1: function(layero) {
                    var name='layui-layer-iframe'+id;
                    var arr = $('iframe[name="'+name+'"]')[0].contentWindow.get_serve();
                    if(arr.length>0){
                        pop_data(arr,true);
                        layer.closeAll();//关闭层
                    }else{
                        dump('您还未选择数据!');
                    }
                }
            });
        });
    });
}