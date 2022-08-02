layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/merchant/merchant_list',
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
        if(event == 'edit'){
            detail(data.id);//修改
        }else if(event == 'delect'){
            delect(data.id);//常规删除
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
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane layui-row layui-col-space3"><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商户名称</label><div class="layui-input-block"><input type="text"id="name"placeholder="请输入商户名称"class="layui-input"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商户编号</label><div class="layui-input-block"><input type="text"id="number"placeholder="请输入商户编号"class="layui-input"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">联系人员</label><div class="layui-input-block"><input type="text"id="contacts"placeholder="请输入联系人员"class="layui-input"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">联系电话</label><div class="layui-input-block"><input type="text"id="tel"placeholder="请输入联系电话"class="layui-input"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商户地址</label><div class="layui-input-block"><input type="text"id="add"placeholder="请输入商户地址"class="layui-input"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商户税号</label><div class="layui-input-block"><input type="text"id="tax"placeholder="请输入商户税号"class="layui-input"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">开户银行</label><div class="layui-input-block"><input type="text"id="bank"placeholder="请输入开户银行"class="layui-input"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">对公账户</label><div class="layui-input-block"><input type="text"id="account"placeholder="请输入对公账户"class="layui-input"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div><more></more></div></div>';
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['720px', '320px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    set_more($('#more_html').html());//设置扩展字段
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    //获取信息
                    if (id > 0) {
                        ajax('POST','/index/merchant/get_merchant',{
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
                        dump('商户名称不可为空!');
                    }else if(!reg_test('empty',info['tel'])&&(!reg_test('tel',info['tel'])&&!reg_test('phone',info['tel']))){
                        dump('联系电话不正确!');
                    }else if(!reg_test('empty',info['tax'])&&(!reg_test('tax',info['tax']))){
                        dump('商户税号不正确!');
                    }else if(!reg_test('empty',info['account'])&&(!reg_test('number',info['account']))){
                        dump('对公账户不正确!');
                    }else {
                        //提交信息
                        info['id']=id;
                        ajax('POST','/index/merchant/set_merchant',info,function(resule){
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
            ajax('POST','/index/merchant/del_merchant',{
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
    jump_info('稍等，数据请求中',"/index/merchant/export_merchant?"+info,true);
}