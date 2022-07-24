layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/attribute/attribute_list',
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
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane layui-row layui-col-space3"><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">属性名称</label><div class="layui-input-block"><input type="text"id="name"placeholder="请输入属性名称"class="layui-input"></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div><more></more><div class="layui-col-md12 attr"><p style="padding-left: 6px;">扩展属性</p><hr/><div class="layui-form-item three_layout"><label class="layui-form-label">扩展名称</label><div class="layui-input-block"><input type="text"placeholder="请输入扩展属性名称"class="layui-input attr_name"><label class="layui-form-label again" onclick="add_attr('+id+');">添加</label></div></div><table class="layui-table"><thead><tr><th width="85%">扩展属性名称</th><th width="15%">操作</th></tr></thead><tbody id="attr_info"></tbody></table></div></div></div>';
    layui.use(['layer','form'], function() {
        var form=layui.form;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: id > 0 ? ['590px', '390px']:['590px', '260px'], //获取宽高
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
                        ajax('POST','/index/attribute/get_attribute',{
                            "id": id
                        },function(resule){
                            pop_set('.pop_box',resule);
                            for (var i = 0; i < resule.subinfo.length; i++) {
                                $('#attr_info').append('<tr><td>'+resule.subinfo[i].name+'</td><td><i class="layui-icon layui-icon-delete" onclick="del_attr(this,'+resule.subinfo[i].id+')"></i></td></tr>');
                            }
                            form.render(); //重新渲染
                        },true);
                    }else{
                        $('.attr').hide();
                        form.render(); //重新渲染
                    }
                },
                btn1: function(layero) {
                    //保存
                    var info=pop_info('.pop_box');
                    if (reg_test('empty',info['name'])) {
                        dump('属性名称不可为空!');
                    }else {
                        //提交信息
                        info['id']=id;
                        ajax('POST','/index/attribute/set_attribute',info,function(resule){
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
            ajax('POST','/index/attribute/del_attribute',{
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
function add_attr(id){
    var name=$('.attr_name').val();
    if(reg_test('empty',name)){
        dump('扩展属性名称不可为空!');
    }else{
        //发送请求
        ajax('POST','/index/attribute/add_attr',{
            "pid": id,
            "name": name
        },function(resule){
            if(resule.state=='success'){
                $('.attr_name').val('');
                $('#attr_info').append('<tr><td>'+name+'</td><td><i class="layui-icon layui-icon-delete" onclick="del_attr(this,'+resule.info+')"></i></td></tr>');
                dump('扩展属性添加成功!');
            }else if(resule.state=='error'){
                dump(resule.info);
            }else{
                dump('[ Error ] 服务器返回数据错误!');
            }
        },true);
    }
}
function del_attr(dom,id){
    layui.use('layer', function() {
        layer.confirm('您确定要删除该扩展属性吗？', {
            btn: ['删除', '取消'], //按钮
            offset: '6%'
        }, function() {
            //发送请求
            ajax('POST','/index/attribute/del_attr',{
                "id": id
            },function(resule){
                if(resule.state=='success'){
                    $(dom).parent().parent().remove();
                    dump('扩展属性删除成功!');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}