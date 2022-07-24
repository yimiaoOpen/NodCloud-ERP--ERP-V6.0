var form_arr;
var form_nums;
//升级操作
function run(){
    layui.use('layer', function() {
        layer.confirm('您确定要进行该操作吗？', {
            btn: ['确定', '取消'], //按钮
            offset: '12%',
            shadeClose: true
        }, function() {
            layer.closeAll();
            $('.fun_info').hide();
            $('.fun_box').show();
            $.ajax({
                type: 'POST',
                async: false,
                url: '/index/service/summary_forms',
                data: {'by':'nodcloud.com'},
            	dataType: "json",
                success: function(resule){
                    form_arr=resule;
                    form_nums=resule.length;
                    start();
                }
            });
        });
    });
}
//初始化过程
function start(){
    layui.use('element', function() {
        var element = layui.element;
        var arr=[];
        set_textarea('初始化数据结构，即将开始更新。');
        for (var i = 0; i < form_arr.length; i++) {
            var ceil = Math.ceil(form_arr[i][2] / 30);
            for (var e = 0; e < ceil; e++) {
                arr.push({
                    'name':form_arr[i][0],//当前报表名称
                    'form':form_arr[i][1],//当前报表标识
                    'classcur':i+1,//当前报表标识
                    'infocur':e+1,//当前详情标识
                });
            }
        }
        var nums=0;
        var nod = setInterval(function(){
            if(nums==arr.length){
                clearInterval(nod);
                set_textarea(' :) 报表数据更新完成。');
                $('.layui-progress-bar').removeClass('layui-bg-green');
            }else{
                $.post("/index/service/cal_summary", arr[nums], function(resule) {
                    element.progress('nod', cal((100/arr.length)*nums)+'%');//更新进度
                    set_textarea('共需更新'+form_nums+'个报表,当前第'+(resule.classcur)+'个['+resule.name+']['+resule.start+'-'+resule.end+']。');
                }).fail(function() {
                    clearInterval(nod);
                    set_textarea(' :( 服务器返回错误处理错误，请重新执行初始化操作。');
                });
                nums++;
            }
        }, 5130);
    });
    
}
//加入数据
function set_textarea(val){
    var str = $('#run_info').val(); //先获取原有的值
    var time = new Date(new Date().getTime()).toLocaleString();//获取当前时间
    $('#run_info').val(time+' - '+val+'\n'+str); //拼接新值
}