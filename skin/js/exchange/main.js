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
    //客户选择事件
    plug['nod_customer'].onselect=function(obj){
        $('#customer_integral').val(obj.integral);
    };
    push_selectpage_plug();//赋值插件内容
    bill_upload();//调用上传插件
    bill_time();//调用日期插件
    set_more($('#more_html').html());//设置扩展字段
    set_bill_more();//填充扩展字段
    set_bill_info();//填充表格数据
});
//保存数据
function save(id){
    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var info = pop_info('.push_data');//获取单据数据
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
                    dump('实际积分不正确!')
                }else if((info.actual-0)>(info.total-0)){
                    dump('实际积分不可大于单据积分!');
                }else if(!reg_test('plus',info.integral)){
                    dump('实收积分不正确!');
                }else if((info.integral-0)>(info.actual-0)){
                    dump('实收积分不可大于实际积分!');
                }else if((info.integral-0)>(info.customer_integral-0)){
                    dump('实收积分不可大于客户积分!');
                }else if(reg_test('empty',info.user)){
                    dump('制单人不可为空!');
                }else{
                    info['id']=id;
                    info['tab']=tab;
                    ajax('POST','/index/exchange/set',info,function(resule){
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
        var tip=type ? '审核后将操作商品库存以及客户积分信息,请再次确定?':'反审核后将反操作库存以及客户积分信息,请再次确定?';
        layer.confirm(tip, {
            btn: ['确定', '取消'],
            offset: '12%'
        }, function() {
            ajax('POST','/index/exchange/auditing',{
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