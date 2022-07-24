//初始化树状表格
$('#tab').treeTable({
    expandLevel : 1,
    onSelect : function($treeTable, id) {
        window.console && console.log('onSelect:' + id);
    }
});
//详情
function detail(id){
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane"><div class="layui-form-item"><label class="layui-form-label">行为名称</label><div class="layui-input-block"><input type="text"id="name"placeholder="请输入行为名称"class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">所属行为</label><div class="layui-input-block"><input type="text"id="pid"placeholder="请选择所属行为"class="layui-input"onclick="show_ztree(this);"nod=""><div class="ztree_box"><ul id="menu_ztree"class="ztree layui-anim layui-anim-upbit"></ul></div></div></div><div class="layui-form-item"><label class="layui-form-label">行为内容</label><div class="layui-input-block"><input type="text"id="value"placeholder="请输入行为内容"class="layui-input"></div></div><div class="layui-form-item state"><label class="layui-form-label">行为状态</label><div class="layui-input-block"><select id="state"><option value="1">正常</option><option value="0">禁用</option></select></div></div><div class="layui-form-item"><label class="layui-form-label">行为排序</label><div class="layui-input-block"><input type="text"id="sort"placeholder="请输入行为排序"class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div></div>';
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['390px', '450px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    //弹出后回调
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
        			$.fn.zTree.init($("#menu_ztree"), {
                        callback: {
                            onClick: function(event, treeId, treeNode) {
                                $('#pid').val(treeNode.name).attr('nod',treeNode.id);
                                $('.ztree_box').hide();
                            }
                        }
            		}, ztree_data);
                    //获取信息
                    if (id > 0) {
                        ajax('POST','/index/develop/get_action',{
                            "id": id
                        },function(resule){
                            pop_set('.pop_box',resule);
                            //设置所属信息
                            if(resule.pid==0){
                                $('.state').hide();
                                $('#pid').val('顶级行为').attr('nod',0);
                            }else{
                                $('#pid').val(resule.pidinfo.name).attr('nod',resule.pid);
                            }
                            //设置节点选中
                            var zTree = $.fn.zTree.getZTreeObj("menu_ztree");
                            var node = zTree.getNodeByParam("id",resule.pid);
                            zTree.selectNode(node);
                            form.render(); //重新渲染
                        },true);
                    }else{
                        $('.state').hide();
                        form.render(); //重新渲染
                    }
                },
                btn1: function(layero) {
                    //保存
                    var info=pop_info('.pop_box');
                    if (reg_test('empty',info['name'])) {
                        dump('行为名称不可为空!');
                    }else if(reg_test('empty',info['pid'])){
                        dump('所属行为不可为空!');
                    }else if(id == pid){
                        dump('所属行为不正确!');
                    }else {
                        //提交信息
                        info['id']=id;
                        ajax('POST','/index/develop/set_action',info,function(resule){
                            if(resule.state=='success'){
                                jump_info('保存成功!');
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
//删除
function del(id){
    layui.use('layer', function() {
        layer.confirm('您确定要删除该数据吗？', {
            btn: ['删除', '取消'], //按钮
            offset: '6%'
        }, function() {
            ajax('POST','/index/develop/del_action',{
                "id": id
            },function(resule){
                if(resule.state=='success'){
                    jump_info('删除成功!');
                }else if(resule.state=='exist_data'){
                    dump('该行为下存在子行为,删除失败!');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}