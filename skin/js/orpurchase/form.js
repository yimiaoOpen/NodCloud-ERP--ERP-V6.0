layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/orpurchase/form_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj')
    });//渲染表格
    //监听工具条事件
    table.on('tool(table_main)', function(obj){
        var data = obj.data;
        var event = obj.event;
        if(event == 'prints'){
            prints(data.id);//打印
        }else if(event == 'info'){
            info(data.id);//详情
        }
    });
    //调用插件
    $('.selectpage').each(function(){
        $(this).selectpage({
            url:$(this).attr('url'),
            tip:$(this).attr('tip'),
            valid:$(this).attr('nod'),
            checkbox:$(this).is('[checkbox]')?true:false,
            disabled:$(this).is('[disabled]')?true:false
        });
    });
    form_time();//调用日期插件
});
//条件搜索
function search() {
    layui.use('table', function() {
        layui.table.reload('data_table',{
            where: search_info('obj'),
            page:1
        });
    });
}
//详情
function info(id){
    layui.use('layer', function() {
        layer.open({
            type: 2,
            title: '详情',
            offset: '1%',
            fixed: false,
            area: ['99%', '98%'],
            shadeClose: true,
            content: '/index/orpurchase/info?id='+id,
            end:function(){
                search();
            }
        });
    });
}
//导出类型
function export_type(){
    var html = '<div class="pop_box form_choice"><ul><li onclick="exports(0)"><i class="layui-icon layui-icon-list"></i><p>简易报表</p></li><li onclick="exports(1)"><i class="layui-icon layui-icon-form"></i><p>详细报表</p></li></ul></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '报表类型',
                skin: 'layui-layer-rim', //加上边框
                area: ['390px', '150px'], //宽高
                offset: '12%',
                content: html,
                fixed: false,
                shadeClose: true,
            });
        });
    });
}
//导出操作
function exports(type){
    var info=search_info('url');
    jump_info('【 数据请求中 】',"/index/orpurchase/exports?"+info+"&mode="+type,true);
}
//打印操作
function prints(id){
    layui.use('form', function(){
        layer.open({
          type: 2,
          title: '打印',
          offset: '9%',
          area: ['650px', '330px'],
          content: '/index/orpurchase/prints?id='+ id
        }); 
    });
}