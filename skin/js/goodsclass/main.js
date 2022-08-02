//初始化树状表格
$('#tab').treeTable({
    expandLevel : 1,
    onSelect : function($treeTable, id) {
        window.console && console.log('onSelect:' + id);
    }
});
//详情
function detail(id){
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane"><div class="layui-form-item"><label class="layui-form-label">分类名称</label><div class="layui-input-block"><input type="text"id="name"placeholder="请输入分类名称"class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">所属分类</label><div class="layui-input-block"><input type="text"id="pid"placeholder="请选择所属分类"class="layui-input"onclick="show_ztree(this);"nod=""><div class="ztree_box"><ul id="menu_ztree"class="ztree layui-anim layui-anim-upbit"></ul></div></div></div><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['390px', '320px'], //宽高
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
                        ajax('POST','/index/goodsclass/get_goodsclass',{
                            "id": id
                        },function(resule){
                            pop_set('.pop_box',resule);
                            //设置所属信息
                            if(resule.pid==0){
                                $('#pid').val('顶级分类').attr('nod',0);
                            }else{
                                $('#pid').val(resule.pidinfo.name).attr('nod',resule.pid);
                            }
                            //设置节点选中
                            var zTree = $.fn.zTree.getZTreeObj("menu_ztree");
                            var node = zTree.getNodeByParam("id",resule.pid);
                            zTree.selectNode(node);
                        },true);
                    }
                },
                btn1: function(layero) {
                    //保存
                    var info=pop_info('.pop_box');
                    if (reg_test('empty',info['name'])) {
                        dump('分类名称不可为空!');
                    }else if(reg_test('empty',info['pid'])){
                        dump('所属分类不可为空!');
                    }else {
                        //提交信息
                        info['id']=id;
                        ajax('POST','/index/goodsclass/set_goodsclass',info,function(resule){
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
            ajax('POST','/index/goodsclass/del_goodsclass',{
                "id": id
            },function(resule){
                if(resule.state=='success'){
                    jump_info('删除成功!');
                }else if(resule.state=='exist_data'){
                    dump('该分类下存在子分类,删除失败!');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}