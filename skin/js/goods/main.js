layui.use('table', function() {
    var table=layui.table;
    table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        even: true,
        cols:  [formfield],
        url: '/index/goods/goods_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
    });//渲染表格
    //监听工具条事件
    table.on('tool(table_main)', function(obj){
        var data = obj.data;
        var event = obj.event;
        if(event == 'edit'){
            detail(data.id);//修改
        }else if(event == 'delect'){
            delect(data.id);//常规删除
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
    $.fn.zTree.init($("#s_goodsclass"), {
        callback: {
            onClick: function(event, treeId, treeNode) {
                $('#s\\|class').val(treeNode.name).attr('nod',treeNode.id);
                $('.ztree_box').hide();
            }
        }
	}, ztree_data);
	//调用插件
    $('#s_user').selectpage({
        url:'/index/service/unit_list',
        tip:'全部单位',
        valid:'s|unit'
    });
    //调用插件
    $('#s_brand').selectpage({
        url:'/index/service/brand_list',
        tip:'全部品牌',
        valid:'s|brand'
    });
    $('body').on('mousedown','.upload_img',function(nod){
        if (nod.which == 3) {
            if(window.confirm('您确定要删除该商品图像？')){
                $(this).remove();
            }
        }
    });
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
function detail(id){
    var html = '<div class="pop_box"><div class="layui-form layui-form-pane"><div class="layui-tab layui-tab-brief"><ul class="layui-tab-title"><li class="layui-this">基础信息</li><li>辅助属性</li><li>图文详情</li><li nod="more_box">扩展属性</li></ul><div class="layui-tab-content"><div class="layui-tab-item layui-show"><div class="layui-row layui-col-space3"><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商品名称</label><div class="layui-input-block"><input type="text"class="layui-input"id="name"placeholder="请输入商品名称"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商品编号</label><div class="layui-input-block"><input type="text"class="layui-input"id="number"placeholder="请输入商品编号"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">规格型号</label><div class="layui-input-block"><input type="text"class="layui-input"id="spec"placeholder="请输入规格型号"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商品分类</label><div class="layui-input-block"><input type="text"id="class"placeholder="选择商品分类"class="layui-input"onclick="show_ztree(this);"nod=""><div class="ztree_box"><ul id="pop_goodsclass"class="ztree layui-anim layui-anim-upbit"></ul></div></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商品品牌</label><div class="layui-input-block"><div id="pop_brand"class="selectpage"url="/index/service/brand_list"tip="选择商品品牌"valid="brand"></div></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商品单位</label><div class="layui-input-block"><div id="pop_unit"class="selectpage"url="/index/service/unit_list"tip="选择商品单位"valid="unit"></div></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">购货价格</label><div class="layui-input-block"><input type="text"class="layui-input"id="buy"placeholder="请输入购货价格"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">销货价格</label><div class="layui-input-block"><input type="text"class="layui-input"id="sell"placeholder="请输入销货价格"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">零售价格</label><div class="layui-input-block"><input type="text"class="layui-input"id="retail"placeholder="请输入零售价格"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">兑换积分</label><div class="layui-input-block"><input type="text"class="layui-input"id="integral"placeholder="请输入兑换积分"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">条形码</label><div class="layui-input-block"><input type="text"class="layui-input"id="code"placeholder="请输入条形码"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">默认仓库</label><div class="layui-input-block"><div id="pop_warehouse"class="selectpage"url="/index/service/warehouse_list"tip="选择默认仓库"valid="warehouse"></div></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">商品货位</label><div class="layui-input-block"><input type="text"class="layui-input"id="location"placeholder="请输入商品货位"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">库存阈值</label><div class="layui-input-block"><input type="text"class="layui-input"id="stocktip"placeholder="请输入库存预警"></div></div></div><div class="layui-col-md4"><div class="layui-form-item"><label class="layui-form-label">备注信息</label><div class="layui-input-block"><input type="text"class="layui-input"id="data"placeholder="请输入备注信息"></div></div></div></div></div><div class="layui-tab-item attribute"><table class="layui-table"><thead><tr><th width="20%">属性名称</th><th width="80%">属性内容</th></tr></thead><tbody id="attribute_main"></tbody></table><table class="layui-table"><thead><tr><th width="30%">属性名称</th><th width="15%">购货价格</th><th width="15%">销货价格</th><th width="15%">零售价格</th><th width="15%">条形码</th><th width="10%">启用</th></tr></thead><tbody id="attr_main"></tbody></table></div><div class="layui-tab-item"><div class="layui-form-item"><label class="layui-form-label">零售名称</label><div class="layui-input-block"><input type="text"class="layui-input"id="retail_name"placeholder="请输入零售名称"></div></div><div class="layui-form-item layui-form-text"><label class="layui-form-label">商品图像<small>提示：右键图片即可删除</small></label><div class="layui-input-block"><div class="layui-textarea upload_main"><div id="upload_box"><i class="layui-icon layui-icon-upload"></i><p>点击上传图像</p></div><div style="clear: both;"></div></div></div></div><div class="layui-form-item layui-form-text"><label class="layui-form-label">商品详情</label><div class="layui-input-block"><textarea id="details"></textarea></div></div></div><div class="layui-tab-item"nod="more_box"><more></more></div></div></div></div></div>';
    layui.use(['layer','form','element','upload'], function() {
        var form = layui.form;
        var element  = layui.element;
        var upload  = layui.upload;
        var editor;
        layer.ready(function() {
            layer.open({
                id:'pop_main',
                type: 1,
                title: '详情',
                skin: 'layui-layer-rim', //加上边框
                area: ['800px', '450px'], //宽高
                offset: '6%',
                content: html,
                btn: ['保存', '取消'],
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    var more_html=$('#more_html').html();
                    more_html==''?$('*[nod="more_box"]').remove():set_more(more_html);//设置扩展字段
                    pop_move(index);//兼容手机弹层
                    auto_off();//禁止自动输入
                    //调用插件
                    $.fn.zTree.init($("#pop_goodsclass"), {
                        callback: {
                            onClick: function(event, treeId, treeNode) {
                                $('#class').val(treeNode.name).attr('nod',treeNode.id);
                                $('.ztree_box').hide();
                            }
                        }
                	}, ztree_data.slice(1));
                	//调用插件[selectpage]
                	var plug={};
                    $('.pop_box .selectpage').each(function(){
                        plug[$(this).attr('valid')]=$(this).selectpage({
                            url:$(this).attr('url'),
                            tip:$(this).attr('tip'),
                            valid:$(this).attr('valid')
                        });
                    });
                    //调用插件
                    upload.render({
                        elem: '#upload_box',
                        url: '/index/goods/upload_img',
                        accept:'images',
                        acceptMime: 'image/*',
                        done: function(resule){
                            if(resule.state=='success'){
                                $('.upload_main div[style]').before('<div class="upload_img"><img src="'+resule.info+'"/></div>');
                            }else if(resule.state=='error'){
                                dump(resule.info);
                            }else{
                                dump('[ Error ] 服务器返回数据错误!');
                            }
                        }
                    });
                    //调用插件
                    editor=KindEditor.create('#details',{
                        width:'100%',
                        height:'320px',
                        zIndex:19950513,
                        uploadJson:'/index/kindeditor/main'
                    });
                    set_attribute();//渲染辅助属性列表
                    //监听-辅助属性选择
                    form.on('checkbox(attribute)', function(data){
                        set_attr();
                        form.render('checkbox');//单独渲染checkbox
                    });
                    //赋值默认库存阈值
                    $('#stocktip').val(default_stocktip);
                    //获取信息
                    if (id > 0) {
                        ajax('POST','/index/goods/get_goods',{
                            "id": id
                        },function(resule){
                            pop_set('.pop_box',resule);
                            //设置商品分类
                            $('#class').val(resule.classinfo.name).attr('nod',resule.class);
                            var zTree = $.fn.zTree.getZTreeObj("pop_goodsclass");
                            var node = zTree.getNodeByParam("id",resule.class);
                            zTree.selectNode(node);
                            //设置[selectpage]插件信息
                            $('.pop_box .selectpage input[id]').each(function(){
                                var plug_id=$(this).attr('id');
                                var plug_key=plug_id+'info';
                                if(resule[plug_key]){
                                    plug[plug_id].selectdata=[resule[plug_key]];//赋值插件数据
                                    plug[plug_id].render_data();//渲染插件内容
                                }
                            });
                            push_attr(resule.attrinfo);//设置辅助属性
                            //设置图像信息
                            if(resule.imgs!==null){
                                for (var i = 0; i < resule.imgs.length; i++) {
                                    $('#upload_box').after('<div class="upload_img"><img src="'+resule.imgs[i]+'"/></div>');
                                }
                            }
                            editor.html(resule.details);//设置商品详情
                            form.render(); //重新渲染
                        },true);
                    }else{
                        form.render(); //重新渲染
                    }
                },
                btn1: function(layero) {
                    //保存
                    var info=pop_info('.pop_box');
                    info['imgs']=imgs_info();//添加商品图像
                    info['details']=editor.html();//添加商品详情
                    if (reg_test('empty',info['name'])) {
                        dump('商品名称不可为空!');
                    }else if(reg_test('empty',info['class'])){
                        dump('商品分类不可为空!');
                    }else if(!reg_test('plus',info['buy'])){
                        dump('购货价格不正确!');
                    }else if(!reg_test('plus',info['sell'])){
                        dump('销货价格不正确!');
                    }else if(!reg_test('plus',info['retail'])){
                        dump('零售价格不正确!');
                    }else if(!reg_test('empty',info['integral'])&&!reg_test('plus',info['integral'])){
                        dump('兑换积分不正确!');
                    }else if(!reg_test('empty',info['stocktip'])&&!reg_test('plus',info['stocktip'])){
                        dump('库存阈值不正确!');
                    }else{
                        //提交信息
                        info['id']=id;
                        info['attr']=attr_info();//添加辅助属性
                        if(info['attr']!==false){
                            ajax('POST','/index/goods/set_goods',info,function(resule){
                                if(resule.state=='success'){
                                    search();
                                    layer.closeAll();
                                    dump('保存成功!');
                                }else if(resule.state=='error'){
                                    dump(resule.info);
                                }else{
                                    dump('[ Error ] 服务器返回数据错误!');
                                }
                            },true);
                        }
                        
                    }
                }
            });
        });
    });
}
//删除操作
function delect(info){
    layui.use(['layer','table'], function() {
        layer.confirm('您确定要删除所选数据吗？', {
            btn: ['删除', '取消'], //按钮
            offset: '6%'
        }, function() {
            var arr=[];//初始化数据
            //判断删除类型
            if(info=='batch'){
                //批量删除
                var nod = layui.table.checkStatus('data_table');//获取选中数据
                for (var i = 0; i < nod.data.length; i++) {
                    arr.push(nod.data[i].id);//循环加入数据
                }
            }else{
                //常规删除
                arr.push(info);//常规加入数据
            }
            //发送请求
            ajax('POST','/index/goods/del_goods',{
                "arr": arr
            },function(resule){
                if(resule.state=='success'){
                    search();
                    dump('删除成功!');
                    $('.btn_group_right button[batch]').remove();//初始化-删除操作
                }else if(resule.state=='error'){
                    dump(resule.info);
                }else{
                    dump('[ Error ] 服务器返回数据错误!');
                }
            },true);
        });
    });
}
//模板下载
function download_file(){
    jump_info('【 数据请求中 】','http://cdn.nodcloud.com/erp/xlsx/商品导入模板.xlsx',true);
}
//导入操作
function imports(){
    var html='<div class="pop_box"><ul class="imports_ul"><li>1.该功能适用于批量导入数据。</li><li>2.您需要下载数据模板后使用Excel录入数据。</li><li>3.录入数据时，请勿修改首行数据标题以及排序。</li><li>4.请查阅使用文档获取字段格式内容以及相关导入须知。</li><li>5.点击下方上传文件按钮，选择您编辑好的文件即可。</li></ul><hr><div class="imports_box"><button class="layui-btn"onclick="download_file()">下载模板</button><button class="layui-btn layui-btn-primary"id="upload_btn">上传文件</button></div></div>';
    layui.use(['layer','upload'], function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '导入数据',
                skin: 'layui-layer-rim', //加上边框
                area: ['430px', '290px'], //宽高
                offset: '6%',
                content: html,
                fixed: false,
                shadeClose: true,
                success: function(layero, index) {
                    pop_move(index);//兼容手机弹层
                    //弹出后回调
                    layui.upload.render({
                        elem: '#upload_btn',
                        url: '/index/goods/import_goods',
                        accept: 'file',
                        exts: 'xlsx',
                        done: function(resule) {
                            if(resule.state=="success"){
                                search();
                                layer.closeAll();
                                dump('恭喜你，成功导入'+resule.info+'条数据！');
                            }else if(resule.state=='error'){
                                dump(resule.info);
                            }else{
                                dump('[ Error ] 服务器返回数据错误!');
                            }
                        }
                    });
                }
            });
        });
    });
}
//导出操作
function exports(){
    var info=search_info('url');
    jump_info('【 数据请求中 】',"/index/goods/export_goods?"+info,true);
}

