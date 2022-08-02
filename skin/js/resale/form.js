layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/resale/form_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
        done: function(res, curr, count){
            table_sum('#data_table',[
                {'text':'单据总金额','key':'total'},
                {'text':'实际总金额','key':'actual'},
                {'text':'实付总金额','key':'money'}
            ]);
        }
    });//渲染表格
    //监听工具条事件
    table.on('tool(table_main)', function(obj){
        var data = obj.data;
        var event = obj.event;
        if(event == 'prints'){
            prints(data.id);//打印
        }else if(event == 'delect'){
            delect(data.id);//常规删除
        }else if(event == 'info'){
            info(data.id);//详情
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
            content: '/index/resale/info?id='+id,
            end:function(){
                search();
            }
        });
    });
}
//审核操作
function auditing(){
    layui.use(['layer','table'], function() {
        layer.confirm('您确定要[审核|反审核]所选数据吗？', {
            btn: ['确定', '取消'], //按钮
            offset: '6%'
        }, function() {
            var arr=[];//初始化数据
            var nod = layui.table.checkStatus('data_table');//获取选中数据
            for (var i = 0; i < nod.data.length; i++) {
                arr.push(nod.data[i].id);//循环加入数据
            }
            //发送请求
            ajax('POST','/index/resale/auditing',{"arr": arr},function(resule){
                if(resule.state=='success'){
                    search();
                    dump('操作成功!');
                    $('.btn_group_right button[batch]').remove();//初始化-批量操作
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
        layer.confirm('您确定要删除所选数据吗？', {
            btn: ['删除', '取消'], //按钮
            offset: '6%'
        }, function() {
            var arr=[];//初始化数据
            //判断删除类型
            if(info=='batch'){
                //批量删除
                var nod = layui.table.checkStatus('data_table');//获取选中数据
                for (var i = 0; i < nod.data.length; i++) {
                    arr.push(nod.data[i].id);//循环加入数据
                }
            }else{
                //常规删除
                arr.push(info);//常规加入数据
            }
            //发送请求
            ajax('POST','/index/resale/del',{
                "arr": arr
            },function(resule){
                if(resule.state=='success'){
                    search();
                    dump('删除成功!');
                    $('.btn_group_right button[batch]').remove();//初始化-批量操作
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
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
    jump_info('【 数据请求中 】',"/index/resale/exports?"+info+"&mode="+type,true);
}
//打印操作
function prints(id){
    layui.use('form', function(){
        layer.open({
          type: 2,
          title: '打印',
          offset: '9%',
          area: ['650px', '330px'],
          content: '/index/resale/prints?id='+ id
        }); 
    });
}