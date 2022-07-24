layui.use('table', function() {
    var table=layui.table;
    var nod=table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        cols:  [formfield],
        url: '/index/service/roomwarning_list',
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
                data:[{field:'name',text:'[ {0} ]-[ 商品数:{1} ]',nod:['data','count']}]
            });
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
    jump_info('【 数据请求中 】',"/index/service/export_roomwarning?"+info,true);
}