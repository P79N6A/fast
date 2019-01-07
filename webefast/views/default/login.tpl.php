<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/login.css" rel="stylesheet" type="text/css" />
<?php echo load_js('jquery-1.8.1.min.js');?>


<title>宝塔eFAST 365</title>
</head>

<body>
<div class="page">
    <img class="page_bg" src="assets/images/page_bg.jpg" />
    <div class="loader">
        <div class="loader_cont">
            <div class="efast_logo"><img src="assets/images/efast.jpg" /></div>        <input class="input_tt shh"  type="text" value="" placeholder="商户代码 User name" />
            <p class="prompt prompt01">商户代码不存在</p>
            <input class="input_tt yhm"  type="text" value="" id="user_code" name="user_code" placeholder="用户名 User name"/>
            <p class="prompt prompt02">用户名不存在</p>
            <input class="input_tt mm" type="password" value="" id="password" name="password" placeholder="密码 Password"/>
            <p class="prompt prompt03">密码不正确</p>
            <a class="btn" href="#" id="btnLogin">登录</a>
            <div class="option">
                <label class="remember"><a name="checkWeek" class="remember_ck" href="javascript:void(0)"></a><span class="remember_tt">记住用户名</span></label><a class="forget" href="#">忘记密码？</a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" >
if(window.location !== window.top.location){
    window.top.location=window.location;
}
$( function(){
    $("label.remember").click(function(){
        if($("a[name='checkWeek']").hasClass('remember_cked')){
            $("a[name='checkWeek']").removeClass('remember_cked');
        }else{
            $("a[name='checkWeek']").addClass('remember_cked');
        }
    });


    $('#user_code').keydown(function(e){
        if(e.keyCode==13){
           $('#btnLogin').click();
        }
    });
    $('#password').keydown(function(e){
        if(e.keyCode==13){
           $('#btnLogin').click();
        }
    });


    $('#btnLogin').click(function() {

        var params = {'do': 1,
            user_code: $('#user_code').val(),
            password: $('#password').val(),
            remember: $("a[name='checkWeek']").hasClass('remember_cked') ? 1 : 0,
            <?php echo CSRFHandler::TOKEN_NAME.":'".CSRFHandler::get_token()."'"?>
        };
        $.post('<?php echo get_app_url('index/login');?>', params, function(data) {
            var ret = eval('('+data+')');
            if (ret.status == 1) {
                window.location.href = '<?php echo get_app_url('index/do_index');?>';
            } else {
                if(ret.status == -5){
                	window.location.href = "?app_act=index/change_password&reason=psw_strong";
                }else if(ret.status == -6){
                	window.location.href = "?app_act=index/change_password&reason=first_login";
                }else if(ret.status == -7){
                	window.location.href = "?app_act=index/change_password&reason=psw_period";
                }else if(ret.status == -8){ 
                	window.location.href = "?app_act=sys/auto_create/param_list";
                }else if (ret.status == -9){
                	alert("该用户被停用，禁止登录");
                }else{
                    // 错误信息提示
                	alert(ret.message);
                }
            }
        });
    });

})
</script>
</body>
</html>
