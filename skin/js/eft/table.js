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
    },
    beforeEditCell:function(rowid,cellname,v,iRow,iCol){lastrow = iRow;lastcell = iCol;},
    afterSaveCell:function(rowid, cellname, value, iRow, iCol){
        //修改触发
        if(cellname=="account"){
            for (var i = 0; i < account_arr.db.length; i++) {
                if(account_arr.db[i].id==value){
                    $(grid_id).jqGrid('setCell',rowid,'set_id',account_arr.db[i].id);//设置调出账户ID
                    break;
                }
            }
        }else if(cellname=="toaccount"){
            for (var i = 0; i < account_arr.db.length; i++) {
                if(account_arr.db[i].id==value){
                    $(grid_id).jqGrid('setCell',rowid,'toaccount_id',account_arr.db[i].id);//设置调入账户ID
                    break;
                }
            }
        }
        check_tab();//保存触发数据检测
    }
});
//获取数据
function pop_data(arr,type){
    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    for (var i = 0; i < arr.length; i++) {
        //基础配置
        var row={
            set_id:arr[i].account,
            toaccount_id:arr[i].toaccount,
            account:arr[i].accountinfo.name,
            toaccount:arr[i].toaccountinfo.name,
            total:arr[i].total,
            data:arr[i].data
        };
        //兼容扩展字段
        if(arr[i].more){
            for (var more_key in arr[i].more) {
                row['more_'+more_key]=arr[i].more[more_key];
            }
        }
        add_table_row(row,false);//添加主表格数据
    }
    $(grid_id).jqGrid('addRowData','nod', new_data);//增加空白行
    auditing_type=='0'&&(check_tab());//未审核状态检查表格
}
//表格检查
function check_tab(){
    var data = $(grid_id).jqGrid("getGridParam",'data'); //获取表格数据
    for (var i = 0; i < data.length; i++) {
        //排除空行
        if(data[i].set_id !== '-1'){
            console.log(data[i].toaccount_id);
            //开始判断
            if(data[i].toaccount_id == undefined){
                dump('第'+(i+1)+'行未选择调入账户!');
                return false;
            }else if((data[i].set_id-0)==(data[i].toaccount_id-0)){
                dump('第'+(i+1)+'行调出调入账户相同!');
                return false;
            }else if(!reg_test('plus',data[i].total) || data[i].total=='0'){
                dump('第'+(i+1)+'行结算金额错误!');
                return false;
            }
        }
    }
    cal_total();//统计数据
    return true;
}
//统计数据
function cal_total(){
    var total = cal($(grid_id).getCol('total', false, 'sum')); //金额合计
    $(grid_id).footerData("set", {"total": "总金额:"+total});//设置合计
}
