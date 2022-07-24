layui.use('table', function() {
    var table=layui.table;
    var nod=table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        cols:  [formfield],
        url: '/index/service/serial_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
        initSort: {
            field: 'goods',//排序字段
            type: 'desc',//排序方式
        },
        done: function(res, curr, count){
            table_fold({
                lay_id:'data_table',//数据表格标识
                field:'goods',//排序字段
                data:[
                    {field:'name',text:'[ {0} ]-[ 串码数:{1} ]',nod:['data','count']},
                ]
            });
        }
    });//渲染表格
    //监听工具条事件
    table.on('tool(table_main)', function(obj){
        var data = obj.data;
        var event = obj.event;
        if(event == 'info'){
            info(data.id);//详情
        }
    });
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
//导出详情
function exports(){
    var info=search_info('url');
    jump_info('【 数据请求中 】',"/index/service/export_serial?"+info,true);
}
//串码详情
function info(id){
    //视图
    layui.use('layer', function() {
       layer.open({
            type: 2,
            title: '串码详情',
            offset: '1%',
            fixed: false,
            area: ['99%', '98%'],
            shadeClose: true,
            content: '/index/main/serial_info.html?id='+id
        }); 
    });
}