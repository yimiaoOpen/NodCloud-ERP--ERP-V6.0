//登陆页
$(function(){
    //随机背景
    var nums= Math.floor(Math.random()*21+1); 　//输出1～21之间的随机整数
    $('#LAY-user-login').css('background-image','url(/skin/images/login/'+nums+'.jpg)');
    //兼容登录失效
    if( window.top != window.self ){
        top.window.location.href='/';
    }
});
//验证账号密码
function login(){
    var user = $('#user').val();
    var pwd = $('#pwd').val();
    if(reg_test('empty',user)){
        dump('请输入账号!');
    }else if(reg_test('empty',pwd)){
        dump('请输入密码!');
    }else{
        $.post("/index/index/check_user",{"user":user,"pwd":pwd}, function(resule) {
            if(resule.state=='success'){
                location.reload();
            }else if(resule.state=='error'){
                dump(resule.info);
            }else{
                dump('服务器响应超时!');
            }
        });
    }
}
//回车事件
document.onkeydown = function (event) {
    var e = event || window.event || arguments.callee.caller.arguments[0];
    if (e && e.keyCode == 13) {
        login();
    }
};