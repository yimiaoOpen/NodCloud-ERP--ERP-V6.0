var goods_list=[];//初始化商品数据
var room_list=[];//初始化仓储数据
var plug={};//初始化插件数据
$(function(){
    get_goods(1,'');//初始化商品数据
    //监听回车搜索
    $("#so_info").keydown(function() {
        if(event.keyCode == "13") {
            get_goods(1,$("#so_info").val());
            $("#so_info").val('');//清空
        }
    });
    //监听选择商品事件
    $("#room_main").on("click",".info",function(){
        var nod=$(this).attr('nod');
        add_goods(room_list[nod]);//添加商品
    });
    //监听设置商品事件
    $("#goods_main").on("click","tr",function(){
        var nod=$(this).attr('nod');
        set_goods(nod);//设置商品
    });
    //监听删除商品事件
    $("#goods_main").on("click","i",function(e){
        var nod=$(this).parent().parent().attr('nod');
        goods_list.splice(nod,1);
        apply_goods();
        e.stopPropagation();
    });
    //商品详情数据改变事件
    $(".goods_info").on('input propertychange',function(){
        sum_goods(this);
    });
    //商品详情数据改变事件
    $(".settle_info").on('input propertychange',function(){
        sum_settle();
    });
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
    push_selectpage_plug();//赋值插件内容
    set_more($('#more_html').html());//设置扩展字段
});
//搜索数据
function so_goods(){
    get_goods(1,$('#so_info').val());
}
//填充商品搜索数据
//传入页数和搜索内容
function get_goods(page,info){
    var limit=16;//每页显示的条数
    var dom=$('.room_list #room_main');
    //发送请求
    ajax('POST','/index/service/cashier_room_list',{"page": page,"limit": limit,"info": info},function(resule){
        dom.empty();//清空数据
        room_list=resule.data;//转存新数据
        if(resule.data.length>0){
            for(var i = 0; i < resule.data.length; i++) {
                var name=resule.data[i].goodsinfo.name;
                var more=resule.data[i].warehouseinfo.name+' / '+(resule.data[i].attr.name==''?'无':resule.data[i].attr.name);
                var retail=resule.data[i].goodsinfo.retail;
                var html='<div class="layui-col-sm3"><div class="info" nod="'+i+'"><p title="'+name+'">'+name+'</p><div><more title="'+more+'">'+more+'</more><span>'+retail+' 元</span></div></div></div>';
                dom.append(html);
            }
            //判断自动添加数据
            if(resule.count==1 && $('#so_info').val()!=''){
                add_goods(resule.data[0]);
                $('#so_info').focus();
                dump('已自动加入商品列表');
            }
            //调用插件
            layui.use('laypage', function(){
                layui.laypage.render({
                    elem: 'page',
                    count: resule.count,
                    limit: limit,
                    curr: page,
                    jump: function(obj, first){
                        first||(get_goods(obj.curr,$('#so_info').val()));//执行查询
                    }
                });
            });
        }else{
            dump('[ '+info+' ] 未查到数据，换个条件试试？');
        }
    },false);
}
//添加商品
function add_goods(info){
    var repeat=false;
    for (var i = 0; i < goods_list.length; i++) {
        if(goods_list[i].id==info.id){
            repeat=i+1;//+1防止首次循环判断为假问题
            break;
        }
    }
    //判断是否存在重复
    if(repeat){
        //存在
        var arr=goods_list[repeat-1];//获取重复数据
        goods_list[repeat-1].set_nums=(arr.set_nums-0)+1;//增加数量
        goods_list[repeat-1].set_total=cal((arr.set_nums-0)*(arr.set_price-0)*(arr.set_discount-0));//设置总价 数量*单价*折扣
    }else{
        //不存在
        info.set_nums=1;//默认数量
        info.set_price=info.goodsinfo.retail;//默认单价
        info.set_discount=1;//默认折扣
        info.set_total=info.goodsinfo.retail;//默认总价
        info.set_serial=[];//默认串码
        info.data='';//默认备注
        goods_list.push(info);
    }
    apply_goods();
    dump('已加入商品列表');
}
//渲染商品
function apply_goods(){
    var goods_money=0;
    $('#goods_main').empty();
    for (var i = 0; i < goods_list.length; i++) {
        goods_money+=goods_list[i].set_total-0;//累加金额
        var html='<tr nod="'+i+'"><td>'+goods_list[i].goodsinfo.name+'</td><td>'+goods_list[i].set_nums+'</td><td class="total">'+goods_list[i].set_total+'</td><td><i class="layui-icon layui-icon-delete"></i></td></tr>';
        $('#goods_main').append(html);
    }
    $('#goods_count').html(goods_list.length);
    $('#goods_money').html(goods_money);
}
//设置商品
function set_goods(index){
    var info=goods_list[index];//获取数据
    //通用赋值数据
    $('.goods_info input[source],.goods_info select[source]').each(function(){
        var val='';//初始化默认值
        source_split=$(this).attr('source').split('|');
        for (var n = 0; n < source_split.length; n++) {
            if(n==0){
                //首次循环-读取info数据
                if(info.hasOwnProperty(source_split[n]) && info[source_split[n]]!=null) {
                    val=info[source_split[n]];
                }else{
                    val='';
                    break;
                }
            }else{
                //非首次循环-读取val数据
                if(val.hasOwnProperty(source_split[n]) && val[source_split[n]]!=null) {
                    val=val[source_split[n]];
                }else{
                    val='';
                    break;
                }
            }
        }
        $(this).val(val);
    });
    //设置串码
    $('#set_serial').empty();
    if(reg_test('empty',info['serialinfo'])){
        $('#set_serial').select2({width:'100%',placeholder: "无需选择"});//赋值
    }else{
        var serial=info['serialinfo'].split(',');//分割数据
        //填充数据
        for (var i = 0; i < serial.length; i++) {
            $('#set_serial').append('<option value="'+serial[i]+'">'+serial[i]+'</option>');
        }
        $('#set_serial').val(info.set_serial).select2({width:'100%',placeholder: "请选择串码"}).on("change",function(e){
            var serial_val=$(this).select2('val');
            if(serial_val!=null){
                $('#set_nums').val(serial_val.length);
                sum_goods();
            }
        });//赋值监听
    }
    $('.main_box .layui-col-xs9').hide();
    $('.goods_info').show();//显示详情
    $('.goods_info').attr('nod',index);//转存INDEX
}
//隐藏商品详情
function hide_goods_info(){
    $('.main_box .layui-col-xs9').hide();
    $('.room_list').show();
}
//计算商品数据
function sum_goods(dom){
    var index = $('.goods_info').attr('nod');
    var info=goods_list[index];//获取数据
    var set_serial = $('#set_serial').select2('val');
    var set_nums = $('#set_nums').val();
    var set_price = $('#set_price').val();
    var set_discount = $('#set_discount').val();
    var data = $('#data').val();
    //判断数据合法性
    if(!reg_test('empty',info.serialinfo) && set_serial.length==0){
        dump('请先选择串码!');
    }else if(!reg_test('empty',info.serialinfo) && set_serial.length!=(set_nums-0)){
        dump('串码个数与商品数量不符，请核实!');
    }else if(!reg_test('plus',set_nums) || set_nums=='0'){
        dump('数量不正确!');
    }else if(!reg_test('plus',set_price)){
        dump('零售单价不正确!');
    }else if(!reg_test('plus',set_discount) || (set_discount-0) == 0 || (set_discount-0) > 1){
        dump('折扣不正确!');
    }else{
        info.set_nums=set_nums;//更新数量
        info.set_price=set_price;//更新单价
        info.set_discount=set_discount;//更新折扣
        info.data=data;//更新备注
        var set_total=cal((set_nums-0)*(set_price-0)*(set_discount-0));//计算金额
        info.set_total=set_total;//更新价格
        if(set_serial.length!=0){
            info.set_serial=set_serial;//更新串码
        }
        $('#set_total').val(set_total);//更新价格
        //兼容扩展字段
        var more={};
        $('.goods_info [id^="more_"]').each(function(){
            var id_split=$(this).attr('id').split("_");
            more[id_split[1]]=$(this).val();
        });
        $.isEmptyObject(more)||(info['more']=more);
        apply_goods();
    }
    
}
//结账
function settle(){
    if($(".settle_info").is(":hidden")){
        //赋值页面数据
        if(goods_list.length==0){
            dump('您还未选择商品数据!');
        }else{
            var total=0;//初始化单据金额
            //数据合法检验
            for (var i = 0; i < goods_list.length; i++) {
                var info=goods_list[i];//获取数据
                if(!reg_test('empty',info.serialinfo) && info.set_serial.length==0){
                    dump('请先选择串码!');
                    set_goods(i);//显示详情数据
                    return false;
                }else if(!reg_test('empty',info.serialinfo) && info.set_serial.length!=(info.set_nums-0)){
                    dump('串码个数与商品数量不符，请核实!');
                    set_goods(i);//显示详情数据
                    return false;
                }else if(!reg_test('plus',info.set_nums) || info.set_nums=='0'){
                    dump('数量不正确!');
                    set_goods(i);//显示详情数据
                    return false;
                }else if(!reg_test('plus',info.set_price)){
                    dump('零售单价不正确!');
                    set_goods(i);//显示详情数据
                    return false;
                }else if(!reg_test('plus',info.set_discount) || (info.set_discount-0) == 0 || (info.set_discount-0) > 1){
                    dump('折扣不正确!');
                    set_goods(i);//显示详情数据
                    return false;
                }else{
                    total=cal((total-0)+(info.set_total-0));//累加金额
                }
            }
            layui.use('form', function() {
                var option='';
                for (var i = 0; i < account_arr.length; i++) {
                    option+='<option value="'+account_arr[i].id+'">'+account_arr[i].name+'</option>';
                }
                $('#pay_tbody').append('<tr><td><select lay-ignore>'+option+'</select></td><td><input type="text" placeholder="请输入结算金额"/></td><td onclick="del_pay(this);"><i class="layui-icon layui-icon-delete"></i></td></tr>');
                $('#total,#actual').val(total);//赋值金额
                $('#integral').val(cal((total-0)*(integral_proportion-0)));//赋值积分
                //监听选择
                layui.form.on('switch(paymemu)', function(switch_data){
                    if(switch_data.elem.checked){
                        $('#money').val('');
                        $('div[nod="account"]').parent().parent().hide();
                        $('#pay_tab').show();
                        $('#money').attr("disabled",true);//禁用
                        $('#pay_tbody tr:gt(0)').remove();//删除多余组合支付
                        $('#pay_tbody input').val('');//初始化结算金额
                    }else{
                        $('#money').val('');
                        $('div[nod="account"]').parent().parent().show();
                        $('#pay_tab').hide();
                        $('#money').attr("disabled",false);//解除禁用
                    }
                });
            });
            $('.main_box .layui-col-xs9').hide();
            $('.settle_info').show();
        }
    }else{
        push_cashier();//提交数据
    }
}
//隐藏结账信息
function hide_settle_info(){
    $('.main_box .layui-col-xs9').hide();
    $('#goods_list').show();
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
//结账信息处理
function sum_settle(){
    var total = $('#total').val();
    var actual = $('#actual').val();
    var integral=$('#integral').val();
    if(!reg_test('plus',actual)){
        dump('实际金额不正确!');
        return false;
    }else if((actual-0)>(total-0)){
        dump('实际价格不可大于单据金额!');
        return false;
    }else{
        //处理支付信息
        if($('#paymemu').is(':checked')){
            //组合支付
            var pay_money = 0; 
            $('#pay_tbody input').each(function(){
                var val = $(this).val();
                if(!reg_test('empty',val)){
                    if(reg_test('plus',val)){
                        pay_money=cal((pay_money-0)+(val-0));
                        if((pay_money-0)>(actual-0)){
                            dump('组合支付总金额不可超过实际价格!');
                            return false;
                        }
                    }else{
                        dump('组合支付第'+($(this).parent().parent().index()+1)+"行结算金额不正确!");
                        $('#money').val('0');
                        return false;
                    }
                }
            });
            $('#money').val(pay_money);
        }
        //判断客户付款
        var money = $('#money').val();
        if(!reg_test('plus',money)){
            dump('客户付款金额不正确!');
            return false;
        }else{
            //计算找零金额
            $('#oddchange').val(0).css('color','#000');
            if((money-0)>(actual-0)){
                $('#oddchange').val(cal((money-0)-(actual-0))).css('color','#f00');
            }
        }
    }
    if(!reg_test('plus',integral)){
        dump('赠送积分不正确');
        return false;
    }
    return true;
}
//提交数据
function push_cashier(){
    var check=sum_settle();
    if(check){
        var info=pop_info('.settle_info');
        info.paytype=$('#paymemu').is(':checked')?1:0;//赋值付款方式
        $('#paymemu').is(':checked')&&(info.account=0);//如组合支付初始化支付账户
        info.payinfo=get_payinfo();//获取组合支付信息
        var tab=[];//初始化商品数据
        //构造新数据
        for (var i = 0; i < goods_list.length; i++) {
            var obj={};
            obj.room=goods_list[i].id;
            obj.goods=goods_list[i].goods;
            obj.warehouse=goods_list[i].warehouse;
            obj.serial=goods_list[i].set_serial.join(',');
            obj.nums=goods_list[i].set_nums;
            obj.price=goods_list[i].set_price;
            obj.discount=goods_list[i].set_discount;
            obj.total=goods_list[i].set_total;
            obj.data=goods_list[i].data;
            (goods_list[i].hasOwnProperty('more')&&!$.isEmptyObject(goods_list[i].more))&&(obj['more']=goods_list[i].more);//加入扩展属性
            tab.push(obj);
        }
        info.tab=tab;//赋值商品数据
        //合法性判断
        if(reg_test('empty',info.customer)){
            dump('您还未选择购买客户!');
        }else if((info.money-0)<(info.actual-0)){
            dump('客户付款不可小于实际金额');
        }else if(info.paytype==0 && reg_test('empty',info.account)){
            dump('您还未选择结算账户');
        }else{
            //提交数据
            info.id=0;
            info.money=info.actual;
            ajax('POST','/index/cashier/set',info,function(resule){
                if(resule.state=='success'){
                    //判断自动打印小票
                    if(cashier_print==1){
                        layui.use('form', function(){
                            layer.open({
                                type: 2,
                                title: '小票打印',
                                offset: '9%',
                                area: ['650px', '330px'],
                                shadeClose: true,
                                end:function(){
                                    jump_info('保存成功!');
                                },
                                content: '/index/cashier/min_prints?auto=true&id='+resule.info
                            }); 
                        }); 
                    }else{
                        jump_info('保存成功!');
                    }
                    
                    
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        }
    }
}