//渲染辅助属性列表
function set_attribute(){
    for (var i = 0; i < attribute.length; i++) {
        var subinfo=attribute[i].subinfo;
        //排除扩展属性为空的
        if(subinfo.length>0){
            var html="<tr><th>"+attribute[i].name+"</th><td>";
            for (var s = 0; s < subinfo.length; s++) { 
                html+="<input type='checkbox' value='"+subinfo[s].id+"' title='"+subinfo[s].name+"' lay-skin='primary' lay-filter='attribute'>";
            }
            html+="</td></tr>";
            $('#attribute_main').append(html);
        }
    }
}
//辅助属性选择
function set_attr(){
    var arr=[];
    $('#attribute_main').find('tr').each(function(){
        var data=[];
        $(this).find('input').each(function(){
            $(this).is(':checked')&&(data.push($(this).val()+'|'+$(this).attr('title')));
        });
        data.length>0&&(arr.push(data));
    });
    var nod=combina_arr(arr);//组合属性
    if(nod!==undefined){
        var tr_html='';
        for (var i = 0; i < nod.length; i++) {
            var attr_arr=nod[i].split(",");
            var attr_k=[];//标识数组
            var attr_v=[];//名称数组
            for (var e = 0; e < attr_arr.length; e++){
                var info = attr_arr[e].split("|");
                attr_k.push(info[0]);
                attr_v.push(info[1]);
            }
            attr_key=attr_k.join("_");//标识数组转成字符串
            //拼接HTML
            tr_html+="<tr nod='"+attr_key+"'>";
            for (var s = 0; s < attr_v.length; s++) {
                tr_html+="<td>"+attr_v[s]+"</td>"
            }
            tr_html+="<td><input type='text' placeholder='购货价格' class='layui-input' /></td><td><input type='text' placeholder='销货价格' class='layui-input' /></td><td><input type='text' placeholder='零售价格' class='layui-input' /></td><td><input type='text' placeholder='条形码' class='layui-input'/></td><td><input type='checkbox' lay-skin='primary' checked></td></tr>";
        }
    }
    $('#attr_main').prev().find('th').eq(0).attr("colspan", arr.length);
    $('#attr_main').empty();//清空结构
    $('#attr_main').append(tr_html);//添加数据
}
//组合可变长数组参数
function combina_arr(){  
    var heads=arguments[0][0];
    for(var i=1,len=arguments[0].length;i<len;i++){  
        heads=AddNewType(heads,arguments[0][i]);  
    }  
    return heads;  
}
//在组合结果上添加新规格
function AddNewType(heads,choices){  
    var result=[];
    for(var i=0,len=heads.length;i<len;i++){  
        for(var j=0,lenj=choices.length;j<lenj;j++){  
            result.push(heads[i]+','+choices[j]);
        }  
    }  
    return result;  
}
//获取辅助属性信息
function attr_info(){
    var info = [];
    var state=true;
    $('#attr_main tr').each(function(){
        var obj={};
        obj['nod']=$(this).attr('nod');//属性组合
        //属性名称
        var colspan=$('#attr_main').parent().find('th[colspan]').attr('colspan');
        var name_arr=[];
        for (var i = 0; i < colspan; i++) {
            name_arr.push($(this).find('td').eq(i).html());
        }
        obj['name']=name_arr.join("|");//属性名称
        //购货价格
        var buy=$(this).find('input').eq(0).val();
        if(reg_test('empty',buy)){
            obj['buy']=$('#buy').val();
        }else{
            if(!reg_test('plus',buy)){
                dump('辅助属性第'+($(this).index()+1)+'行购货价格不正确!');
                state = false;
            }else{
                obj['buy']=buy;
            }
        }
        //销货价格
        var sell=$(this).find('input').eq(1).val();
        if(reg_test('empty',sell)){
            obj['sell']=$('#sell').val();
        }else{
            if(!reg_test('plus',sell)){
                dump('辅助属性第'+($(this).index()+1)+'行销货价格不正确!');
                state = false;
            }else{
                obj['sell']=sell;
            }
        }
        //零售价格
        var retail=$(this).find('input').eq(2).val();
        if(reg_test('empty',retail)){
            obj['retail']=$('#retail').val();
        }else{
            if(!reg_test('plus',retail)){
                dump('辅助属性第'+($(this).index()+1)+'行零售价格不正确!');
                state = false;
            }else{
                obj['retail']=retail;
            }
        }
        //条形码
        obj['code']=$(this).find('input').eq(3).val();
        //启用
        var enable=$(this).find('input').eq(4);
        obj['enable']=enable.is(':checked')?1:0;
        info.push(obj);//添加数据
    });
    return state?info:false;
}
//获取商品图像信息
function imgs_info(){
    var info = [];
    $('.upload_main img').each(function(){
        info.push($(this).attr('src'));
    });
    return info;
}
//赋值辅助属性
function push_attr(arr){
    for (var i = 0;  i < arr.length; i++) {
        var data = arr[i].nod.split("_");
        for (var r = 0; r < data.length; r++) {
            $("#attribute_main input[value='"+data[r]+"']").prop("checked", true);
        }
    }
    set_attr();//渲染辅助属性
    for (var e = 0;  e < arr.length; e++) {
        var dom=$("#attr_main tr[nod='"+arr[e].nod+"']").find('input');
        dom.eq(0).val(arr[e].buy);
        dom.eq(1).val(arr[e].sell);
        dom.eq(2).val(arr[e].retail);
        dom.eq(3).val(arr[e].code);
        dom.eq(4).prop("checked",arr[e].enable==1?true:false);
    }
}