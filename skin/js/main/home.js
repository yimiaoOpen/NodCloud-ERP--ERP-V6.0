layui.config({
    base: '/skin/js/' //静态资源所在路径
}).extend({
    index: 'lib/index', //主入口模块
}).use('index', function() {
    //区块轮播切换
    layui.use(['admin', 'carousel'], function() {
        var $ = layui.$,
            admin = layui.admin,
            carousel = layui.carousel,
            element = layui.element,
            device = layui.device();
        //轮播切换
        $('.layadmin-carousel').each(function() {
            var othis = $(this);
            carousel.render({
                elem: this,
                width: '100%',
                arrow: 'none',
                interval: othis.data('interval'),
                autoplay: othis.data('autoplay') == true,
                trigger: (device.ios || device.android) ? 'click' : 'hover',
                anim: othis.data('anim')
            });
        });
        element.render('progress');
    });
    //数据概览
    layui.use(['admin', 'carousel', 'echarts'], function() {
        var $ = layui.$,
            admin = layui.admin,
            carousel = layui.carousel,
            echarts = layui.echarts;
        var echartsApp = [],
            options = [
                {
                    title: {text: '购货单',x:'center',textStyle: {fontSize: 14}},
                    tooltip: {
                        trigger: 'axis'
                    },
                    xAxis: [{type: 'category',boundaryGap: false,data: day}],
                    yAxis: [{type: 'value'}],
                    series: [{
                        name: '单据金额',
                        type: 'line',
                        smooth: true,
                        itemStyle: {normal: {areaStyle: {type: 'default'}}},
                        data:echarts_data['purchase']
                    }]
                },
                {
                    title: {text: '采购入库单',x:'center',textStyle: {fontSize: 14}},
                    tooltip: {
                        trigger: 'axis'
                    },
                    xAxis: [{type: 'category',boundaryGap: false,data: day}],
                    yAxis: [{type: 'value'}],
                    series: [{
                        name: '单据金额',
                        type: 'line',
                        smooth: true,
                        itemStyle: {normal: {areaStyle: {type: 'default'}}},
                        data:echarts_data['rpurchase']
                    }]
                },
                {
                    title: {text: '销货单',x:'center',textStyle: {fontSize: 14}},
                    tooltip: {
                        trigger: 'axis'
                    },
                    xAxis: [{type: 'category',boundaryGap: false,data: day}],
                    yAxis: [{type: 'value'}],
                    series: [{
                        name: '单据金额',
                        type: 'line',
                        smooth: true,
                        itemStyle: {normal: {areaStyle: {type: 'default'}}},
                        data:echarts_data['sale']
                    }]
                },
                {
                    title: {text: '零售单',x:'center',textStyle: {fontSize: 14}},
                    tooltip: {
                        trigger: 'axis'
                    },
                    xAxis: [{type: 'category',boundaryGap: false,data: day}],
                    yAxis: [{type: 'value'}],
                    series: [{
                        name: '单据金额',
                        type: 'line',
                        smooth: true,
                        itemStyle: {normal: {areaStyle: {type: 'default'}}},
                        data:echarts_data['cashier']
                    }]
                },{
                    title: {text: '服务单',x:'center',textStyle: {fontSize: 14}},
                    tooltip: {
                        trigger: 'axis'
                    },
                    xAxis: [{type: 'category',boundaryGap: false,data: day}],
                    yAxis: [{type: 'value'}],
                    series: [{
                        name: '单据金额',
                        type: 'line',
                        smooth: true,
                        itemStyle: {normal: {areaStyle: {type: 'default'}}},
                        data:echarts_data['itemorder']
                    }]
                }
            ],
            elemDataView = $('#LAY-index-dataview').children('div'),
            renderDataView = function(index) {
                echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
                echartsApp[index].setOption(options[index]);
                //window.onresize = echartsApp[index].resize;
                admin.resize(function() {
                    echartsApp[index].resize();
                });
            };
        //没找到DOM，终止执行
        if (!elemDataView[0]) return;
        renderDataView(0);
        //监听数据概览轮播
        var carouselIndex = 0;
        carousel.on('change(LAY-index-dataview)', function(obj) {
            renderDataView(carouselIndex = obj.index);
        });
        //监听侧边伸缩
        layui.admin.on('side', function() {
            setTimeout(function() {
                renderDataView(carouselIndex);
            }, 300);
        });
        //监听路由
        layui.admin.on('hash(tab)', function() {
            layui.router().path.join('') || renderDataView(carouselIndex);
        });
    });
});