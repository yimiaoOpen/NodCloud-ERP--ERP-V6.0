layui.use('table', function() {
    var table=layui.table;
    var nod=table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        cols:  [formfield],
        url: '/index/service/goodsprofit_list',
        page: true,
        limit: 30,
        limits: [30,60,90,150,300],
        method: 'post',
        where: search_info('obj'),
        initSort: {
            field: 'goods',//排序字段
            type: 'desc',//排序方式
        },
        done: function(res, curr, count){
            table_fold({
                lay_id:'data_table',//数据表格标识
                field:'goods',//排序字段
                data:[
                    {field:'name',text:'[ {0} ]-[ 商品数:{1} ]',nod:['data','count']},
                    {field:'sale',text:'[ 总销货金额:{0} ]',nod:['sum']},
                    {field:'cashier',text:'[ 总零售金额:{0} ]',nod:['sum']},
                    {field:'sales_revenue',text:'[ 总销售收入:{0} ]',nod:['sum']},
                    {field:'sales_cost',text:'[ 总销售成本:{0} ]',nod:['sum']},
                    {field:'sales_maori',text:'[ 总销售毛利:{0} ]',nod:['sum']},
                    {field:'gross_interest_rate',text:'[ 总销售毛利率:{0} ]',nod:[function(data){
                        var sales_revenue = 0;//销售收入
                        var sales_maori = 0;//销售毛利
                        for (var i = 0; i < data.length; i++) {
                            sales_revenue = cal((sales_revenue-0)+(data[i].sales_revenue-0));
                            sales_maori = cal((sales_maori-0)+(data[i].sales_maori-0));
                        }
                        return sales_revenue==0?'0%':cal((sales_maori/sales_revenue)*100)+'%';
                    }]}
                ]
            });
            table_sum('#data_table',[
                {'text':'总销货金额','key':'sale'},
                {'text':'总零售金额','key':'cashier'},
                {'text':'总销售收入','key':'sales_revenue'},
                {'text':'总销售成本','key':'sales_cost'},
                {'text':'总销售毛利','key':'sales_maori'}
            ]);
        }
    });//渲染表格
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
    jump_info('【 数据请求中 】',"/index/service/export_goodsprofit?"+info,true);
}