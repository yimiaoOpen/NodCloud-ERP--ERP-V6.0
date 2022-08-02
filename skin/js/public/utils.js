/**
 * Created by Administrator on 2017/11/9.
 */
/**
 * 前台js常用函数工具类
 * @author:chenyi
 * @version 1.0
 * @Date: 2017/12/8
 */
(function ($, window) {
    //前台工具类对象
    window.$t = window.$t || {};



    /**通过枚举获取数据*/
    window.$t.getDataByEnum=function (enumName) {
        /**localStorage是否已存在该数据*/
        var data = $t.getStorageItem(enumName);
        if (!data) {
            $.ajax({
                url: $s.getDataByEnum,
                async: false,
                type: 'post',
                data: {enumName: enumName},
                dataType: "json",
                success: function (R) {
                    if (R.code == 0) {
                        data = R;
                        /**设置localStorage缓存*/
                        $t.setStorageItem(enumName, data);
                    } else {
                        data = {};
                        alert(R.msg);
                    }
                }
            });
        }
        return data;
    };

    /**通过字典获取数据*/
    window.$t.getDataByCode=function (codeName) {
        /**localStorage是否已存在该数据*/
        var data = $t.getStorageItem(codeName);
        if (!data) {
            $.ajax({
                url: $s.getDataByCode,
                async: false,
                data: {codeName: codeName},
                type: 'post',
                dataType: "json",
                success: function (R) {
                    if (R.code == 0) {
                        data = R;
                        /**设置localStorage缓存*/
                        $t.setStorageItem(codeName, data);
                    } else {
                        data = {};
                        alert(R.msg);
                    }
                }
            });

        }

        return data;
    };

    /**
     * 添加tabs页
     * @Date: 2018/04/16
     */
    window.$t.addTab=function (url,name) {
        //判断该页面是否已存在
        if (  $(parent.document).find("#navTab").find("li[data-url='" + url + "']").length === 0) {
            var index = Loading.open(1,false);
            //如果不存在
            $(parent.document).find("#navTab").find("li").removeClass("selected");
            //新增tab页
            var _li = ['<li tabid="tools-utils" class="selected" data-url="' + url + '">',
                '<a href="javascript:" title="' + name + '" class="tools-utils">',
                '<span>' + name + '</span>',
                '</a>',
                '<a href="javascript:;" class="close">close</a>',
                '</li>'].join("");
            $(parent.document).find("#navTab").find("ul").append(_li);
            //新增右侧更多list
            $(parent.document).find(".tabsMoreList").find("li").removeClass("selected");
            var moreli = '<li class="selected" data-url="'+url+'"><a href="javascript:"  title="' + name + '">' + name + '</a></li>';
            $(parent.document).find(".tabsMoreList").append(moreli);

            $(parent.document).find(".content").find("iframe").removeClass("cy-show");
            //打开iframe
            var iframe = $('<iframe class="cy-show" scrolling="yes" frameborder="0" style="width: 100%; height: 100%; overflow: visible; "></iframe>');
            $(iframe).attr("src", url);
            $(parent.document).find(".content").append(iframe);
            $(iframe).load(function() {
                Loading.close(index);
            });

            //tab过多时
            var _lis = $(parent.document).find(".tabsPageHeaderContent").find("li");
            var n = 0;
            for (var i = 0; i < _lis.length; i++) {
                n += $(_lis[i]).width();
            }

            //获取右侧区域宽度
            var _width = $(parent.document).find("#navTab").width();
            if (n > parseInt(_width)-150 ) {
                $(parent.document).find(".tabsRight,.tabsLeft").show();
            }


        }else{
            $(parent.document).find("#navTab").find("li").removeClass("selected");
            $(parent.document).find("#navTab").find("li[data-url='" + url + "']").addClass("selected");
            $(parent.document).find(".content").find("iframe").removeClass("cy-show");
            $(parent.document).find(".content").find("iframe[src='"+url+"']").addClass("cy-show");
            //更多列表
            $(parent.document).find(".tabsMoreList").find("li").removeClass("selected");
            $(parent.document).find(".tabsMoreList").find("li[data-url='"+url+"']").addClass("selected");
        }

    };

    /**
     * 关闭窗口
     * @Date: 2017/12/8
     */
    window.$t.closeWindow=function () {
        var  frameindex= parent.layer.getFrameIndex(window.name);
        parent.layer.close(frameindex);
    };
    /**
     * 关闭窗口并刷新
     * @Date: 2017/12/8
     */
    window.$t.Refresh=function (PageId) {
        var  frameindex= parent.layer.getFrameIndex(window.name);
        parent.layer.close(frameindex);
        if(PageId){
           var iframe=$(parent.document).find("#"+PageId).find("iframe")[0];
            $(iframe).contents().find(".search-btn").click();
        }else{
            var parent_iframe=$(parent.document).find(".layadmin-tabsbody-item.layui-show iframe")[0]||$(parent.document).find("iframe")[0];
            $(parent_iframe).contents().find(".search-btn").click();
        }

    };
    /**
     * 获取前端缓存
     * @param key   字典或枚举  code|enum
     * @Date: 2017/12/8
     */
    window.$t.getStorageItem = function (key) {
        return JSON.parse(localStorage.getItem(key));
    };

    /**
     * 设置前端缓存
     * @param key   字典或枚举  code|enum
     * @param data   存储的值（数组）
     * @Date: 2017/12/8
     */
    window.$t.setStorageItem = function (key, data) {
        localStorage.setItem(key, JSON.stringify(data));
    };
    /**
     * 获取前端缓存
     * @param key   字典或枚举  code|enum
     * @Date: 2017/12/8
     */
    window.$t.getSessionStorage = function (key) {
        return JSON.parse(sessionStorage.getItem(key));
    };

    /**
     * 设置前端缓存
     * @param key   字典或枚举  code|enum
     * @param data   存储的值（数组）
     * @Date: 2017/12/8
     */
    window.$t.setSessionStorage = function (key, data) {
        sessionStorage.setItem(key, JSON.stringify(data));
    };
    /**
     * 日期格式化
     * @param fmt   转化格式
     * @param date   时间
     * @Date: 2017/12/8
     */
    window.$t.dateFormat=function(dateStr,fmt) {

        var date=new Date(dateStr);
        var o = {
            "M+" : date.getMonth()+1,                 //月份
            "d+" : date.getDate(),                    //日
            "h+" : date.getHours(),                   //小时
            "m+" : date.getMinutes(),                 //分
            "s+" : date.getSeconds(),                 //秒
            "q+" : Math.floor((date.getMonth()+3)/3), //季度
            "S"  : date.getMilliseconds()             //毫秒
        };
        if(/(y+)/.test(fmt)) {
            fmt=fmt.replace(RegExp.$1, (date.getFullYear()+"").substr(4 - RegExp.$1.length));
        }
        for(var k in o) {
            if(new RegExp("("+ k +")").test(fmt)){
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
            }
        }
        return fmt;
    };

    /**
     * 时间格式化
     * @param fmt        转化格式
     * @param timeStamp   时间戳
     * @Date: 2017/12/8
     */
    window.$t.timeFormat=function (timeStamp,fmt) {
        var date = new Date();
        if(timeStamp.length===10){
            date.setTime(timeStamp * 1000);
        }
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        m = m < 10 ? ('0' + m) : m;
        var d = date.getDate();
        d = d < 10 ? ('0' + d) : d;
        var h = date.getHours();
        h = h < 10 ? ('0' + h) : h;
        var minute = date.getMinutes();
        var second = date.getSeconds();
        minute = minute < 10 ? ('0' + minute) : minute;
        second = second < 10 ? ('0' + second) : second;
        return $t.dateFormat(y + '-' + m + '-' + d+' '+h+':'+minute+':'+second,fmt);
    };

    /**
     * 获取oss临时账号,返回oss客户端
     */
    window.$t.getOssClient = function () {
        var accessKeyId;
        var accessKeySecret;
        var securityToken;
        var bucket;
        $.ajax({
            type:"POST",
            url: $s.getOssUrl,
            dataType: "json",
            async: false,
            cache: false,
            success: function (result) {
                if(result.code==0){
                    accessKeyId = result.data.accessKeyId;
                    accessKeySecret = result.data.accessKeySecret;
                    securityToken = result.data.securityToken;
                    bucket = result.data.bucket;
                }else{
                    Msg.error("oss账号获取失败");
                }


            },
            error: function (error) {
                Msg.error("oss账号获取失败");
            }
        });

        var client = new OSS.Wrapper({
            region: 'img-cn-shanghai',
            accessKeyId: accessKeyId,
            accessKeySecret: accessKeySecret,
            stsToken: securityToken,
            bucket: bucket
        });

        return client;
    };



    /**
     * 获取文件名
     */
    window.$t.getFileName = function (fileName) {
        var timestamp=new Date().getTime();

        var suffix=fileName.substr(fileName.indexOf("."));
        var result = timestamp+$t.getUUID(6,8)+suffix;
        return result;
    };

    /**
     * 获取oss图片访问路径
     */
    window.$t.getImgBaseUrl = function () {
        var ossBaseUrl= localStorage.getItem("ossBaseUrl");
        if(ossBaseUrl!='undefined'&&ossBaseUrl!=''&&ossBaseUrl!=null){
            return ossBaseUrl;
        }
        $.ajax({
            type:"POST",
            url: $s.getOssBaseUrl,
            dataType: "json",
            async: false,
            cache: false,
            success: function (result) {
                if(result.code==0){
                    ossBaseUrl=result.ossBaseUrl;
                    localStorage.setItem("ossBaseUrl",result.ossBaseUrl);
                }else{
                    Msg.error("oss图片根路径获取失败");
                }

            },
            error: function (error) {
                Msg.error("oss图片根路径获取失败");
            }
        });
        return ossBaseUrl;
    };

    /**
     * 获取oss图片访问路径
     */
    window.$t.getOssFileUrl = function (fileName) {
       var ossBaseUrl= localStorage.getItem("ossBaseUrl");
        if(ossBaseUrl!='undefined'&&ossBaseUrl!=''&&ossBaseUrl!=null){
            return ossBaseUrl+fileName;
        }
        $.ajax({
            type:"POST",
            url: $s.getOssBaseUrl,
            dataType: "json",
            async: false,
            cache: false,
            success: function (result) {
                if(result.code==0){
                    fileName=result.ossBaseUrl+fileName;
                    localStorage.setItem("ossBaseUrl",result.ossBaseUrl);
                }else{
                    Msg.error("oss图片根路径获取失败");
                }

            },
            error: function (error) {
                Msg.error("oss图片根路径获取失败");
            }
        });
        return fileName;
    };

    /**
     * Ajax请求数据
     * @param url 请求地址
     * @param params 请求参数
     * @param success 成功回调函数
     * @param async 是否异步请求
     * @param type 请求类型("post"或"get")
     * @param dataType 数据类型，默认"json"
     */
    window.$t.doAjax = function (url, params, success, async, type, dataType) {
        jQuery.support.cors = true;
        $.ajax({
            url: url,
            cache: false,
            dataType: dataType ? dataType : "json",
            type: type && type === 'get' ? 'get' : "post",
            data: params,
            async: async != undefined && async != null && async === false ? async : true,
            success: success,
            timeout: 10000,    //超时时间设置
            error: function (jqXHR, textStatus, errorThrown) {
                $.error("Ajax请求错误\n" + "     textStatus=" + textStatus + "\n" + "     errorThrown=" + errorThrown);
                $.error("\n     url=" + url + "\n    data=" + window.$t.jsonToStr(params));
            },
            beforeSend: function (XMLHttpRequest) {
            }
        });
    };
    /**
     ** 加法函数，用来得到精确的加法结果
     ** 说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。
     ** 调用：accAdd(arg1,arg2)
     ** 返回值：arg1加上arg2的精确结果
     **/
    window.$t.accAdd = function (arg1, arg2) {
        var r1, r2, m, c;
        try {
            r1 = arg1.toString().split(".")[1].length;
        }
        catch (e) {
            r1 = 0;
        }
        try {
            r2 = arg2.toString().split(".")[1].length;
        }
        catch (e) {
            r2 = 0;
        }
        c = Math.abs(r1 - r2);
        m = Math.pow(10, Math.max(r1, r2));
        if (c > 0) {
            var cm = Math.pow(10, c);
            if (r1 > r2) {
                arg1 = Number(arg1.toString().replace(".", ""));
                arg2 = Number(arg2.toString().replace(".", "")) * cm;
            } else {
                arg1 = Number(arg1.toString().replace(".", "")) * cm;
                arg2 = Number(arg2.toString().replace(".", ""));
            }
        } else {
            arg1 = Number(arg1.toString().replace(".", ""));
            arg2 = Number(arg2.toString().replace(".", ""));
        }
        return (arg1 + arg2) / m;
    }

    /**
     ** 减法函数，用来得到精确的减法结果
     ** 说明：javascript的减法结果会有误差，在两个浮点数相减的时候会比较明显。这个函数返回较为精确的减法结果。
     ** 调用：accSub(arg1,arg2)
     ** 返回值：arg1减去arg2的精确结果
     **/
    window.$t.accSub = function (arg1, arg2) {
        var r1, r2, m, n;
        try {
            r1 = arg1.toString().split(".")[1].length;
        }
        catch (e) {
            r1 = 0;
        }
        try {
            r2 = arg2.toString().split(".")[1].length;
        }
        catch (e) {
            r2 = 0;
        }
        m = Math.pow(10, Math.max(r1, r2)); //last modify by deeka //动态控制精度长度
        n = (r1 >= r2) ? r1 : r2;
        return ((arg1 * m - arg2 * m) / m).toFixed(n);
    },

        /**
         ** 乘法函数，用来得到精确的乘法结果
         ** 说明：javascript的乘法结果会有误差，在两个浮点数相乘的时候会比较明显。这个函数返回较为精确的乘法结果。
         ** 调用：accMul(arg1,arg2)
         ** 返回值：arg1乘以 arg2的精确结果
         **/
        window.$t.accMul = function (arg1, arg2) {
            var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
            try {
                m += s1.split(".")[1].length;
            }
            catch (e) {
            }
            try {
                m += s2.split(".")[1].length;
            }
            catch (e) {
            }
            return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
        },

        /**
         ** 除法函数，用来得到精确的除法结果
         ** 说明：javascript的除法结果会有误差，在两个浮点数相除的时候会比较明显。这个函数返回较为精确的除法结果。
         ** 调用：accDiv(arg1,arg2)
         ** 返回值：arg1除以arg2的精确结果
         **/
        window.$t.accDiv = function (arg1, arg2) {
            var t1 = 0, t2 = 0, r1, r2;
            try {
                t1 = arg1.toString().split(".")[1].length;
            }
            catch (e) {
            }
            try {
                t2 = arg2.toString().split(".")[1].length;
            }
            catch (e) {
            }
            with (Math) {
                r1 = Number(arg1.toString().replace(".", ""));
                r2 = Number(arg2.toString().replace(".", ""));
                return (r1 / r2) * pow(10, t2 - t1);
            }
        },

        /**
         * 获取项目根目录
         */
        window.$t.getContextPath = function () {
            // 获取当前网址，如： http://localhost:8083/uimcardprj/share/meun.jsp
            var curWwwPath = window.document.location.href;
            // 获取主机地址之后的目录，如： uimcardprj/share/meun.jsp
            var pathName = window.document.location.pathname;
            var pos = curWwwPath.indexOf(pathName);
            // 获取主机地址，如： http://localhost:8083
            var localhostPaht = curWwwPath.substring(0, pos);
            // 获取带"/"的项目名，如：/uimcardprj
            var projectName = pathName.substring(0, pathName.substr(1).indexOf('/') + 1);
            return (localhostPaht + projectName) + "/";
        };


    /**
     * 产生一个唯一的uuid
     * @param len 产生的字符串长度
     * @param radix 进制数
     */
    window.$t.getUUID = function (len, radix) {
        var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if (len) {
            for (i = 0; i < len; i++) {
                uuid[i] = chars[0 | Math.random() * radix];
            }
        } else {
            var r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for (i = 0; i < 36; i++) {
                if (!uuid[i]) {
                    r = 0 | Math.random() * 16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    };

    /**时候是手机端*/
    window.$t.isMobile=function () {
        return /Android|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent);
    };


})(jQuery, window);