layui.use(['table','form','laydate'], function() {
    var table=layui.table;
    var laydate=layui.laydate;
    var data_table_cols=[
        {field: 'time', title: '登陆时间', width: 200, align:'center'},
        {field: 'ip', title: '登陆IP', width: 200, align:'center'},
        {field: 'user', title: '登陆账号', width: 200, align:'center'},
        {field: 'pwd', title: '登陆密码', width: 200, align:'center'}
    ];//表格选项
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [data_table_cols],
        url: '/index/plug/more?plug_info=loginerror/main/loginerror_list',
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
//清空数据
function empty(){
    layui.use('layer', function() {
        layer.confirm('您确定要清空全部数据？', {
            btn: ['确定', '取消'], //按钮
            offset: '6%',
            shadeClose: true
        }, function() {
            ajax('POST','/index/plug/more?plug_info=loginerror/main/empty_loginerror',{
                "by": 'nodcloud.com'
            },function(resule){
                if(resule.state=='success'){
                    search();
                    dump('清空数据成功!');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}