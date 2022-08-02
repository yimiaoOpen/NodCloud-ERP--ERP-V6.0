var grid_id='#data_table';//数据表格标识
$(function(){
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
    $('#s_brand').selectpage({
        url:'/index/service/brand_list',
        tip:'全部品牌',
        valid:'s|brand'
    });
    //调用插件
    $('#s_warehouse').selectpage({
        url:'/index/service/warehouse_list',
        tip:'全部仓库',
        valid:'s|warehouse'
    });
})
$(grid_id).jqGrid({
    url: "/index/service/roomcheck_list",
	datatype: "JSON",
	mtype: "POST",
    colNames:formfield.colNames,
    colModel:formfield.colModel,
    autowidth:true,//自动宽度
    cellsubmit: 'clientArray', //开启编辑保存
	height: $(document).height()*0.81,//插件高度
	cellEdit: true, //开启单元格编辑
    rownumbers: true, //开启行号
    shrinkToFit: false,//滚动条
    pager: "#data_pager",
    rowNum: 30,//默认条数
    rowList: [30,60,90,150,300],//条数选择
    AutoNextCellNewRow:false,//自动跳转下一个可编辑区域不创建新行
    loadComplete:function(){
        jqgrid_resize();//尺寸改变重设宽度
        tab_hiddens(sys);//设置批次隐藏显示
    },
    beforeEditCell:function(rowid,cellname,v,iRow,iCol){lastrow = iRow;lastcell = iCol;},
    afterSaveCell:function(rowid, cellname, value, iRow, iCol){
        check_tab();//保存触发数据检测
    }
});
//条件搜索
function search() {
    $(grid_id).jqGrid('setGridParam',{
    	postData:search_info('obj')
	}).trigger('reloadGrid');
}
//检查表格数据
function check_tab(){
    var data = $(grid_id).jqGrid("getRowData"); //获取表格数据
    for (var i = 0; i < data.length; i++) {
        //排除空行
        if(data[i].set_id !== '-1' && !reg_test('empty',data[i].stock)){
            //开始判断
            if(!reg_test('plus',data[i].stock)){
                dump('第'+(i+1)+'行盘点数量错误!');
                return false;
            }else{
                //计算行数据
                var check=cal((data[i].stock-0)-(data[i].nums-0));//计算盘盈盘亏
                $(grid_id).jqGrid('setCell',data[i].set_id,'check',check);//设置盘盈盘亏
            }
        }
    }
    return true;
}
//盘点
function inventory(){
    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    var html = '<div class="pop_box form_choice"><ul><li onclick="overage()"><i class="layui-icon layui-icon-form"></i><p>盘盈单</p></li><li onclick="loss()"><i class="layui-icon layui-icon-form"></i><p>盘亏单</p></li></ul></div>';
    layui.use('layer', function() {
        layer.ready(function() {
            layer.open({
                type: 1,
                title: '盘点单类型',
                skin: 'layui-layer-rim', //加上边框
                area: ['390px', '150px'], //宽高
                offset: '12%',
                content: html,
                fixed: false,
                shadeClose: true,
            });
        });
    });
}
//盘盈单 - 其他入库单
function overage(){
    if(check_tab()){
        var arr = $(grid_id).jqGrid("getRowData"); //获取表格数据
        var data=[];
        for (var i = 0; i < arr.length; i++) {
            if((arr[i].check-0)>0){
                var obj={};
                obj['goods']=arr[i].goods_id;
                obj['attr']=arr[i].attr_nod;
                obj['warehouse']=arr[i].warehouse_id;
                obj['batch']=arr[i].batch;
                obj['nums']=arr[i].check;
                data.push(obj);
            }
        }
        if(data.length>0){
            //判断单据打开情况
            if(parent.$("li[lay-attr='/index/otpurchase/main.html']").length>0){
                dump('请先关闭其他入库单页面');
            }else{
                var href='/index/otpurchase/main.html?info='+Base64.encode(JSON.stringify(data));
                parent.layui.index.openTabsPage(href, '其他入库单');
            }
        }else{
            dump('盘盈单数据为空,请核实!');
        }
    }
}
//盘亏单 - 其他出库单
function loss(){
    if(check_tab()){
        var arr = $(grid_id).jqGrid("getRowData"); //获取表格数据
        var data=[];
        for (var i = 0; i < arr.length; i++) {
            if((arr[i].check-0)<0){
                var obj={};
                obj['room']=arr[i].set_id;
                obj['goods']=arr[i].goods_id;
                obj['warehouse']=arr[i].warehouse_id;
                obj['nums']=Math.abs(arr[i].check);
                data.push(obj);
            }
        }
        if(data.length>0){
            //判断单据打开情况
            if(parent.$("li[lay-attr='/index/otsale/main.html']").length>0){
                dump('请先关闭其他出库单页面');
            }else{
                var href='/index/otsale/main.html?info='+Base64.encode(JSON.stringify(data));
                parent.layui.index.openTabsPage(href, '其他出库单');
            }
        }else{
            dump('盘亏单数据为空,请核实!');
        }
    }
}
//导出详情
function exports(){
    var info=search_info('url');
    jump_info('【 数据请求中 】',"/index/service/export_roomcheck?"+info,true);
}