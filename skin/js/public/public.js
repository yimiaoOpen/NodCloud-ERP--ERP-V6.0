$(function(){
    auto_off();//禁止自动输入
    search_keydown();//回车搜索
});
var reg={
    empty:/^\s*$/g,//空判断
    tel:/^1\d{10}$/,//手机号判断
    phone:/^(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,14}$/,//座机号判断
    tax:/^[A-Z0-9]{15}$|^[A-Z0-9]{17}$|^[A-Z0-9]{18}$|^[A-Z0-9]{20}$/,//税号判断
    number:/^[0-9]*$/,//数字组合判断
    integer:/^[1-9]+\d*$/,//正整数判断不含0
    plus:/^\d+(\.\d{1,2})?$/,//含0正数判断最多2位小数
    email:/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/,//邮箱判断
    time:/^(19|20)\d{2}-(0?\d|1[012])-(0?\d|[12]\d|3[01])$/,//时间正则
    numerical:/^(\-)?\d+(\.\d{1,2})?$/,//正负数值2位小数
    serial:/^[0-9a-zA-Z]*$/ //串码正则
};
//信息框|刷新OR跳转
//info:提示信息|url:跳转网址|state:是否新窗口打开
function jump_info(info,url,state){
    var state=state||false;
    dump(info);
    setTimeout(function(){
        if(url===undefined){
            location.reload();
        }else{
            state ? window.open(url) : window.location.href=url;
        }
    },1000);
}
//消息|非跳转
function dump(msg_text){
    layui.use('layer', function(){
        layui.layer.msg(msg_text);
    });
}
//页面刷新-去除参数
function reload(){
    window.location.href=window.location.pathname;
}
//页面刷新-保留参数
function replace(){
    location.replace(location.href);
}
//显示隐藏更多搜索条件
function show_more_info(dom){
    var obj = $(dom);
    if(obj.attr('more')=='0'){
        //显示
        obj.attr('more','1');
        $('#search_data div[show]').show();
        obj.find('i').removeClass('layui-icon-down').addClass('layui-icon-up');
    }else{
        //隐藏
        obj.attr('more','0');
        $('#search_data div[show]').hide();
        obj.find('i').removeClass('layui-icon-up').addClass('layui-icon-down');
    }
}
//搜索数据
//type:obj对象形式,url链接形式
function search_info(type){
    var data = type == 'obj' ? {} : '';
    $('#search_data input[id],#search_data select[id]').each(function(){
        var group = $(this).attr('id').split('|');
        var sign = $(this).is('[nod]') ? $(this).attr('nod') : $(this).val();
        type == 'obj' ? data[group[1]]=sign : data+="&"+group[1]+'='+sign;
    });
    return type == 'obj' ? data : data.substr(1);
}
//显示隐藏tree
function show_ztree(dom){
    $(dom).next().show();
    $(dom).parent().hover(function(){
        $(dom).next().hide();
    });
}
//通用遮罩AJAX
function ajax(type,url,data,fun,async_type){
    layui.use(['layer'],function(){
        $.ajax({
            type: type,
            url: url,
            data: data,
            async: async_type,
            dataType: "json",
            success: fun,
            error: function(resule){
                console.log(resule);
                dump('[ Error ] 请求处理失败,错误信息已输出控制台!');
            },
            beforeSend:function(){
                //请求开始
                layer.load(1, {
                    shade: [0.1,'#000']
                });
            },
            complete:function(){
                //请求结束
                layer.closeAll('loading');
            }
        });
    });
}
//返回四舍五入数值
function cal(nums){
    return Number((nums-0).toFixed(2)).toString();
}
//自适应弹框位置
function pop_move(index){
    var dom = $('#layui-layer'+index);
    var dom_width = dom.width();
    var body_width = $('body').width();
    var scrollLeft = $(window).scrollLeft();
    if(body_width-dom_width<12){
        dom.css('width',body_width-12).css('left',scrollLeft>12?scrollLeft+12:scrollLeft);
    }
}
//禁止自动输入
function auto_off(){
    $('input').attr('autocomplete','off');
}
//获取弹层数据
function pop_info(dom){
    var info={};
    var more={};
    $(dom+' input[id],'+dom+' select[id]').each(function(){
        var key = $(this).attr('id');
        var val = $(this).is('[nod]') ? $(this).attr('nod') : $(this).val();
        if($(this).parents("[nod='more']").length>0){
            more[key]=val;
        }else{
            info[key]=val;
        }
    });
    $.isEmptyObject(more)||(info['more']=more);
    return info;
}
//设置扩展字段
function set_more(html){
    if(html!=''){
        $('more').after(html);
        $('more').nextAll().each(function(){
            $(this).attr('nod','more');
        });
        $('more').remove();
    }
}
//设置弹层数据
function pop_set(dom,info){
    for (var nod in info) {
        if(info[nod]!=null){
            if(nod=='more' && $(dom).find('div[nod="more"]').length>0){
                //兼容扩展字段
                for (var more in info[nod]) {
                    $(dom).find('#'+more).val(info[nod][more]);
                }
            }else{
                //基础字段赋值
                if(typeof(info[nod])=='object' && ('nod' in info[nod])){
                    //兼容NOD用法
                    $(dom).find('#'+nod).val(info[nod].nod);
                }else{
                    //常规用法
                    $(dom).find('#'+nod).val(info[nod]);
                }
            }
        }
    }
}
//通用预设正则判断
function reg_test(key,val){
    return reg[key].test(val);
}
//获取表单字段
function get_formfield(key){
    var info;
    ajax('POST','/index/service/get_formfield',{
        "key": key
    },function(resule){
        if(resule.state=='success'){
            info=resule.code;
        }else if(resule.state=='error'){
            dump(resule.info);
        }else{
            dump('[ Error ] 服务器返回数据错误!');
        }
    },false);
    return info;
}
//LAYUI表格数据统计
function table_sum(nod,info){
    var val='';
    var dom = $(nod).next();
    if(info.length>0){
        var val_arr=[];
        for (var i = 0; i < info.length; i++) {
            var nums = 0;
            dom.find('.layui-table-main td[data-field="'+info[i].key+'"]').each(function(){
                //排除折叠数据
                if($(this).parents('.fold_tr').length==0){
                    nums=cal((nums-0)+($(this).find('div').html()-0));
                }
            });
            val_arr.push(info[i].text+':'+nums);
        }
        val=val_arr.join(" | ");
    }
    dom.find('.layui-laypage-default').append('<span class="layui-laypage-sum">'+val+'</span>');
}
//查看文件
function look_file(dom){
    var nod = $(dom).find('input[nod]').attr('nod');
    if(nod==undefined || nod==''){
        dump('【 未读取到文件信息 】');
    }else{
        jump_info('【 数据请求中 】',nod,true);
    }
}
//设置隐藏LAYUI字段信息
function hidelayuifield(arr,info){
    info=info.split(",");//字段信息转数组
    for (var i = 0; i < arr.length; i++) {
        if(arr[i].hasOwnProperty('field') && $.inArray(arr[i].field, info)!=-1){
            arr[i].hide=true;
        }
    }
    return arr;
}
//搜索区域回车事件
function search_keydown(){
    $("#search_data input[id]").bind("keydown",function(e){
        var theEvent = e || window.event;
        var code = theEvent.keyCode || theEvent.which || theEvent.charCode;
        if(code == 13){
            var nod=$('#search_data').parent().find('button i[class="layui-icon layui-icon-search"]').parent().attr('onclick');
            nod&&(eval(nod));
        }
    });
}
//单据|赋值插件内容  
function push_selectpage_plug(){
    //变量检验
    if(typeof plug!="undefined" && Object.keys(plug).length>0 && typeof plug_val!="undefined" && Object.keys(plug_val).length>0){
        for (var key in plug_val) {
            if(plug.hasOwnProperty(key)){
                plug[key].selectdata=plug_val[key];//赋值插件数据
                plug[key].render_data();//渲染插件内容
            }
        }
    }
}
//单据|通用附件上传
function bill_upload(){
    layui.use('upload', function() {
        var upload = layui.upload;
        //调用上传插件
        upload.render({
            elem: '#upload_region',
            url: '/index/service/upload_file',
            accept:'file',
            done: function(resule){
                if(resule.state=='success'){
                    $('#file').val('[ 已上传 | 点击查看 ]').attr('nod',resule.info);
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            }
        });
    });
}
//单据|通用日期选择
function bill_time(){
    layui.use('laydate', function() {
        var laydate = layui.laydate;
        //调用时间插件
        laydate.render({
            elem: '#time'
        });
    });
}
//报表|通用日期选择
function form_time(){
    layui.use('laydate', function() {
        var laydate = layui.laydate;
        //调用时间插件
        laydate.render({
            elem: '#s\\|start_time'
        });
        laydate.render({
            elem: '#s\\|end_time'
        });
    });
}
//判断元素是否重复
function isrepeat(arr){
    var hash = {};
    for(var i in arr) {
        if(hash[arr[i]]){
            return true;
        }else{
            hash[arr[i]] = true;
        }
    }
    return false;
}
//设置单据扩展属性
function set_bill_more(){
    if(typeof more_val!="undefined" && more_val!= null && Object.keys(more_val).length>0){
        for (var key in more_val) {
            $('#'+key).val(more_val[key]);
        }
    }
}
//自动填充实际金额为单据金额
function get_total(){
    dump('已快捷录入');
    $('#actual').val($('#total').val());
}
//layui数据表格折叠
//使用前需提前排序表格
function table_fold(config) {
    var dom = $('div[lay-id="'+config.lay_id+'"]');//主DIV
    var main = dom.find('.layui-table-main table');//主表格
    var data = {}; //初始化数据
	dom.find('th[data-field="fold_null"]').remove();//清空DOM结构
    //循环主表格数据并初始化结构数据
    main.find('tr').each(function() {
        var obj = {};//初始化tr数据
        var index=$(this).index();//获取INDEX
        $(this).find('td').each(function() {
            obj[$(this).attr('data-field')] = $(this).find('.layui-table-cell').html();//转存数据
        })
        $(this).attr('nod', obj[config.field]);//赋值主表格标识
        //兼容浮动层
        dom.find('.layui-table-fixed').each(function(){
            $(this).find('tbody tr').eq(index).attr('nod', obj[config.field]);//赋值浮动层标识
        })
        //转存数据
        if (data.hasOwnProperty(obj[config.field])) {
            data[obj[config.field]].push(obj);//增加数据
        } else {
            data[obj[config.field]] = [obj];//首次赋值
        }
    });
    //创建空折叠容器
    dom.find('thead,tbody').each(function(){
        //排除右浮动
        if($(this).parents('.layui-table-fixed-r').length==0){
            var tr_dom=$(this).find('tr').each(function(){
                if($(this).parent().is('thead')){
                    $(this).prepend('<th data-field="fold_null" data-key="0-0-0"><div class="layui-table-cell laytable-cell-0-0-0"></div></th>')//TH
                }else{
                    $(this).prepend('<td data-field="fold_null" data-key="0-0-0"><div class="layui-table-cell laytable-cell-0-0-0"></div></td>')//TD
                }
            });
        }
    });
    //循环新数据
    for (var key in data) {
        //判断数据是否单条
        if(data[key].length > 1) {
            var text_obj={};
            for (var i = 0; i < config.data.length; i++) {
                //获取数据
                var nod_obj = {};
                for (var s = 0; s < config.data[i].nod.length; s++) {
                    //类型判断
                    if(typeof config.data[i].nod[s] === "function"){
                        nod_obj[s]=config.data[i].nod[s](data[key]);
                    }else{
                        if (config.data[i].nod[s] == 'data') {
                            //读取数据
                            nod_obj[s] = data[key][0][config.data[i].field];
                        } else if (config.data[i].nod[s] == 'sum') {
                            //求和
                            var sum = 0;
                            for (var z = 0; z < data[key].length; z++) {
                                sum = cal((sum-0)+(data[key][z][config.data[i].field] - 0));
                            }
                            nod_obj[s] = sum;
                        } else if (config.data[i].nod[s] == 'count') {
                            nod_obj[s] = data[key].length;
                        }
                    }
                }
                var text = config.data[i].text;//获取显示模板
                //赋值模板内容
                for (var nod_obj_key in nod_obj) {
                    text=text.replace('{'+nod_obj_key+'}', nod_obj[nod_obj_key]);
                }
                text_obj[config.data[i].field]=text;
            }
            //插入折叠行[兼容浮动层]
            dom.find('tbody').each(function(){
                var first = $(this).find('tr[nod="' + key + '"]').eq(0);//获取首行DOM
                first.before('<tr class="fold_tr" onclick="set_fold(this);" fold="hide" fold_nod="'+key+'">'+first.html()+'</tr>');//预先插入HTML
                //填充新数据
                $(this).find('tr[fold_nod="'+key+'"]').each(function(){
                    $(this).find('div').empty();//清空源数据
                    $(this).find('td').removeAttr('align').removeAttr('data-content');//清空属性
                    //填充折叠容器
                    $(this).find('td[data-key="0-0-0"] div').html('<i class="layui-icon layui-icon-triangle-r"></i>');
                    //填充数据
                    for (var text_obj_key in text_obj){
                        $(this).find('td[data-field="'+text_obj_key+'"] div').html(text_obj[text_obj_key]);
                    }
                });
                
            });
        }else{
            dom.find('tr[nod="'+key+'"]').removeAttr('nod');//删除单条数据标识
        }
    }
    //写入新STYLE
    var style=dom.find('style').html();
    dom.find('style').html('.layui-table-view tr[nod]{display: none;}.fold_tr{background: #eee;}.laytable-cell-0-0-0{width: 40px;padding: 0;text-align: center;}'+style)
}
//显示隐藏折叠表格
function set_fold(dom){
    var fold=$(dom).attr('fold');
    var fold_nod=$(dom).attr('fold_nod');
    $('.layui-table-view tr[fold_nod="'+fold_nod+'"]').attr('fold',fold=='show'?'hide':'show');
    $('.layui-table-view tr[nod="'+fold_nod+'"]').toggle();
    $('.layui-table-view tr[fold_nod="'+fold_nod+'"] i').attr('class',fold=='show'?'layui-icon layui-icon-triangle-r':'layui-icon layui-icon-triangle-d');
    layui.use('table', function(){
        var table = layui.table;
        table.resize('data_table');//重置表格尺寸
    });
    
}