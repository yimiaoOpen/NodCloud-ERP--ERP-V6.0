layui.use('table', function() {
    var table=layui.table;
    var nod=table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        cols:  [formfield],
        url: '/index/service/billprofit_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
        done: function(res, curr, count){
            table_sum('#data_table',[
        		{'text':'总销售收入','key':'sales_revenue'},
        		{'text':'总销售成本','key':'selling_cost'},
        		{'text':'总销售毛利','key':'gross_margin'},
        		{'text':'总优惠金额','key':'discount'},
        		{'text':'总销售净利润','key':'net_profit'},
        		{'text':'总应收金额','key':'receivable'},
        		{'text':'总实收金额','key':'money'}
        	]);
        }
    });//渲染表格
    //调用插件
    $('.selectpage').each(function(){
        $(this).selectpage({
            url:$(this).attr('url'),
            tip:$(this).attr('tip'),
            valid:$(this).attr('nod'),
            checkbox:$(this).is('[checkbox]')?true:false,
            disabled:$(this).is('[disabled]')?true:false
        });
    });
    form_time();//调用日期插件
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
//导出详情
function exports(){
    var info=search_info('url');
    jump_info('【 数据请求中 】',"/index/service/export_billprofit?"+info,true);
}