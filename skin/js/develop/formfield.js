layui.use('table', function() {
    var table=layui.table;
    var data_table_cols=[
        {type:'checkbox',fixed: 'left'},
        {field: 'name', title: '表单名称', width: 200, align:'center'},
        {field: 'key', title: '表单标识', width: 200, align:'center'},
        {field: 'data', title: '备注信息', width: 200, align:'center'},
        {field: 'set', title: '相关操作', width: 200, align:'center',fixed:'right',toolbar:'#bar_info'},
    ];//表格选项
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [data_table_cols],
        url: '/index/develop/formfield_list',
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
        if(event == 'copy'){
            copy(data);//复制
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
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane layui-row layui-col-space3"><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">表单名称</label><div class="layui-input-block"><input type="text"id="name"placeholder="请输入表单名称"class="layui-input"lay-verify="required"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">表单标识</label><div class="layui-input-block"><input type="text"id="key"placeholder="请输入表单标识"class="layui-input"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">表单备注</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入表单备注"class="layui-input"></div></div></div><div class="layui-col-md12"><table class="layui-table field_tab"><thead><tr><th width="30px">排序</th><th>字段配置</th><th width="30px">显示</th><th width="20px"onclick="add_field(false);"><i class="layui-icon layui-icon-add-1"></i></th></tr></thead><tbody id="field_main"></tbody></table></div></div></div>';
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['900px', '520px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    //获取信息
                    if (id > 0) {
                        ajax('POST','/index/develop/get_formfield',{
                            "id": id
                        },function(resule){
                            pop_set('.pop_box',resule);
                            //增加字段选项
                            for (var i = 0; i < resule.subinfo.length; i++) {
                                add_field(resule.subinfo[i]);
                            }
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
                        dump('表单名称不可为空!');
                    }else if (reg_test('empty',info['key'])) {
                        dump('表单标识不可为空!');
                    }else {
                        //提交信息
                        info['id']=id;
                        info['info']=get_field();
                        if(info['info'].length>0){
                            ajax('POST','/index/develop/set_formfield',info,function(resule){
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
                        }else{
                            dump('字段信息不可为空!');
                        }
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
            ajax('POST','/index/develop/del_formfield',{
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
//添加字段
function add_field(obj){
    obj=obj||{info:'',show:1};
    checked=obj.show>0?'checked':'';
    layui.use(['layer','form'], function() {
        var html='<tr><td class="bg_sort"></td><td><textarea class="layui-textarea">'+obj.info+'</textarea></td><td><input type="checkbox" lay-skin="primary" '+checked+'></td><td onclick="del_field(this);"><i class="layui-icon layui-icon-delete"></i></td></tr>';
        $('#field_main').append(html);
        $(".field_tab").tableDnD();
        layui.form.render('checkbox');//重新渲染
    });
    
    
}
//删除字段
function del_field(dom){
    layui.use(['layer','table'], function() {
        layer.confirm('删除后不可恢复,您确定删除吗？', {
            btn: ['删除', '取消'], //按钮
            offset: '6%'
        }, function() { 
            $(dom).parent().remove();//删除元素
            layer.closeAll('dialog'); //关闭信息框
        });
    });
}
//获取字段信息
function get_field(){
    var arr=[];
    $('#field_main tr').each(function(){
        var info =$(this).find('textarea').val();
        var show =$(this).find('input[type="checkbox"]').prop('checked')?1:0;
        if(!reg_test('empty',info)){
            arr.push({info:info,show:show});
        }
    });
    return arr;
}
//复制
function copy(data){
    layui.use(['layer','table'], function() {
        layer.confirm('您确定要复制[ '+data.key+' ]字段信息吗？', {
            btn: ['确定', '取消'], //按钮
            offset: '6%'
        }, function() { 
            //发送请求
            ajax('POST','/index/develop/copy_formfield',{
                "id": data.id
            },function(resule){
                if(resule.state=='success'){
                    search();
                    layer.closeAll('dialog'); //关闭信息框
                    dump('复制成功!');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}




