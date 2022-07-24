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
        url: '/index/auth/auth_list',
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
        if(event == 'auth'){
            auth(data.id);//权限设置
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
function auth(id){
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane layui-row layui-col-space3"><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">商户</label><div class="layui-input-block"><div id="pop_merchantpage"class="selectpage"nod="merchant"tip="所有商户"url="/index/service/merchant_list"></div></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">客户</label><div class="layui-input-block"><div id="pop_customerpage"class="selectpage"nod="customer"tip="所有客户"url="/index/service/customer_list"></div></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">供应商</label><div class="layui-input-block"><div id="pop_supplierpage"class="selectpage"nod="supplier"tip="所有供应商"url="/index/service/supplier_list"></div></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">仓库</label><div class="layui-input-block"><div id="pop_warehousepage"class="selectpage"nod="warehouse"tip="所有仓库"url="/index/service/warehouse_list"></div></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">职员</label><div class="layui-input-block"><div id="pop_userpage"class="selectpage"nod="user"tip="所有职员"url="/index/service/user_list"></div></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">资金账户</label><div class="layui-input-block"><div id="pop_accountpage"class="selectpage"nod="account"tip="所有资金账户"url="/index/service/account_list"></div></div></div></div><more></more></div></div>';
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['620px', '460px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    set_more($('#more_html').html());//设置扩展字段
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    var plug={};
                    $('.pop_box .selectpage').each(function(){
                        plug[$(this).attr('nod')]=$(this).selectpage({
                            url:$(this).attr('url'),
                            tip:$(this).attr('tip'),
                            valid:$(this).attr('nod'),
                            checkbox:true,
                            push:{'notauth':true}
                        });
                    });
                    //获取信息
                    ajax('POST','/index/auth/get_auth',{
                        "id": id
                    },function(resule){
                        for (var i = 0; i < resule.length; i++) {
                            //处理插件数据
                            plug[resule[i].name].selectdata=resule[i].info;//赋值插件数据
                            plug[resule[i].name].render_data();//渲染插件内容
                        }
                    },true);
                },
                btn1: function(layero) {
                    //保存
                    var arr=[];//初始化数据信息
                    var info=pop_info('.pop_box');
                    for (var key in info) {
                        if(info[key]!==''){
                            var nod={};
                            nod['name']=key;
                            nod['info']=info[key].split(',');
                            arr.push(nod);
                        }
                    }
                    //提交信息
                    ajax('POST','/index/auth/set_auth',{
                        id:id,
                        arr:JSON.stringify(arr)
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