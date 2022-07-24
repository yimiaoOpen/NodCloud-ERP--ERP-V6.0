layui.use(['table','form'], function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/code/code_list',
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
        if(event == 'view'){
            view(data);//修改
        }else if(event == 'edit'){
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
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane layui-row layui-col-space3"><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">条码名称</label><div class="layui-input-block"><input type="text"id="name"placeholder="请输入条码名称"class="layui-input"></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">条码内容</label><div class="layui-input-block"><input type="text"id="code"placeholder="请输入条码内容"class="layui-input"></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">条码类型</label><div class="layui-input-block"><select id="type"><option value="0">条形码</option><option value="1">二维码</option></select></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div><more></more></div></div>';
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['520px', '366px'], //宽高
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
                        ajax('POST','/index/code/get_code',{
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
                        dump('条码名称不可为空!');
                    }else if (reg_test('empty',info['code'])) {
                        dump('条码内容不可为空!');
                    }else {
                        //提交信息
                        info['id']=id;
                        ajax('POST','/index/code/set_code',info,function(resule){
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
            ajax('POST','/index/code/del_code',{
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
function view(data){
    var config={};
    if(data.type.nod=='0'){
        //条形码
        config.area=['390px', '160px'];
        config.url='/index/code/view?text='+data.code+'&type=txm';
    }else if(data.type.nod=='1'){
        //二维码
        config.area=['320px', '320px'];
        config.url='/index/code/view?text='+data.code+'&type=ewm';
    }
    var html='<div class="pop_box"><img id="code_url"></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            var index = layer.open({
                type: 1,
                title: '图像信息',
                skin: 'layui-layer-rim', //加上边框
                area: config.area, //宽高
                offset: '6%',
                content: html,
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    $('#code_url').attr('src',config.url).css({
                        'display': 'block',
                        'margin': '0 auto',
                        'max-width': '100%'
                    });
                }
            });
        });
    });
}
//模板下载
function download_file(){
    jump_info('【 数据请求中 】','http://cdn.nodcloud.com/erp/xlsx/条码导入模板.xlsx',true);
}
//导入操作
function imports(){
    var html='<div class="pop_box"><ul class="imports_ul"><li>1.该功能适用于批量导入数据。</li><li>2.您需要下载数据模板后使用Excel录入数据。</li><li>3.录入数据时，请勿修改首行数据标题以及排序。</li><li>4.请查阅使用文档获取字段格式内容以及相关导入须知。</li><li>5.点击下方上传文件按钮，选择您编辑好的文件即可。</li></ul><hr><div class="imports_box"><button class="layui-btn"onclick="download_file()">下载模板</button><button class="layui-btn layui-btn-primary"id="upload_btn">上传文件</button></div></div>';
    layui.use(['layer','upload'], function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '导入数据',
                skin: 'layui-layer-rim', //加上边框
                area: ['430px', '290px'], //宽高
                offset: '6%',
                content: html,
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    pop_move(index);//兼容手机弹层
                    //弹出后回调
                    layui.upload.render({
                        elem: '#upload_btn',
                        url: '/index/code/import_code',
                        accept: 'file',
                        exts: 'xlsx',
                        done: function(resule) {
                            if(resule.state=="success"){
                                search();
                                layer.closeAll();
                                dump('恭喜你，成功导入'+resule.info+'条数据！');
                            }else if(resule.state=='error'){
                                dump(resule.info);
                            }else{
                                dump('[ Error ] 服务器返回数据错误!');
                            }
                        }
                    });
                }
            });
        });
    });
}
//导出操作
function exports(){
    var info=search_info('url');
    jump_info('【 数据请求中 】',"/index/code/export_code?"+info,true);
}