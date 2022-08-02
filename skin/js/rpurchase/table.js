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
        //修改触发
        if(cellname=="warehouse"){
            for (var i = 0; i < warehouse_arr.db.length; i++) {
                if(warehouse_arr.db[i].id==value){
                    $(grid_id).jqGrid('setCell',rowid,'warehouse_id',warehouse_arr.db[i].id);//设置所入仓库ID
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
        //初始化配置
        var goodsinfo=arr[i].goodsinfo;
        //基础配置
        var row={
            set_id:arr[i].id,
            warehouse_id:arr[i].warehouse,
            name:goodsinfo.name,
            attr:arr[i].hasOwnProperty('attr')?arr[i].attr.name:'',
            warehouse:arr[i].warehouseinfo.name,
            batch:arr[i].batch,
            serial:arr[i].serial,
            nums:arr[i].nums,
            price:arr[i].price,
            total:arr[i].total,
            brand:goodsinfo.brandinfo?goodsinfo.brandinfo.name:'',
            number:goodsinfo.number,
            class:goodsinfo.classinfo?goodsinfo.classinfo.name:'',
            spec:goodsinfo.spec,
            code:goodsinfo.code,
            unit:goodsinfo.unitinfo?goodsinfo.unitinfo.name:'',
            stocktip:goodsinfo.stocktip,
            location:goodsinfo.location,
            retail_name:goodsinfo.retail_name,
            data:arr[i].data
        };
        //兼容扩展字段
        if(arr[i].more){
            for (var more_key in arr[i].more) {
                row['more_'+more_key]=arr[i].more[more_key];
            }
        }
        add_table_row(row);//添加主表格数据
    }
    cal_total();//统计数据
}
//表格检查
function check_tab(){
    var data = $(grid_id).jqGrid("getGridParam",'data'); //获取表格数据
    var serial_arr=[];//初始化串码数据
    for (var i = 0; i < data.length; i++) {
        //排除空行
        if(data[i].set_id !== '-1'){
            //开始判断
            if(data[i].warehouse_id=='0'){
                dump('第'+(i+1)+'行未选择所入仓库!');
                return false;
            }else if(!reg_test('plus',data[i].nums) || data[i].nums=='0'){
                dump('第'+(i+1)+'行数量错误!');
                return false;
            }else if(!reg_test('plus',data[i].price)){
                dump('第'+(i+1)+'行采购单价不正确!');
                return false;
            }else{
                //处理串码数据
                if(!reg_test('empty',data[i].serial)){
                    //判断串码合法性
                    var serial=data[i].serial.split(',');//分割串码字符串
                    for (var s = 0; s < serial.length; s++) {
                        if(reg_test('empty',serial[s])){
                            dump('第'+(i+1)+'行串码第'+(s+1)+'条不可为空!');
                            return false;
                        }else{
                            if(reg_test('serial',serial[s])){
                                serial_arr.push(serial[s]);//转存串号
                            }else{
                                dump('第'+(i+1)+'行串码第'+(s+1)+'条不正确!');
                                return false;
                            }
                        }
                    }
                    //判断串码与数量的对应关系
                    if(serial.length!=data[i].nums){
                        data[i].nums = serial.length;//覆盖数量
                        $(grid_id).jqGrid('setCell',data[i].id,'nums',data[i].nums);//设置数量
                        dump('第'+(i+1)+'行串码个数与数量不符,已自动计算数量!');
                        return false;
                    }
                }
                //计算行数据
                var total=cal((data[i].nums-0)*(data[i].price-0));//计算行总价
                $(grid_id).jqGrid('setCell',data[i].id,'total',total);//设置行总价
            }
        }
    }
    if(serial_arr.length>0 && isrepeat(serial_arr)){
        dump('存在重复串码,请核实!');
        return false;
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
