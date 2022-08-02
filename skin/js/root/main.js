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
        url: '/index/root/root_list',
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
        if(event == 'root'){
            root(data.id);//权限设置
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
function root(id){
    var html = '<div class="pop_box"><div class="layui-form"><table class="layui-table"id="tabs"><thead><tr><th>功能名称</th><th>新增</th><th>删除</th><th>修改</th><th>报表</th><th>审核</th></tr></thead><tbody><tr><td>购货单</td><td><input type="checkbox"id="purchase_add"lay-skin="primary"></td><td><input type="checkbox"id="purchase_del"lay-skin="primary"></td><td><input type="checkbox"id="purchase_edit"lay-skin="primary"></td><td><input type="checkbox"id="purchase_form"lay-skin="primary"></td><td><input type="checkbox"id="purchase_auditing"lay-skin="primary"></td></tr><tr><td>购货退货单</td><td><input type="checkbox"id="repurchase_add"lay-skin="primary"></td><td><input type="checkbox"id="repurchase_del"lay-skin="primary"></td><td><input type="checkbox"id="repurchase_edit"lay-skin="primary"></td><td><input type="checkbox"id="repurchase_form"lay-skin="primary"></td><td><input type="checkbox"id="repurchase_auditing"lay-skin="primary"></td></tr><tr><td>采购订单</td><td><input type="checkbox"id="opurchase_add"lay-skin="primary"></td><td><input type="checkbox"id="opurchase_del"lay-skin="primary"></td><td><input type="checkbox"id="opurchase_edit"lay-skin="primary"></td><td><input type="checkbox"id="opurchase_form"lay-skin="primary"></td><td><input type="checkbox"id="opurchase_auditing"lay-skin="primary"></td></tr><tr><td>采购入库单</td><td><input type="checkbox"id="rpurchase_add"lay-skin="primary"></td><td><input type="checkbox"id="rpurchase_del"lay-skin="primary"></td><td><input type="checkbox"id="rpurchase_edit"lay-skin="primary"></td><td><input type="checkbox"id="rpurchase_form"lay-skin="primary"></td><td><input type="checkbox"id="rpurchase_auditing"lay-skin="primary"></td></tr><tr><td>销货单</td><td><input type="checkbox"id="sale_add"lay-skin="primary"></td><td><input type="checkbox"id="sale_del"lay-skin="primary"></td><td><input type="checkbox"id="sale_edit"lay-skin="primary"></td><td><input type="checkbox"id="sale_form"lay-skin="primary"></td><td><input type="checkbox"id="sale_auditing"lay-skin="primary"></td></tr><tr><td>销货退货单</td><td><input type="checkbox"id="resale_add"lay-skin="primary"></td><td><input type="checkbox"id="resale_del"lay-skin="primary"></td><td><input type="checkbox"id="resale_edit"lay-skin="primary"></td><td><input type="checkbox"id="resale_form"lay-skin="primary"></td><td><input type="checkbox"id="resale_auditing"lay-skin="primary"></td></tr><tr><td>零售单</td><td><input type="checkbox"id="cashier_add"lay-skin="primary"></td><td><input type="checkbox"id="cashier_del"lay-skin="primary"></td><td><input type="checkbox"id="cashier_edit"lay-skin="primary"></td><td><input type="checkbox"id="cashier_form"lay-skin="primary"></td><td><input type="checkbox"id="cashier_auditing"lay-skin="primary"></td></tr><tr><td>零售退货单</td><td><input type="checkbox"id="recashier_add"lay-skin="primary"></td><td><input type="checkbox"id="recashier_del"lay-skin="primary"></td><td><input type="checkbox"id="recashier_edit"lay-skin="primary"></td><td><input type="checkbox"id="recashier_form"lay-skin="primary"></td><td><input type="checkbox"id="recashier_auditing"lay-skin="primary"></td></tr><tr><td>服务单</td><td><input type="checkbox"id="itemorder_add"lay-skin="primary"></td><td><input type="checkbox"id="itemorder_del"lay-skin="primary"></td><td><input type="checkbox"id="itemorder_edit"lay-skin="primary"></td><td><input type="checkbox"id="itemorder_form"lay-skin="primary"></td><td><input type="checkbox"id="itemorder_auditing"lay-skin="primary"></td></tr><tr><td>积分兑换单</td><td><input type="checkbox"id="exchange_add"lay-skin="primary"></td><td><input type="checkbox"id="exchange_del"lay-skin="primary"></td><td><input type="checkbox"id="exchange_edit"lay-skin="primary"></td><td><input type="checkbox"id="exchange_form"lay-skin="primary"></td><td><input type="checkbox"id="exchange_auditing"lay-skin="primary"></td></tr><tr><td>调拨单</td><td><input type="checkbox"id="allocation_add"lay-skin="primary"></td><td><input type="checkbox"id="allocation_del"lay-skin="primary"></td><td><input type="checkbox"id="allocation_edit"lay-skin="primary"></td><td><input type="checkbox"id="allocation_form"lay-skin="primary"></td><td><input type="checkbox"id="allocation_auditing"lay-skin="primary"></td></tr><tr><td>其他入库单</td><td><input type="checkbox"id="otpurchase_add"lay-skin="primary"></td><td><input type="checkbox"id="otpurchase_del"lay-skin="primary"></td><td><input type="checkbox"id="otpurchase_edit"lay-skin="primary"></td><td><input type="checkbox"id="otpurchase_form"lay-skin="primary"></td><td><input type="checkbox"id="otpurchase_auditing"lay-skin="primary"></td></tr><tr><td>其他出库单</td><td><input type="checkbox"id="otsale_add"lay-skin="primary"></td><td><input type="checkbox"id="otsale_del"lay-skin="primary"></td><td><input type="checkbox"id="otsale_edit"lay-skin="primary"></td><td><input type="checkbox"id="otsale_form"lay-skin="primary"></td><td><input type="checkbox"id="otsale_auditing"lay-skin="primary"></td></tr><tr><td>收款单</td><td><input type="checkbox"id="gather_add"lay-skin="primary"></td><td><input type="checkbox"id="gather_del"lay-skin="primary"></td><td><input type="checkbox"id="gather_edit"lay-skin="primary"></td><td><input type="checkbox"id="gather_form"lay-skin="primary"></td><td><input type="checkbox"id="gather_auditing"lay-skin="primary"></td></tr><tr><td>付款单</td><td><input type="checkbox"id="payment_add"lay-skin="primary"></td><td><input type="checkbox"id="payment_del"lay-skin="primary"></td><td><input type="checkbox"id="payment_edit"lay-skin="primary"></td><td><input type="checkbox"id="payment_form"lay-skin="primary"></td><td><input type="checkbox"id="payment_auditing"lay-skin="primary"></td></tr><tr><td>其他收入单</td><td><input type="checkbox"id="otgather_add"lay-skin="primary"></td><td><input type="checkbox"id="otgather_del"lay-skin="primary"></td><td><input type="checkbox"id="otgather_edit"lay-skin="primary"></td><td><input type="checkbox"id="otgather_form"lay-skin="primary"></td><td><input type="checkbox"id="otgather_auditing"lay-skin="primary"></td></tr><tr><td>其他支出单</td><td><input type="checkbox"id="otpayment_add"lay-skin="primary"></td><td><input type="checkbox"id="otpayment_del"lay-skin="primary"></td><td><input type="checkbox"id="otpayment_edit"lay-skin="primary"></td><td><input type="checkbox"id="otpayment_form"lay-skin="primary"></td><td><input type="checkbox"id="otpayment_auditing"lay-skin="primary"></td></tr><tr><td>资金调拨单</td><td><input type="checkbox"id="eft_add"lay-skin="primary"></td><td><input type="checkbox"id="eft_del"lay-skin="primary"></td><td><input type="checkbox"id="eft_edit"lay-skin="primary"></td><td><input type="checkbox"id="eft_form"lay-skin="primary"></td><td><input type="checkbox"id="eft_auditing"lay-skin="primary"></td></tr><tr><td>库存操作</td><td><input type="checkbox"id="room_add"lay-skin="primary"></td><td>-</td><td>-</td><td>-</td><td>-</td></tr><tr><td>数据报表</td><td>-</td><td>-</td><td>-</td><td><input type="checkbox"id="data_form"lay-skin="primary"></td><td>-</td></tr><tr><td>基础资料</td><td><input type="checkbox"id="basics_add"lay-skin="primary"></td><td><input type="checkbox"id="basics_del"lay-skin="primary"></td><td><input type="checkbox"id="basics_edit"lay-skin="primary"></td><td><input type="checkbox"id="basics_form"lay-skin="primary"></td><td>-</td></tr><tr><td>辅助资料</td><td><input type="checkbox"id="auxiliary_add"lay-skin="primary"></td><td><input type="checkbox"id="auxiliary_del"lay-skin="primary"></td><td><input type="checkbox"id="auxiliary_edit"lay-skin="primary"></td><td><input type="checkbox"id="auxiliary_form"lay-skin="primary"></td><td>-</td></tr><tr><td>高级设置</td><td><input type="checkbox"id="senior_add"lay-skin="primary"></td><td><input type="checkbox"id="senior_del"lay-skin="primary"></td><td><input type="checkbox"id="senior_edit"lay-skin="primary"></td><td><input type="checkbox"id="senior_form"lay-skin="primary"></td><td>-</td></tr></tbody></table></div></div>';
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['680px', '513px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    $('#tabs tbody').append($('#more_html').html());//设置扩展字段
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    //获取信息
                    ajax('POST','/index/root/get_root',{
                        "id": id
                    },function(resule){
                        //默认全选
                        $('#tabs input[type="checkbox"]').prop('checked',true);
                        for (var i = 0; i < resule.length; i++) {
                            if(resule[i].info==0){
                                $('#'+resule[i].name).prop('checked',false);
                            }
                        }
                        form.render(); //重新渲染
                    },true);
                },
                btn1: function(layero) {
                    //保存
                    var arr=[];
                    $('#tabs input[type="checkbox"]').each(function(){
                        var nod={};
                        nod['name']=$(this).attr('id');
                        nod['info']=$(this).is(':checked')?1:0;
                        arr.push(nod);
                    });
                    //提交信息
                    ajax('POST','/index/root/set_root',{
                        id:id,
                        arr:arr
                    },function(resule){
                        if(resule.state=='success'){
                            layer.closeAll();
                            dump('保存成功!');
                        }else if(resule.state=='error'){
                            dump(resule.info);
                        }else{
                            dump('[ Error ] 服务器返回数据错误!');
                        }
                    },true);
                    
                }
            });
        });
    });
}