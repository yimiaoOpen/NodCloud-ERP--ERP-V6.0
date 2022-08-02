$('#userpage').selectpage({
    url:'/index/service/user_list',
    tip:'全部制单人',
    valid:'s|user',
    checkbox:true
});
layui.use(['table','laydate'], function() {
    var table=layui.table;
    var laydate=layui.laydate;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/log/log_list',
        page: true,
        limit: 30,  
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
    });//渲染表格
    //时间组件
    laydate.render({
        elem: '#s\\|start_time'
    });
    laydate.render({
        elem: '#s\\|end_time'
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
//清空操作
function empty(){
    layui.use('layer', function() {
        layer.confirm('您确定要清空所有日志信息吗？', {
            btn: ['清空', '取消'], //按钮
            offset: '6%'
        }, function() {
            //发送请求
            ajax('POST','/index/log/empty_log',{"by": 'nodcloud.com'},function(resule){
                if(resule.state=='success'){
                    search();
                    dump('清空成功!');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}