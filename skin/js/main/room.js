layui.use('table', function() {
    var table=layui.table;
    var nod=table.render({
        id: 'data_table',
        elem: '#data_table',
        height:'full-120',
        cols:  [formfield],
        url: '/index/service/room_list',
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
                    {field:'nums',text:'[ 库存数:{0} ]',nod:['sum']}
                ]
            });
            table_sum('#data_table',[
                {'text':'当前页库存总数','key':'nums'},
            ]);
        }
    });//渲染表格
    //监听工具条事件
    table.on('tool(table_main)', function(obj){
        var data = obj.data;
        var event = obj.event;
        if(event == 'info'){
            info(data.id);//详情
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
    jump_info('【 数据请求中 】',"/index/service/export_room?"+info,true);
}
//库存详情
function info(id){
    //视图
    layui.use('layer', function() {
       layer.open({
            type: 2,
            title: '库存详情',
            offset: '1%',
            fixed: false,
            area: ['99%', '98%'],
            shadeClose: true,
            content: '/index/main/room_info.html?id='+id
        }); 
    });
}