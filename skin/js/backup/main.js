layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/backup/backup_list',
        page: true,
        limit: 30,  
        limits: [30,60,90,150,300],
        method: 'post'
    });//渲染表格
    //监听工具条事件
    table.on('tool(table_main)', function(obj){
        var data = obj.data;
        var event = obj.event;
        if(event == 'restore'){
            restore(data.name);//恢复备份
        }else if(event == 'delect'){
            delect(data.name);//常规删除
        }
    });
    //监听批量操作
    table.on('checkbox(table_main)', function(obj){
        var nod = table.checkStatus('data_table');
        $('.btn_group_right button[batch]').remove();//初始化-删除操作
        if(nod.data.length>0){
            $('.btn_group_right').prepend($('#batch_html').html());
        }
    });
});
//搜索数据
function search() {
    layui.use('table', function() {
        layui.table.reload('data_table',{page:1});
    });
}
//备份数据
function new_backup(){
    ajax('POST','/index/backup/new_backup',{"by": 'nodcloud.com'},function(resule){
        if(resule.state=='success'){
            search();
            dump('备份成功!');
            $('.btn_group_right button[batch]').remove();//初始化-删除操作
        }else if(resule.state=='error'){
            dump(resule.info);
        }else{
            dump('[ Error ] 服务器返回数据错误!');
        }
    },true);
}
//恢复备份
function restore(name){
    layui.use(['layer'], function() {
        layer.confirm('您确定要恢复该备份文件？', {
            btn: ['恢复', '取消'], //按钮
            offset: '6%'
        }, function() {
            //发送请求
            ajax('POST','/index/backup/restore',{
                "name": name
            },function(resule){
                if(resule.state=='success'){
                    dump('恢复备份成功!');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}
//删除操作
function delect(info){
    layui.use(['layer','table'], function() {
        layer.confirm('您确定要删除所选备份文件？', {
            btn: ['删除', '取消'], //按钮
            offset: '6%'
        }, function() {
            var arr=[];//初始化数据
            //判断删除类型
            if(info=='batch'){
                //批量删除
                var nod = layui.table.checkStatus('data_table');//获取选中数据
                for (var i = 0; i < nod.data.length; i++) {
                    arr.push(nod.data[i].name);//循环加入数据
                }
            }else{
                //常规删除
                arr.push(info);//常规加入数据
            }
            //发送请求
            ajax('POST','/index/backup/del_backup',{
                "arr": arr
            },function(resule){
                if(resule.state=='success'){
                    search();
                    dump('删除备份成功!');
                    $('.btn_group_right button[batch]').remove();//初始化-删除操作
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}