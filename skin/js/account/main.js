layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/account/account_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
        done: function(res, curr, count){
            table_sum('#data_table',[
                {'text':'资金余额汇总','key':'balance'}
            ]);
        }
    });//渲染表格
    //监听工具条事件
    table.on('tool(table_main)', function(obj){
        var data = obj.data;
        var event = obj.event;
        if(event == 'edit'){
            detail(data.id);//修改
        }else if(event == 'delect'){
            delect(data.id);//常规删除
        }else if(event == 'accountinfo'){
            accountinfo(data.id);//明细信息
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
function detail(id){
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane layui-row layui-col-space3"><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">账户名称</label><div class="layui-input-block"><input type="text"id="name"placeholder="请输入账户名称"class="layui-input"></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">账户编号</label><div class="layui-input-block"><input type="text"id="number"placeholder="请输入账户编号"class="layui-input"></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">期初余额</label><div class="layui-input-block"><input type="text"id="initial"placeholder="请输入期初余额"class="layui-input"></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">开账时间</label><div class="layui-input-block"><input type="text"id="createtime"placeholder="请输入开账时间"class="layui-input"></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div><more></more></div></div>';
    layui.use(['layer','form','laydate'], function() {
        var form = layui.form;
        var laydate  = layui.laydate;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['520px', '420px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    set_more($('#more_html').html());//设置扩展字段
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    laydate.render({elem: '#createtime'});//日期选择器
                    //获取信息
                    if (id > 0) {
                        ajax('POST','/index/account/get_account',{
                            "id": id
                        },function(resule){
                            pop_set('.pop_box',resule);
                            form.render(); //重新渲染
                        },true);
                    }else{
                        form.render(); //重新渲染
                    }
                },
                btn1: function(layero) {
                    //保存
                    var info=pop_info('.pop_box');
                    if (reg_test('empty',info['name'])) {
                        dump('账户名称不可为空!');
                    }else if(!reg_test('empty',info['initial'])&&(!reg_test('numerical',info['initial']))){
                        dump('期初余额不正确!');
                    }else if(!reg_test('empty',info['createtime'])&&(!reg_test('time',info['createtime']))){
                        dump('开账时间不正确!');
                    }else {
                        //提交信息
                        info['id']=id;
                        ajax('POST','/index/account/set_account',info,function(resule){
                            if(resule.state=='success'){
                                search();
                                layer.closeAll();
                                dump('保存成功!');
                            }else if(resule.state=='error'){
                                dump(resule.info);
                            }else{
                                dump('[ Error ] 服务器返回数据错误!');
                            }
                        },true);
                    }
                }
            });
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
            ajax('POST','/index/account/del_account',{
                "arr": arr
            },function(resule){
                if(resule.state=='success'){
                    search();
                    dump('删除成功!');
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
//导出操作
function exports(){
    var info=search_info('url');
    jump_info('【 数据请求中 】',"/index/account/export_account?"+info,true);
}
//明细操作
function accountinfo(id){
    //视图
    layui.use('layer', function() {
       layer.open({
            type: 2,
            title: '明细信息',
            offset: '1%',
            fixed: false,
            area: ['99%', '98%'],
            shadeClose: true,
            content: '/index/account/accountinfo.html?id='+id
        }); 
    });
}