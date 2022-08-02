$('#merchantpage').selectpage({
    url:'/index/service/merchant_list',
    tip:'全部商户',
    valid:'s|merchant'
});
layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/user/user_list',
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
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane layui-row layui-col-space3"><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">职员账号</label><div class="layui-input-block"><input type="text"id="user"placeholder="请输入职员账号"class="layui-input"></div></div></div><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">职员密码</label><div class="layui-input-block"><input type="text"id="pwd"placeholder="请输入职员密码"class="layui-input"></div></div></div><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">所属商户</label><div class="layui-input-block"><div id="pop_merchantpage"class="selectpage"></div></div></div></div><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">职员名称</label><div class="layui-input-block"><input type="text"id="name"placeholder="请输入职员名称"class="layui-input"></div></div></div><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label"id="upload_region">头像信息</label><div class="layui-input-block"onclick="look_file(this);"><input type="text"id="img"placeholder="点击左侧区域上传"class="layui-input"nod=""disabled="disabled"></div></div></div><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div><more></more></div></div>';
    layui.use(['layer','form','upload'], function() {
        var form = layui.form;
        var upload = layui.upload;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['590px', '300px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    set_more($('#more_html').html());//设置扩展字段
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    //调用选择插件
                    var plug = $('#pop_merchantpage').selectpage({
                        url:'/index/service/merchant_list',
                        tip:'请选择所属商户',
                        valid:'merchant'
                    });
                    //调用上传插件
                    upload.render({
                        elem: '#upload_region',
                        url: '/index/user/upload_img',
                        accept:'images',
                        acceptMime: 'image/*',
                        done: function(resule){
                            if(resule.state=='success'){
                                $('#img').val('[ 已上传 | 点击查看 ]').attr('nod',resule.info);
                            }else if(resule.state=='error'){
                                dump(resule.info);
                            }else{
                                dump('[ Error ] 服务器返回数据错误!');
                            }
                        }
                    });
                    //获取信息
                    if (id > 0) {
                        ajax('POST','/index/user/get_user',{
                            "id": id
                        },function(resule){
                            pop_set('.pop_box',resule);
                            //处理密码输入区域
                            $('#pwd').val('').attr('placeholder','不修改密码请留空');
                            //处理插件数据
                            plug.selectdata=[{'id':resule.merchant,'name':resule.merchantinfo.name}];//赋值插件数据
                            plug.render_data();//渲染插件内容
                            //处理上传数据
                            resule.img==''||($('#img').val('[ 已上传 | 点击查看 ]').attr('nod',resule.img));
                            form.render(); //重新渲染
                        },true);
                    }else{
                        form.render(); //重新渲染
                    }
                },
                btn1: function(layero) {
                    //保存
                    var info=pop_info('.pop_box');
                    if (reg_test('empty',info['user'])) {
                        dump('职员账号不可为空!');
                    }else if(id==0 && reg_test('empty',info['pwd'])){
                        dump('职员密码不可为空!');
                    }else if(reg_test('empty',info['merchant'])) {
                        dump('所属商户不可为空!');
                    }else if(reg_test('empty',info['name'])) {
                        dump('职员名称不可为空!');
                    }else{
                        //提交信息
                        info['id']=id;
                        ajax('POST','/index/user/set_user',info,function(resule){
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
            ajax('POST','/index/user/del_user',{
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