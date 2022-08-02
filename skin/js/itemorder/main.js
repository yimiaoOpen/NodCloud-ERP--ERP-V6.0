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
                set_id:'serve',
            });//获取表格数据
            if(tab.length==0){
                dump('数据表格内容不可为空!');
            }else{
                if(!reg_test('plus',info.actual)){
                    dump('实际金额不正确!')
                }else if((info.actual-0)>(info.total-0)){
                    dump('实际金额不可大于单据金额!');
                }else if(!reg_test('plus',info.money)){
                    dump('实收金额不正确!');
                }else if((info.money-0)>(info.actual-0)){
                    dump('实收金额不可大于实际金额!');
                }else if(reg_test('empty',info.user)){
                    dump('制单人不可为空!');
                }else if(reg_test('empty',info.account)){
                    dump('结算账户不可为空!');
                }else{
                    info['id']=id;
                    info['tab']=tab;
                    ajax('POST','/index/itemorder/set',info,function(resule){
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
        var tip=type ? '审核后将操作资金账户,请再次确定?':'反审核后将反操作资金账户,请再次确定?';
        layer.confirm(tip, {
            btn: ['确定', '取消'],
            offset: '12%'
        }, function() {
            ajax('POST','/index/itemorder/auditing',{
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