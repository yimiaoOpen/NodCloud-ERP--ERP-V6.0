//初始化框架
layui.config({
	base: '/skin/js/' //静态资源所在路径
}).extend({
	index: 'lib/index' //主入口模块
}).use('index');
//设置商户
function set_merchant(){
    var html = '<div class="pop_box"style="padding:12px"><div class="layui-form layui-form-pane"><div class="layui-form-item"><label class="layui-form-label">商户</label><div class="layui-input-block"><div id="merchantpage"class="selectpage"></div></div></div><blockquote class="layui-elem-quote layui-quote-nm">功能说明：<br>1.通过上方选择框选择商户信息。<br>2.商户列表数据受数据授权影响。<br>3.该功能生效范围为除设置模块外的所有模块。</blockquote></div></div>';
    layui.use(['layer'], function() {
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '设置商户',
                skin: 'layui-layer-rim', //加上边框
                area: ['520px', '320px'], //宽高
                offset: '12%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    pop_move(index);//兼容手机弹层
                    var plug = $('#merchantpage').selectpage({
                        url:'/index/service/merchant_list',
                        tip:'全部商户',
                        valid:'merchant',
                        checkbox:true
                    });
                    //获取信息
                    ajax('POST','/index/service/get_merchant',{
                        "by": 'nodcloud.com'
                    },function(resule){
                        plug.selectdata=resule.info;//赋值插件数据
                        plug.render_data();//渲染插件内容
                    },true);
                },
                btn1: function(layero) {
                    //保存
                    ajax('POST','/index/service/set_merchant',{
                        merchant:$('#merchant').val()
                    },function(resule){
                        if(resule.state=='success'){
                            layer.closeAll();
                            dump('设置成功!');
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