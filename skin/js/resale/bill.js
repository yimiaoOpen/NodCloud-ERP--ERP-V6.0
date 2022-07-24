layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/resale/bill_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
        done: function(res, curr, count){
            table_sum('#data_table',[
                {'text':'单据总金额','key':'total'},
                {'text':'实际总金额','key':'actual'},
                {'text':'实付总金额','key':'money'}
            ]);
        }
    });//渲染表格
    //监听工具条事件
    table.on('tool(table_main)', function(obj){
        var data = obj.data;
        var event = obj.event;
        if(event == 'view'){
            view(data.id);//详情
        }else if(event == 'set_bill'){
            set_bill(data);//操作
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
    //调用插件
    $('.selectpage').each(function(){
        $(this).selectpage({
            url:$(this).attr('url'),
            tip:$(this).attr('tip'),
            valid:$(this).attr('nod'),
            checkbox:$(this).is('[checkbox]')?true:false,
            disabled:$(this).is('[disabled]')?true:false
        });
    });
    form_time();//调用日期插件
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
function view(id){
    layui.use('layer', function() {
        layer.open({
            type: 2,
            title: '详情',
            offset: '1%',
            fixed: false,
            area: ['99%', '98%'],
            shadeClose: true,
            content: '/index/resale/info?id='+id,
            end:function(){
                search();
            }
        });
    });
}
//导出操作
function exports(){
    var info=search_info('url');
    jump_info('【 数据请求中 】',"/index/resale/bill_export?"+info,true);
}
//操作对账单
function set_bill(data) {
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane layui-row layui-col-space3"><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">未结算金额</label><div class="layui-input-block"><input type="text"class="layui-input difference"disabled></div></div></div><div class="layui-col-md12"><p style="padding-left: 6px;">核销操作</p><hr/><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">结算账户</label><div class="layui-input-block"><div id="pop_account"class="selectpage"></div></div></div></div><div class="layui-col-md6"><div class="layui-form-item three_layout"><label class="layui-form-label">结算金额</label><div class="layui-input-block"><input type="text"id="money"placeholder="请输入结算金额"class="layui-input"><label class="layui-form-label again"onclick="add_bill('+data.id+');">添加</label></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div><table class="layui-table tab_center"><thead><tr><th width="25%">操作时间</th><th width="15%">结算账户</th><th width="15%">结算金额</th><th width="15%">制单人</th><th width="20%">备注信息</th><th width="10%">操作</th></tr></thead><tbody id="bill_info"></tbody></table></div></div></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area:['720px', '460px'],
                offset: '6%',
                content: html,
                btn: ['强制核销', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    //字段赋值
                    $('.difference').val(cal((data.actual-0)-(data.money-0)));
                    //调用插件
                    $('#pop_account').selectpage({
                        url:'/index/service/account_list',
                        tip:'请选择结算账户',
                        valid:'account'
                    });
                    //获取信息
                    ajax('POST','/index/resale/bill_info',{
                        "id": data.id
                    },function(resule){
                        for (var i = 0; i < resule.length; i++) {
                            $('#bill_info').append('<tr><td>'+resule[i].time+'</td><td>'+resule[i].accountinfo.name+'</td><td>'+resule[i].money+'</td><td>'+resule[i].userinfo.name+'</td><td>'+resule[i].data+'</td><td><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_bill(this,'+resule[i].id+')">删除</button></td></tr>');
                        }
                    },true);
                },
                btn1: function(layero) {
                    if(data.actual==data.money){
                        dump('已核销单据不可执行该操作!');
                    }else{
                        layer.confirm('操作说明:<br/>1.核销状态将改为强制核销。<br/>2.强制核销等同于已核销状态。<br/>3.请再次确认是否执行该操作。', {
                            btn: ['确认', '取消'], //按钮
                            offset: '6%'
                        }, function() {
                            //发送请求
                            ajax('POST','/index/resale/force_bill',{
                                "id": data.id
                            },function(resule){
                                if(resule.state=='success'){
                                    layer.closeAll();//关闭层
                                    dump('强制核销成功!');
                                }else if(resule.state=='error'){
                                    dump(resule.info);
                                }else{
                                    dump('[ Error ] 服务器返回数据错误!');
                                }
                            },true);
                        });
                    }
                    
                },
                end: function() {
                    search();
                }
            });
        });
    });
}
//添加核销信息
function add_bill(id){
    var difference=$('.difference').val();
    var account =$('#account').val();
    var money =$('#money').val();
    var data =$('#data').val();
    if(reg_test('empty',account)){
        dump('结算账户不可为空!');
    }else if(!reg_test('plus',money) || money=='0'){
        dump('结算金额不正确!');
    }else if((money-0)>(difference-0)){
        dump('结算金额不可超出未结算金额!');
    }else{
        //发送请求
        ajax('POST','/index/resale/add_bill',{
            "pid": id,
            "account": account,
            "money": money,
            "data": data
        },function(resule){
            if(resule.state=='success'){
                $('#money').val('');
                $('#data').val('');
                $('.difference').val(cal((difference-0)-(money-0)));
                $('#bill_info').prepend('<tr><td>'+resule.info.time+'</td><td>'+resule.info.accountinfo.name+'</td><td>'+resule.info.money+'</td><td>'+resule.info.userinfo.name+'</td><td>'+resule.info.data+'</td><td><button class="layui-btn layui-btn-primary layui-btn-sm" onclick="del_bill(this,'+resule.info.id+')">删除</button></td></tr>');
                dump('添加成功!');
            }else if(resule.state=='error'){
                dump(resule.info);
            }else{
                dump('[ Error ] 服务器返回数据错误!');
            }
        },true);
    }
}
//添加核销信息
function del_bill(dom,id){
    var difference=$('.difference').val();
    var money=$(dom).parent().parent().find('td').eq(2).html();
    layui.use('layer', function() {
        layer.confirm('结算资金将返还结算账户,您确定吗?', {
            btn: ['确定', '取消'], //按钮
            offset: '6%'
        }, function() {
            //发送请求
            ajax('POST','/index/resale/del_bill',{
                "id": id
            },function(resule){
                if(resule.state=='success'){
                    $('.difference').val(cal((difference-0)+(money-0)));
                    $(dom).parent().parent().remove();
                    dump('删除成功!');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}
//批量核销
function set_bills(){
    var arr=[];//初始化数据
    var difference = 0;//未结算总金额
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane layui-row layui-col-space3"><div class="layui-col-md12"><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">单据总数量</label><div class="layui-input-block"><input type="text"class="layui-input pop_nums"disabled></div></div></div><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">未结算总额</label><div class="layui-input-block"><input type="text"class="layui-input pop_difference"disabled></div></div></div></div><div class="layui-col-md12"><p style="padding-left: 6px;">批量核销操作</p><hr/><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">结算账户</label><div class="layui-input-block"><div id="pop_account"class="selectpage"></div></div></div></div><div class="layui-col-md6"><div class="layui-form-item"><label class="layui-form-label">结算金额</label><div class="layui-input-block"><input type="text"id="money"placeholder="请输入结算金额"class="layui-input"></div></div></div><div class="layui-col-md12"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"id="data"placeholder="请输入备注信息"class="layui-input"></div></div></div></div><div class="layui-col-md12"><fieldset class="layui-elem-field"><legend style="font-size: 16px;">操作说明</legend><div class="layui-field-box"><ul class="ul_li_line"><li>1.该操作可批量对单据进行核销操作。</li><li>2.将按照单据先后顺序依次进行处理。</li><li>3.结算金额将按照单据自动拆分计算。</li><li>4.备注信息将写入资金账户详情列表。</li><li>5.该操作不可逆，请您仔细核对数据。</li></ul></div></fieldset></div></div></div>';
    layui.use(['layer','table'], function() {
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '批量操作',
                skin: 'layui-layer-rim', //加上边框
                area:['720px', '506px'],
                offset: '6%',
                content: html,
                btn: ['批量核销', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    //表格数据
                    var nod = layui.table.checkStatus('data_table');//获取选中数据
                    for (var i = 0; i < nod.data.length; i++) {
                        //匹配核销类型
                        if($.inArray(nod.data[i].billtype.nod,[0,1])>-1){
                            arr.push(nod.data[i].id);//加入数据ID
                            difference=cal((nod.data[i].actual-0)-(nod.data[i].money-0)+(difference-0));//累加未结算金额
                        }
                    }
                    //字段赋值
                    $('.pop_nums').val(arr.length);
                    $('.pop_difference').val(difference);
                    //调用插件
                    $('#pop_account').selectpage({
                        url:'/index/service/account_list',
                        tip:'请选择结算账户',
                        valid:'account'
                    });
                },
                btn1: function(layero) {
                    var account =$('#account').val();
                    var money =$('#money').val();
                    var data =$('#data').val();
                    if(arr.length==0){
                        dump('未查找到符合条件的单据!');
                    }else if(reg_test('empty',account)){
                        dump('结算账户不可为空!');
                    }else if(!reg_test('plus',money) || money=='0'){
                        dump('结算金额不正确!');
                    }else if((money-0)>(difference-0)){
                        dump('结算金额不可超出未结算总额!');
                    }else{
                        layer.confirm('提交数据后不可批量逆操作,请再次确认是否操作?', {
                            btn: ['确认', '取消'], //按钮
                            offset: '6%'
                        }, function() {
                            //发送请求
                            ajax('POST','/index/resale/set_bills',{
                                "arr": arr,
                                "account": account,
                                "money": money,
                                "data": data
                            },function(resule){
                                if(resule.state=='success'){
                                    layer.open({
                                        title:'反馈信息',
                                        offset: '6%',
                                        content:'本次实际核销[ '+(resule.info.length)+' ]条单据，单据编号为[ '+resule.info.join(' | ')+' ]。',
                                        btn1: function(layero) {
                                            layer.closeAll();//关闭层
                                            $('.btn_group_right button[batch]').remove();//初始化-批量操作
                                        }
                                    });
                                }else if(resule.state=='error'){
                                    dump(resule.info);
                                }else{
                                    dump('[ Error ] 服务器返回数据错误!');
                                }
                            },true);
                        });
                        
                    }
                },
                end: function() {
                    search();
                }
            });
        });
    });
}