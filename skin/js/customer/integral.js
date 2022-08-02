$('#userpage').selectpage({
    url:'/index/service/user_list',
    tip:'全部制单人',
    valid:'s|user'
});
layui.use(['table','laydate'], function() {
    var table=layui.table;
    var laydate=layui.laydate;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/customer/integral_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
    });//渲染表格
    //时间组件
    laydate.render({
        elem: '#s\\|start_time'
    });
    laydate.render({
        elem: '#s\\|end_time'
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
//操作
function set(id){
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane"><div class="layui-form-item"pane><label class="layui-form-label">操作类型</label><div class="layui-input-block"><input type="radio"name="set"value="inc"title="增加积分"checked><input type="radio"name="set"value="dec"title="减少积分"></div></div><div class="layui-form-item"><label class="layui-form-label">积分数值</label><div class="layui-input-block"><input type="text"id="integral"placeholder="请输入积分数值"class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div></div>';
    layui.use(['layer','form'], function() {
        var form = layui.form;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '积分操作',
                skin: 'layui-layer-rim', //加上边框
                area: ['520px', '290px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    form.render('radio'); //重新渲染
                },
                btn1: function(layero) {
                    //保存
                    var info=pop_info('.pop_box');
                    if (!reg_test('plus',info['integral']) || info['integral']=='0') {
                        dump('积分数值填写错误!');
                    }else {
                        //提交信息
                        info['id']=id;
                        info['set']=$("input[name='set']:checked").val();
                        ajax('POST','/index/customer/set_integral',info,function(resule){
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
//导出操作
function exports(){
    var info=search_info('url');
    jump_info('【 数据请求中 】',"/index/customer/export_integral?"+info,true);
}