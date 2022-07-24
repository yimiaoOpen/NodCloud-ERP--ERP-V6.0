//打印配置
var LODOP;
//打印报表
function prints(){
    LODOP=getLodop();
    eval(Base64.decode(print_text));
    LODOP.PREVIEW();
};
//修改模板
function edit(){
    LODOP=getLodop();
    eval(Base64.decode(print_text));
    LODOP.SET_SHOW_MODE("HIDE_PBUTTIN_SETUP",true);
    LODOP.SET_SHOW_MODE("HIDE_ABUTTIN_SETUP",true);
    if (LODOP.CVERSION) CLODOP.On_Return=function(TaskID,Value){
        layui.use('layer', function() {
            layer.confirm('是否启用新模板？', {
                btn: ['启用', '取消'], //按钮
                offset: '12%'
            }, function() {
                print_text=Base64.encode(Value);//赋值新模板
                ajax('POST','/index/prints/set',{
                    "name": print_name,
                    "type": paper_type,
                    "main":print_text
                },function(resule){
                    if(resule.state=='success'){
                        dump('模板保存成功');
                    }else if(resule.state=='error'){
                        dump(resule.info);
                    }else{
                        dump('[ Error ] 服务器返回数据错误!');
                    }
                },true);
            });
        });
    };
    LODOP.PRINT_DESIGN();
};
//恢复默认
function recovery(){
    layui.use('layer', function() {
        layer.confirm('您确定要恢复默认模板？', {
            btn: ['确定', '取消'], //按钮
            offset: '12%'
        }, function() {
            ajax('POST','/index/prints/recovery',{
                "name": print_name,
                "type": paper_type
            },function(resule){
                if(resule.state=='success'){
                    jump_info('恢复默认模板成功');
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}