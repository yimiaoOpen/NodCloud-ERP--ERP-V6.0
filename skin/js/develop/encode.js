//初始化FORM
layui.use('form');
function compress(){
    var info = search_info('obj');
    var code = $('#code').val();
    if(reg_test('empty',code)){
        dump('您还没输入需要压缩的代码');
    }else{
        info['code']=$('#code').val();
        ajax('POST','/index/develop/compress',info,function(resule){
            if(resule.state=='success'){
                $('#info').val(resule.code);
            }else if(resule.state=='error'){
                dump(resule.info);
            }else{
                dump('[ Error ] 服务器返回数据错误!');
            }
        },true);
    }
}
