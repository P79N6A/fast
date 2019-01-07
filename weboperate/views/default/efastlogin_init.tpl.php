<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
body,img,input,p,a{ margin:0; padding:0; font-family:"微软雅黑",Arial, Helvetica, sans-serif}
img,input{ border:none;}
a{ text-decoration:none;}
.page{ position:relative; margin-top:-20px;}
.page .page_bg{ width:100%; min-width:1280px; min-height:768px;}
.page .loader{ width:438px; height:520px; position:absolute; top:43%; left:50%; margin-left:-219px; margin-top:-260px; background:#FFF; border-radius:5px;}
.page .loader_cont{ width:76%; margin:14% auto 0;}
.page .loader_cont .efast_logo{ text-align:center; margin-bottom:50px;}
.page .loader_cont .input_tt{ display:block; width:89%; height:30px; background-color:#e7e7e5; border-radius:6px; font-size:16px; color:#8b8d8e; padding:7px 5%; border:2px solid #FFF; line-height:30px;}
.page .loader_cont .input_tt:focus{ border:2px solid #7ecaf1;}
.page .loader_cont .prompt{ font-size:12px; color:#f03434; line-height:26px; visibility:hidden;}
.page .loader_cont .btn{ display:block; width:100%; height:44px; border-radius:6px; font-size:22px; letter-spacing:30px; text-align:center; line-height:44px; background:#e95613; color:#FFF; text-indent:25px; margin-bottom:26px;}
.page .loader_cont .btn:hover{ text-decoration:none;}
.page .loader_cont .btn:focus{ text-decoration:none;}
.page .loader_cont .option .remember{ float:left; color:#8b8d8e; font-size:14px; }
.page .loader_cont .option .remember .remember_ck{ display:block; float:left; width:14px; height:13px; background:url(../images/check.jpg) no-repeat; margin-right:5px; margin-top:4px;}
.page .loader_cont .option .remember .remember_cked{display:block; float:left; width:14px; height:13px; background:url(../images/checked.jpg) no-repeat; margin-right:5px; margin-top:4px;}
.page .loader_cont .option .remember .remember_tt{ display:block; float:left; width:100px;}
.page .loader_cont .option .forget{ float:right; color:#8b8d8e; font-size:14px;}
</style>
<?php echo load_js('jquery-1.8.1.min.js');?>
<title>eFAST电商快车</title>
</head>

<body>
<div class="page">
    <img class="page_bg" src="assets/images/page_bg.jpg" />
    <div class="loader">
        <div class="loader_cont">
            <div class="efast_logo"><img src="assets/images/efast.jpg" /></div>        <input class="input_tt shh" id="customer_code" type="text" value="" placeholder="商户代码 User name" />
            <p class="prompt prompt01">商户代码不存在</p>
            <input class="input_tt yhm"  type="text" value="" id="user_code" name="user_code" placeholder="用户名 User name"/>
            <p class="prompt prompt02">用户名不存在</p>
            <input class="input_tt mm" type="password" value="" id="password" name="password" placeholder="密码 Password"/>
            <p class="prompt prompt03">密码不正确</p>
            <a class="btn" href="#" id="btnLogin">登录</a>
            <div class="option">
            <!--
                <label class="remember"><a name="checkWeek" class="remember_ck" href="javascript:void(0)"></a><span class="remember_tt">记住用户名</span></label><a class="forget" href="#">忘记密码？</a>
                -->
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
            customer_code: $('#customer_code').val(),
            user_code: $('#user_code').val(),
            password: $('#password').val(),
            app_fmt: 'json',
            //remember: $("a[name='checkWeek']").hasClass('remember_cked') ? 1 : 0,
        };
        $.post('<?php echo get_app_url('efastlogin/login_efast');?>', params, function(data) {
            try{
                var ret = eval('('+data+')');
            }catch(e){
                alert('登录失败：'+data);
                return;
            }
            if (ret.status == 1) {
                window.location.href = ret.data;
            } else {
                // 错误信息提示
                alert(ret.message);
            }
        });
    });

    $("body").ajaxError(function(event,request,settings){
         alert('登录失败：'+request.responseText);
    });

})
</script>
</body>
</html>
