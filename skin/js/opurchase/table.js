var goods_source='goods';//商品数据源
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
        check_tab();//保存触发数据检测
    }
});
//获取数据
function pop_data(arr,type){
    $(grid_id).jqGrid("saveCell",lastrow,lastcell); //保存当前编辑的单元格
    for (var i = 0; i < arr.length; i++) {
        //初始化配置
        var obj=type?arr[i]:arr[i].goodsinfo;
        //基础配置
        var row={
            set_id:obj.id,
            attr_nod:'-1',
            name:type?arr[i].name:arr[i].goodsinfo.name,
            attr:'',
            nums:type?'1':arr[i].nums,
            brand:obj.brandinfo?obj.brandinfo.name:'',
            number:type?arr[i].number:arr[i].goodsinfo.number,
            class:obj.classinfo?obj.classinfo.name:'',
            spec:type?arr[i].spec:arr[i].goodsinfo.spec,
            code:type?arr[i].code:arr[i].goodsinfo.code,
            unit:obj.unitinfo?obj.unitinfo.name:'',
            stocktip:type?arr[i].stocktip:arr[i].goodsinfo.stocktip,
            location:type?arr[i].location:arr[i].goodsinfo.location,
            retail_name:type?arr[i].retail_name:arr[i].goodsinfo.retail_name,
            data:type?'':arr[i].data
        };
        //处理辅助属性
        if(obj.attrinfo.length>0){
            row['attr'] = arr[i].hasOwnProperty('attr')?arr[i].attr.name:'点击选择';
            row['attr_nod'] = arr[i].hasOwnProperty('attr')?arr[i].attr.nod:'0';
            select_attr[obj.id]=obj.attrinfo;//转存辅助属性数组
        }
        //兼容扩展字段
        if(arr[i].more){
            for (var more_key in arr[i].more) {
                row['more_'+more_key]=arr[i].more[more_key];
            }
        }
        add_table_row(row);//添加主表格数据
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
            if(data[i].attr_nod=='0'){
                dump('第'+(i+1)+'行未选择辅助属性!');
                return false;
            }else if(!reg_test('plus',data[i].nums) || data[i].nums=='0'){
                dump('第'+(i+1)+'行数量错误!');
                return false;
            }
        }
    }
    cal_total();//统计数据
    return true;
}
//统计数据
function cal_total(){
    var nums = cal($(grid_id).getCol('nums', false, 'sum')); //数量合计
    $(grid_id).footerData("set", {"nums": "总数量:"+nums});//设置合计
}
