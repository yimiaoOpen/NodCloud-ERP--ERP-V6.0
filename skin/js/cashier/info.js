var plug={};
$(function(){
    //调用插件
    $('.selectpage').each(function(){
        plug[$(this).attr('id')]=$(this).selectpage({
            url:$(this).attr('url'),
            tip:$(this).attr('tip'),
            valid:$(this).attr('nod'),
            checkbox:$(this).is('[checkbox]')?true:false,
            disabled:$(this).is('[disabled]')?true:false
        });
    });
    //监听数据改变
    $('#actual').bind('input propertychange', function() {
        var actual = $('#actual').val();
        $('#money').val(actual);
        $('#integral').val(cal((actual-0)*(sys.integral_proportion-0)));//赋值赠送积分
    });
    //监听组合支付
    layui.use('form', function(){
        var form = layui.form;
        form.on('switch(paymemu)', function(data){
            if(data.elem.checked){
                //开启
                show_pays();
            }else{
                //关闭
                payinfo=[];//初始化数据
                dump('已清空组合支付数据!');
            }
        });
    });
    push_selectpage_plug();//赋值插件内容
    bill_upload();//调用上传插件
    bill_time();//调用日期插件
    set_more($('#more_html').html());//设置扩展字段
    set_bill_more();//填充扩展字段
    set_bill_info();//填充表格数据
});
//快捷录入金额
function get_totals(){
    var total=$('#total').val();
    $('#actual').val(total);
    $('#money').val(total);
    $('#integral').val(cal((total-0)*(sys.integral_proportion-0)));//赋值赠送积分
    dump('已快捷录入!');
}
//显示组合支付信息
function show_pays(){
    if($('#paymemu').is(':checked')){
        var html = '<div class="pop_box"><table class="layui-table"id="pay_tab"style="margin: 0;"><thead><tr><th>结算账户</th><th>结算金额</th><th onclick="add_pay();">相关操作<i class="layui-icon layui-icon-add-circle"></i></th></tr></thead><tbody id="pay_tbody"></tbody></table></div>';
        layui.use('layer', function() {
            layer.ready(function() {
                layer.open({
                    id:'pop_main',
                    type: 1,
                    title: '组合支付详情',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['630px', '350px'], //宽高
                    offset: '6%',
                    content: html,
                    btn: ['保存', '取消'],
                    fixed: false,
                    shadeClose: true,
                    success: function(layero, index) {
                        var option='';
                        for (var i = 0; i < account_arr.length; i++) {
                         option+='<option value="'+account_arr[i].id+'">'+account_arr[i].name+'</option>';
                        }
                        $('#pay_tbody').append('<tr><td><select lay-ignore>'+option+'</select></td><td><input type="text" placeholder="请输入结算金额"/></td><td onclick="del_pay(this);"><i class="layui-icon layui-icon-delete"></i></td></tr>');
                        //初始化数据
                        for (var i = 0; i < payinfo.length; i++) {
                            i!=0&&(add_pay());
                            var tr_dom=$('#pay_tbody tr').eq(i);
                            tr_dom.find('select').val(payinfo[i].account);
                            tr_dom.find('input').val(payinfo[i].money);
                        }
                    },
                    btn1: function(layero) {
                        //保存
                        if(check_payinfo()){
                            payinfo=get_payinfo();//转存数据
                            layer.closeAll();//关闭层
                        }
                    }
                });
            });
        });
    }else{
        dump('您还未启用组合支付,该操作无效!');
    }
}

//增加组合支付
function add_pay(){
    var tr = $('#pay_tbody tr');
    if(tr.length==3){
        dump('组合支付最多支持三种方式');
    }else{
        $('#pay_tbody').append('<tr>'+tr.eq(0).html()+'</tr>');
    }
}
//删除组合支付
function del_pay(dom){
    var tr = $('#pay_tbody tr');
    if(tr.length!=1){
        $(dom).parent().remove();
    }
}
//检查组合支付数据
function check_payinfo(){
    var actual = $('#actual').val();
    var pay_money = 0; 
    $('#pay_tbody input').each(function(){
        var val = $(this).val();
        if(!reg_test('empty',val)){
            if(reg_test('plus',val)){
                pay_money=cal((pay_money-0)+(val-0));
                if((pay_money-0)>(actual-0)){
                    dump('组合支付总金额不可超过实际价格!');
                    return false();
                }
            }else{
                dump('组合支付第'+($(this).parent().parent().index()+1)+"行结算金额不正确!");
                return false();
            }
        }
    });
    if((pay_money-0)==(actual-0)){
        return true;
    }else{
        dump('组合支付总金额与实际金额不符!');
        return false;
    }
}
//获取组合支付数据
function get_payinfo(){
    var arr = [];
    $('#pay_tbody tr').each(function(){
        var money = $(this).find('input').val();
        if(!reg_test('empty',money) && reg_test('plus',money) && (money-0)!=0){
            var obj={};
            obj['account']=$(this).find('select').val();
            obj['money'] = money;
            arr.push(obj);
        }
    })
    return arr;
}

//保存数据
function save(id){
    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var info = pop_info('.push_data');//获取单据数据
    info.paytype=$('#paymemu').is(':checked')?1:0;//赋值付款方式
    if(reg_test('empty',info.customer)){
        dump('客户不可为空!');
    }else if(!reg_test('time',info.time)){
        dump('单据日期不正确!');
    }else if(reg_test('empty',info.number)){
        dump('单据编号不可为空!');
    }else{
        //判断表格合法性
        if(check_tab()){
            var tab = tab_info({
                set_id:'room',
                goods_id:'goods',
                warehouse_id:'warehouse'
            });//获取表格数据
            if(tab.length==0){
                dump('数据表格内容不可为空!');
            }else{
                if(!reg_test('plus',info.actual)){
                    dump('实际金额不正确!')
                }else if((info.actual-0)>(info.total-0)){
                    dump('实际金额不可大于单据金额!');
                }else if(reg_test('empty',info.user)){
                    dump('制单人不可为空!');
                }else if(info.paytype==0 && reg_test('empty',info.account)){
                    dump('结算账户不可为空!');
                }else if(!reg_test('plus',info.integral)){
                    dump('赠送积分不正确!')
                }else{
                    info['id']=id;
                    info['tab']=tab;
                    info['payinfo']=payinfo;//获取组合支付数据
                    //判断组合判断
                    if(info.paytype==1){
                        var pay_money=0;//初始化组合支付总金额
                        for (var i = 0; i < payinfo.length; i++) {
                            pay_money=(pay_money-0)+(payinfo[i].money-0)
                        }
                        if((pay_money-0)!=(info.actual-0)){
                            show_pays();
                            dump('组合支付数据不正确!');
                            return false
                        }
                    }
                    ajax('POST','/index/cashier/set',info,function(resule){
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
        }
    }
}
//审核操作
function auditing(id,type){
    layui.use('layer', function() {
        var tip=type ? '审核后将操作商品库存以及资金账户,请再次确定?':'反审核后将反操作库存以及资金账户,请再次确定?';
        layer.confirm(tip, {
            btn: ['确定', '取消'],
            offset: '12%'
        }, function() {
            ajax('POST','/index/cashier/auditing',{
                'arr':[id]
            },function(resule){
                if(resule.state=='success'){
                    jump_info('操作成功!');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}