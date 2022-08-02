layui.use('form', function(){
    var form = layui.form;
    ajax('POST','/index/often/often_list',{'by':'nodcloud.com'},function(resule){
        if(resule.state=='success'){
            for (var i = 0; i < resule.info.length; i++) {
                var html='<input type="checkbox" title="'+resule.info[i].name+'" nod="'+resule.info[i].url+'" root="'+resule.info[i].root+'"';
                html+=resule.info[i].checked==true? ' checked>':'>';
                $('#often_box').append(html);
            }
            form.render(); //重新渲染
        }else if(resule.state=='error'){
            dump(resule.info);
        }else{
            dump('[ Error ] 服务器返回数据错误!');
        }
    },true);
});
//保存数据
function save(){
    var arr=[];
    $('input[type="checkbox"]').each(function(){
        if($(this).prop('checked')){
            arr.push({
                'name':$(this).attr('title'),
                'set':$(this).attr('nod'),
                'root':$(this).attr('root')
            });
        }
    });
    ajax('POST','/index/often/set_often',{
        arr:JSON.stringify(arr)//编码兼容空数组
    },function(resule){
        if(resule.state=='success'){
            dump('保存成功');
        }else if(resule.state=='error'){
            dump(resule.info);
        }else{
            dump('[ Error ] 服务器返回数据错误!');
        }
    },true);
}