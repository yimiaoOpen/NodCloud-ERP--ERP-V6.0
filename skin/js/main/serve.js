layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-83',
        even: true,
        cols:  [formfield],
        url: '/index/service/serve_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
    });//渲染表格
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
//获取服务数据
function get_serve(){
    var arr=[];
    layui.use('table', function() {
        var table=layui.table;
        var checkStatus = table.checkStatus('data_table');
        arr = checkStatus.data;//获取选中行的数据
    });
    return arr;
}