;(function ($) {
    //入口函数
    $.fn.selectpage = function(options) {
        var length = $(this).length;
        if(length==0){
            alert('[ SelectPage ] DOM元素不存在!');
        }else if(length===1){
            var plug = new Plug(this,options);//创建实体
            plug.main();//调用主方法
            return plug;
        }else{
            alert('[ SelectPage ] DOM元素重复!');
        }
    };
    //初始化Plug函数
    var Plug = function(_this,options) {
        var random = Math.floor(Math.random()*19950513);//创建随机数
        this.t=_this;
        this.t_id='#'+$(_this).attr('id');
        this.primary_data={};//源数据
        this.defaults = {
            url:'nodcloud.com',//数据请求地址
            css:'/skin/css/selectpage.css',//CSS地址
            checkbox:false,//默认单选
            tip:'请选择数据',//默认提示文字
            valid:'val_'+random,//数据对象ID
            pageid:'page_'+random,//分页ID
            filter:'filter_'+random,//多选标识
            push:{},//请求参数,
            disabled:false,//是否禁用
        };//默认参数
        this.onselect=function(){};//选中触发事件
        this.selectdata=options.hasOwnProperty("selectdata")?options.selectdata:[];//初始化选中数据
        this.config = $.extend(true, this.defaults, options);//合并参数
        //引入CSS
        if($("link[href='"+this.config.css+"']").length==0){
            $('head').append('<link href="'+this.config.css+'" rel="stylesheet" type="text/css" />');
        }
        return this;
    };
    //主方法
    Plug.prototype.main = function(){
        var t=this.t,
        choice = this.config.checkbox===true ? '' : 'select-single',
        html =[
            '<div class="layui-form-select">',
                '<div class="layui-select-title">',
                    '<div class="layui-input multi-select">',
                        '<tip>'+this.config.tip+'</tip>',
                    '</div>',
                    '<i class="layui-edge"></i>',
                '</div>',
                '<dl class="layui-anim layui-anim-upbit">',
                    '<div class="search">',
                        '<i class="layui-icon layui-icon-search"></i>',
                        '<input class="layui-input search_condition" placeholder="关键字搜索">',
                        '<i class="layui-icon layui-icon-close"></i>',
                    '</div>',
                    '<div class="data '+choice+'">',
                    '</div>',
                    '<div id="'+this.config.pageid+'" class="page">',
                    '</div>',
                '</dl>',
                '<input type="text" id="'+this.config.valid+'" style="display:none;">',
            '</div>'
        ].join("");
        $(t).empty();//初始化结构防止重复渲染
        $(t).append(html);//添加结构数据
        this.get_data(1);//初始化数据
        this.render_data();//渲染数据
        //判断是否禁用
        if(this.config.disabled==true){
            $(t).find('.layui-form-select').css('color','#ccc');
        }else{
            this.listens();//监听操作
        }
    };
    //获取数据
    Plug.prototype.get_data = function(page){
        var t = this.t,
        _this=this,
        limit = 5,
        data = $(t).find('.search_condition').val(),
        info={
            "page": page,//当前页数
            "limit": limit,//每页数据
            "data": data//请求数据
        };//请求数据
        info = $.extend(true, info, _this.config.push);//合并请求参数
        ajax('POST',this.config.url,info,function(resule){
            if(resule.state=='success'){
                var dom=$(t).find('.data');
                dom.empty();//清空DOM结构
                _this.primary_data={};//清空源数据
                if(resule.data.length>0){
                    //写入新数据
                    var val=$(t).find('input[id="'+_this.config.valid+'"]').val();//获取现有数据
                    var nod = val.split(",");//字符串转数组
                    for (var i = 0; i < resule.data.length; i++) {
                        var html='';
                        var id=resule.data[i].id;
                        var name=resule.data[i].name;
                        var selected=$.inArray(id.toString(),nod)<0?false:true;
                        if(_this.config.checkbox===true){
                            html+='<dd class="select-item" lay-value="'+id+'"><input type="checkbox" title="'+name+'" lay-filter="'+_this.config.filter+'" lay-skin="primary"';
                            if(selected){html+='checked';}
                            html+='></dd>';
                        }else{
                            html='<dd class="select-item';
                            if(selected){html+=' layui-this';}
                            html+='" lay-value="'+id+'">'+name+'</dd>';
                        }
                        _this.primary_data[id]=resule.data[i];//转存源数据
                        dom.append(html);
                    }
                    _this.render_form();
                }else{
                    //空数据提示
                    dom.append('<span class="empty_tip">【未查询到数据】</span>');
                }
                //渲染分页
                layui.use('laypage', function(){
                    var laypage = layui.laypage;
                    laypage.render({
                        elem: _this.config.pageid,
                        count: resule.count, //数据总数
                        limit:limit,//每页显示的条数
                        curr:page,
                        first: false,
                        last: false,
                        prev: '<i class="layui-icon layui-icon-left"></i>',
                        next: '<i class="layui-icon layui-icon-right"></i>',
                        layout: ['prev', 'next', 'count'],
                        jump: function (obj, first) {
                            //解决LAYUI事件导致的分页跳转关闭面板
                            $(t).find('.layui-box').addClass('layui-select-title');
                            $(t).find('.layui-box a').each(function(){$(this).addClass('layui-select-title');});
                            //首次不执行搜索
                            if(!first){
                                _this.get_data(obj.curr);
                            }
                        }
                    });
                });
            }else if(resule.state=='error'){
                dump(resule.info);
            }else{
                dump('[ Error ] 服务器返回数据错误!');
            }
        },true);
    };
    //渲染数据
    Plug.prototype.render_data = function(){
        var t = this.t,
        element = $(t).find('.multi-select'),//数据显示DOM对象父元素
        valdom=$(t).find('input[id="'+this.config.valid+'"]');//取值DOM对象
        element.find('.selected').remove(),//删除数据显示DOM元素
        valdom.val('');//清空取值数据
        //清空数据区选中
        if(this.config.checkbox===true){
            $(t).find('.data .select-item input').prop('checked',false);//多选取消选中
        }else{
            $(t).find('.data .select-item').removeClass('layui-this');//清空单选样式
        }
        if(this.selectdata.length>0){
            $(t).find('tip').hide();//隐藏提示信息
            var arr=[];
            for (var i = 0; i < this.selectdata.length; i++) {
                var html='<div class="selected"><span>'+this.selectdata[i].name+'</span><i class="layui-icon layui-icon-close" nod="'+this.selectdata[i].id+'"></i></div>';
                element.append(html);//追加HTML
                //设置数据区选中
                if(this.config.checkbox===true){
                    $(t).find('.select-item[lay-value="'+this.selectdata[i].id+'"] input').prop('checked',true);//多选设置选中
                }else{
                    $(t).find('.select-item[lay-value="'+this.selectdata[i].id+'"]').addClass('layui-this');//单选设置样式
                }
                arr.push(this.selectdata[i].id);//赋值数据
            }
            valdom.val(arr.join(","));//赋值数据
        }else{
            $(t).find('tip').show();//显示提示信息
        }
        this.render_form();//重新渲染
    };
    //删除指定数据
    Plug.prototype.del_data = function(id){
        for (var i = 0; i < this.selectdata.length; i++) {
            if(this.selectdata[i].id==id){
                this.selectdata.splice(i,1);//删除当前数据
                break;
            }
        }
    };
    //LAYUI渲染
    Plug.prototype.render_form = function(){
        layui.use('form', function(){
            layui.form.render();
        });
    };
    //监听操作
    Plug.prototype.listens = function(){
        var t = this.t,
        _this=this;
        //打开或隐藏面板
        $(document).off("click", _this.t_id + " .multi-select").on("click", _this.t_id + " .multi-select", function (e) {
            var element = $(this).parents('.layui-form-select');
            if(element.hasClass("layui-form-selected") === true){
                element.removeClass("layui-form-selected layui-form-selectup");
            }else{
                $('.layui-form-select').removeClass("layui-form-selected layui-form-selectup");
                element.addClass("layui-form-selected");
                //判断弹出方向[排除弹层内]
                if(element.parents('.layui-layer').length==0){
                    $(window).height()-((element.offset().top)+42)-(element.find('dl').height()+12)<=0&&(element.addClass("layui-form-selectup"));
                }
            }
            e.stopPropagation();
        });
        //失去焦点隐藏
        $(document).on("click", "body:not('"+_this.t_id+"')", function () {
            $(t).find('.layui-form-select').removeClass("layui-form-selected");
        });
        //阻止LAYUI事件
        $(document).on("click", _this.t_id + " .layui-anim", function (e) {
            e.stopPropagation();
        });
        //复选点击监听事件
        layui.use(['form'], function () {
            var form = layui.form;
            form.on('checkbox('+_this.config.filter+')', function (data) {
                var dom=$(data.elem);//当前DOM对象
                var id=dom.parent().attr('lay-value');
                var name=dom.attr('title');
                if(dom.prop('checked')){
                    _this.selectdata.push({id:id,name:name});
                    _this.onselect(_this.primary_data[id]);//选中事件
                }else{
                    _this.del_data(id);//删除指定数据
                }
                _this.render_data();
            });
        });
        //监听选中
        $(document).on("click", _this.t_id + " .select-single .select-item", function () {
            $(t).find('.layui-form-select').removeClass("layui-form-selected");//隐藏面板
            _this.selectdata.splice(0,_this.selectdata.length);//清空数组
            _this.selectdata=[{ 
               id: $(this).attr('lay-value'),
               name: $(this).html()
            }];
            _this.onselect(_this.primary_data[$(this).attr('lay-value')]);//选中事件
            _this.render_data();//渲染数据
        });
        //监听回车搜索
        $(document).on("keypress", _this.t_id + " .search_condition", function (e) {
            //是否为Enter键
            if (/^13$/.test(e.keyCode)) {
                _this.get_data(1);//搜索数据
            }
            e.stopPropagation();
        });
        //监听清除搜索内容
        $(document).on("click", _this.t_id + " .search .layui-icon-close", function (e) {
            var dom=$(t).find('.search_condition');
            //判断是否需要操作
            if(dom.val()!=""){
                dom.val('');//清空数据
                _this.get_data(1);//搜索数据
            }
            e.stopPropagation();
        });
        //监听删除
        $(document).on("click", _this.t_id + " .selected i", function (e) {
            var id=$(this).attr('nod');
            _this.del_data(id);//删除指定数据
            _this.render_data();//渲染数据
            e.stopPropagation();
        });
    };
})(jQuery);