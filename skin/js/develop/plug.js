layui.use(['table','layer','form'], function() {
    var table=layui.table;
    var data_table_cols=[
        {field: 'name', title: '插件名称', width: 200, align:'center'},
        {field: 'info', title: '插件介绍', width: 360, align:'center'},
        {field: 'ver', title: '版本号', width: 120, align:'center'},
        {field: 'author', title: '插件作者', width: 160, align:'center'},
        {field: 'state', title: '插件状态', width: 120, align:'center',templet: '<div>{{d.state.name}}</div>'},
        {field: 'set', title: '相关操作', width: 320, align:'center',toolbar:'#bar_info'},
    ];//表格选项
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [data_table_cols],
        url: '/index/develop/plug_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
    });//渲染表格
    //监听工具条事件
    table.on('tool(table_main)', function(obj){
        var data = obj.data;
        var event = obj.event;
        if(event == 'install'){
            //安装
            layer.confirm('您确定要安装该插件？', {
                btn: ['确定', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                ajax('POST','/index/develop/plug_server',{
                    "event": event,
                    "only": data.only
                },function(resule){
                    if(resule.state=='success'){
                        search();
                        dump('插件安装成功!');
                    }else if(resule.state=='error'){
                        dump(resule.info);
                    }else{
                        dump('[ Error ] 服务器返回数据错误!');
                    }
                },true);
            });
        }else if(event == 'discont'){
            //停用
            layer.confirm('您确定要停用该插件？', {
                btn: ['确定', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                ajax('POST','/index/develop/plug_server',{
                    "event": event,
                    "only": data.only
                },function(resule){
                    if(resule.state=='success'){
                        search();
                        dump('插件停用成功!');
                    }else if(resule.state=='error'){
                        dump(resule.info);
                    }else{
                        dump('[ Error ] 服务器返回数据错误!');
                    }
                },true);
            });
        }else if(event == 'enable'){
            //停用
            layer.confirm('您确定要启用该插件？', {
                btn: ['确定', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                ajax('POST','/index/develop/plug_server',{
                    "event": event,
                    "only": data.only
                },function(resule){
                    if(resule.state=='success'){
                        search();
                        dump('插件启用成功!');
                    }else if(resule.state=='error'){
                        dump(resule.info);
                    }else{
                        dump('[ Error ] 服务器返回数据错误!');
                    }
                },true);
            });
        }else if(event == 'uninstall'){
            //停用
            layer.confirm('您确定要卸载该插件？', {
                btn: ['确定', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                ajax('POST','/index/develop/plug_server',{
                    "event": event,
                    "only": data.only
                },function(resule){
                    if(resule.state=='success'){
                        search();
                        dump('插件卸载成功!');
                    }else if(resule.state=='error'){
                        dump(resule.info);
                    }else{
                        dump('[ Error ] 服务器返回数据错误!');
                    }
                },true);
            });
        }else if(event == 'view'){
            //视图
            layer.open({
                type: 2,
                title: data.name+' - '+$(this).html(),
                offset: '1%',
                fixed: false,
                area: ['99%', '98%'],
                shadeClose: true,
                content: '/index/plug/more?plug_info='+$(this).attr('parameter'),
                end:function(){
                    reload();
                }
            }); 
        }else if(event == 'delect'){
            //删除
            layer.confirm('您确定要删除该插件？', {
                btn: ['确定', '取消'], //按钮
                offset: '6%',
                shadeClose: true
            }, function() {
                ajax('POST','/index/develop/plug_server',{
                    "event": event,
                    "only": data.only
                },function(resule){
                    if(resule.state=='success'){
                        search();
                        dump('插件删除成功!');
                    }else if(resule.state=='error'){
                        dump(resule.info);
                    }else{
                        dump('[ Error ] 服务器返回数据错误!');
                    }
                },true);
            });
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