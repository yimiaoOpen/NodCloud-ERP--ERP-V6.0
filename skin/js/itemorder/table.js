var grid_id='#data_table';//数据表格标识
$(grid_id).jqGrid({
    datatype: "local",
    colNames:formfield.colNames,
    colModel:formfield.colModel,
    autowidth:true,//自动宽度
    cellsubmit: 'clientArray', //开启编辑保存
	height: $(document).height()*0.5,//插件高度
	cellEdit: true, //开启单元格编辑
    rownumbers: true, //开启行号
    footerrow:true,//统计
    shrinkToFit: false,//滚动条
    loadComplete:function(){
        jqgrid_resize();//尺寸改变重设宽度
        reserve_line();//预留空行
        tab_hiddens(sys);//设置批次串码隐藏显示
    },
    beforeEditCell:function(rowid,cellname,v,iRow,iCol){lastrow = iRow;lastcell = iCol;},
    afterSaveCell:function(rowid, cellname, value, iRow, iCol){
        check_tab();//保存触发数据检测
    }
});
//获取数据
function pop_data(arr,type){
    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    for (var i = 0; i < arr.length; i++) {
        //初始化配置
        var obj=type?arr[i]:arr[i].serveinfo;
        //基础配置
        var row={
            set_id:obj.id,
            name:obj.name,
            nums:type?'1':arr[i].nums,
            price:type?obj.price:arr[i].price,
            total:type?obj.price:arr[i].total,
            data:type?'':arr[i].data
        };
        //兼容扩展字段
        if(arr[i].more){
            for (var more_key in arr[i].more) {
                row['more_'+more_key]=arr[i].more[more_key];
            }
        }
        add_table_row(row,true);//添加主表格数据
    }
    $(grid_id).jqGrid('addRowData','nod', new_data);//增加空白行
    $(grid_id).jqGrid("nextCell",$(grid_id).find('tr').length-1,0);//最后一行激活输入
    auditing_type=='0'&&(check_tab());//未审核状态检查表格
}
//表格检查
function check_tab(){
    var data = $(grid_id).jqGrid("getGridParam",'data'); //获取表格数据
    for (var i = 0; i < data.length; i++) {
        //排除空行
        if(data[i].set_id !== '-1'){
            //开始判断
            if(!reg_test('plus',data[i].nums) || data[i].nums=='0'){
                dump('第'+(i+1)+'行数量错误!');
                return false;
            }else if(!reg_test('plus',data[i].price)){
                dump('第'+(i+1)+'行服务单价不正确!');
                return false;
            }else{
                //计算行数据
                var total=cal((data[i].nums-0)*(data[i].price-0));//计算行总价
                $(grid_id).jqGrid('setCell',data[i].id,'total',total);//设置行总价
            }
        }
    }
    cal_total();//统计数据
    return true;
}
//统计数据
function cal_total(){
    var nums = cal($(grid_id).getCol('nums', false, 'sum')); //数量合计
    var total = cal($(grid_id).getCol('total', false, 'sum')); //总价合计
    $(grid_id).footerData("set", {"nums": "总数量:"+nums,"total": "总金额:"+total});//设置合计
    $('#total').val(total);//赋值单据金额
}
