//初始化
$(function(){
    //赋值CSS过滤路径
    for (var i = 0; i < nod_css.length; i++) {
        $('#exclude_css').append(nod_css[i]+"\n");
    }
    //赋值JS过滤路径
    for (var s = 0; s < nod_js.length; s++) {
        reg_test('empty',nod_js[s])||($('#exclude_js').append(nod_js[s]+"\n"));
    }
});
//保存数据
function save(){
    var cache_time=$('#cache_time').val();
    var exclude_css=$.grep($('#exclude_css').val().split("\n"),function(nod){
        return (!reg_test('empty',nod));  
    });
    var exclude_js=$.grep($('#exclude_js').val().split("\n"),function(nod){
        return (!reg_test('empty',nod));  
    });
    if(!reg_test('plus',cache_time) || cache_time=='0'){
        dump('缓存有效期不正确!');
    }else{
        ajax('POST','/index/plug/more?plug_info=speed/main/save',{
            "cache_time": cache_time,
            "exclude_css": exclude_css,
            "exclude_js": exclude_js
        },function(resule){
            if(resule.state=='success'){
                dump('参数保存成功!');
            }else if(resule.state=='error'){
                dump(resule.info);
            }else{
                dump('[ Error ] 服务器返回数据错误!');
            }
        },true);
    }
}