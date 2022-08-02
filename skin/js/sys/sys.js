layui.use(['element','layer','form'], function(){
    var layer = layui.layer;
    var form = layui.form;
    //监听提示
    $('body *[nod-tips]').on({
        mouseenter:function(){
            layer.tips($(this).attr('nod-tips'), this);
        },
        mouseleave:function(){
            layer.closeAll('tips');//关闭所有的tips层  
        }
    });
    //初始化数据
    for (var name in sys) {
        $('#'+name).length==1&&($('#'+name).val(sys[name]));//赋值数据
    }
    $('#cashier_customer_page').selectpage({
        url:'/index/service/customer_list',
        tip:'无',
        valid:'cashier_customer',
        selectdata:cashier_customer_selectdata,
        push:{'notauth':true}
    });
    $('#cashier_account_page').selectpage({
        url:'/index/service/account_list',
        tip:'无',
        valid:'cashier_account',
        selectdata:cashier_account_selectdata,
        push:{'notauth':true}
    });
    
});
//保存数据
function save(){
    //获取页面数据
    var info={};
    $('.layui-tab-content input[id],select[id],textarea[id]').each(function(){
        info[$(this).attr('id')]=$(this).val();
    });
    //判断数据
    if(reg_test('empty',info['sys_name'])){
        dump('系统名称不正确!');
    }else if(!reg_test('plus',info['room_threshold'])){
        dump('库存默认阀值不正确!');
    }else if(reg_test('empty',info['cashier_title'])){
        dump('零售标题不正确!');
    }else if(!reg_test('integer',info['integral_proportion'])){
        dump('积分比例不正确!');
    }else if(!reg_test('integer',info['form_day'])){
        dump('图表天数不正确!');
    }else{
        ajax('POST','/index/sys/save',info,function(resule){
            dump('保存成功');
        },true);
    }
}

