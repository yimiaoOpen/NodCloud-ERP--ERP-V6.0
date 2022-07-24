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
                //1.设置页面数据
                var regex1=/PRINT_INITA\((.*?)\){1}/g;//正则表达式
                var exec1=regex1.exec(Value);//执行正则
                var split1=exec1[1].split(",");//拆分数据
                split1[3]='(html_height+'+ cal((split1[3]-0)-(html_height-0)) +')';//赋值数据
                Value=Value.replace(exec1[1],split1.join(","));//替换数据
                //2.设置表格数据
                var regex2=/ADD_PRINT_TABLE\((.*?)\){1}/g;//正则表达式
                var exec2=regex2.exec(Value);//执行正则
                var split2=exec2[1].split(",");//拆分数据
                split2[3]='html_height';//赋值数据
                Value=Value.replace(exec2[1],split2.join(","));//替换数据
                //3.设置表格后数据
                var regex3=/html_table\)\;((?:.|\n|\r\n)*)/g;//正则表达式[表格后续配置数据]
                var exec3=regex3.exec(Value);//执行正则
                var data=exec3[1];//获取表格后续配置数据
                var regex4=/ADD_.*\;{1}/g;//获取所有ADD配置
                var exec4=data.match(regex4);//执行正则
                for (var i = 0; i < exec4.length; i++) {
                    var regex5=/\((.*)\)/g;//获取ADD配置数据
                    var exec5=regex5.exec(exec4[i]);//执行正则
                    var split5=exec5[1].split(",");//拆分数据
                    var difference=cal((split5[0]-0)-(html_height-0));
                    split5[0]='(html_height+'+difference+')';//赋值数据
                    if(exec4[i].indexOf('LINE')>0){
                        split5[2]='(html_height+'+cal((difference-0)+1)+')';//赋值线条第三个数值
                    }
                    Value=Value.replace(exec5[1],split5.join(","));//替换数据
                }
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
//迷你打印
function min_prints(){
    LODOP=getLodop();
    eval(Base64.decode(print_text));
    // LODOP.SET_PREVIEW_WINDOW(0,1,0,266,513,"");//打印前弹出选择打印机的对话框
    LODOP.SET_PRINT_MODE("AUTO_CLOSE_PREWINDOW",1);//打印后自动关闭预览窗口
    LODOP.PREVIEW();
};