<?php echo load_js('jquery-1.8.1.min.js');?>
<style>
/*reset*/
body,div,a,p,ul,li,img,h1,h2,h3,h4,h5,h6,ol,table,tr,td,form,input{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
a{ text-decoration:none;}
li{ list-style:none;}
img,input{ border:none;}

.login_wrap{ position:absolute; top:0px;left:0px;width:100%; height:100%; background:#F4F4F4 url(assets/images/login_bg.jpg) no-repeat center center; background-size:cover;}
.login_wrap .top{ width:950px; margin:12px auto 0;}
.login_wrap .top img{ vertical-align:middle;}
.login_wrap .top span{ color:#edb03b; margin:0 33px 0 10px;}
.login_wrap .top a{ color:#edb03b;}
.login_wrap .top a img{ margin-right:7px;}
.login_wrap .top i{ color:#edb03b; font-style:normal; margin:0 20px 0 20px;}

.login_wrap .midl{ width:925px; overflow:hidden; position:absolute; top:50%; left:50%; margin-left:-470px; margin-top:-300px;}
.login_wrap .midl .picture_wrap{ float:left;}
.login_wrap .midl .picture_wrap .picture_icon{ float:left; margin-right:35px; padding-top:270px; display:none;}
.login_wrap .midl .picture_wrap .picture_icon a{ display:block; width:16px; height:16px; margin-bottom:25px; background:url(assets/images/picture_icon.png) no-repeat 0 bottom; text-indent:-100px; overflow:hidden;}
.login_wrap .midl .picture_wrap .picture_icon a.curr{ background-position:0 top;}
.login_wrap .midl .picture_wrap .picture_cont{ float:left; display:none; padding-top:75px;}
.login_wrap .midl .form_wrap{ float:right; text-align:center; position:relative; width:340px; overflow:hidden; padding-top:75px;}
.login_wrap .midl .form_wrap form{ padding:28px 0 0; background-color:#FFF; border-radius:5px; overflow:hidden; box-shadow: 0px 0px 10px #ccc; margin:25px 5px 5px;}
.login_wrap .midl .form_wrap form .input_p{ margin-bottom:12px; position:relative;}
.login_wrap .midl .form_wrap form .input_p input{ width:226px; height:35px; background-color:#FFF; border-radius:5px; padding-left:44px; color:#333; font-size:14px; border:2px solid #d6d6d6;}
.login_wrap .midl .form_wrap form .input_p input:focus{border-color:#f1c570;}
.login_wrap .midl .form_wrap form .input_p input.error{ border-color:#f74d4d;}
.login_wrap .midl .form_wrap form .input_p input.merchant_code{ background-image:url(assets/images/login_input_bg.png); background-repeat:no-repeat; background-position:10px 4px;}
.login_wrap .midl .form_wrap form .input_p input.user_name{ background-image:url(assets/images/login_input_bg.png); background-repeat:no-repeat; background-position:10px -46px;}
.login_wrap .midl .form_wrap form .input_p input.secret_code{ background-image:url(assets/images/login_input_bg.png); background-repeat:no-repeat; background-position:10px -95px;}
.login_wrap .midl .form_wrap form .input_p span{ display:block; width:256px; height:35px; padding-left:14px; background:url(assets/images/login_remind_bg.png) repeat-x; border-radius:5px; color:#fff478; position:absolute; left:55px; bottom:-36px; z-index:10; text-align:left; line-height:35px; display:none;}
.login_wrap .midl .form_wrap form .input_p span img{ vertical-align:middle; margin-right:5px;}
.login_wrap .midl .form_wrap form .captcha{ margin-bottom:12px;}
.login_wrap .midl .form_wrap form .captcha input{ width:86px; height:35px; background-color:#FFF; border-radius:5px; padding-left:44px;color:#9e9fb6; font-size:14px; border:2px solid #d6d6d6; background-image:url(assets/images/login_input_bg.png); background-repeat:no-repeat; background-position:10px -144px; margin-right:7px;}
.login_wrap .midl .form_wrap form .captcha input:focus{border-color:#f1c570;}
.login_wrap .midl .form_wrap form .captcha input.error{ border-color:#f74d4d;}
.login_wrap .midl .form_wrap form .captcha .code_wrap{ display:inline-block; width:130px; height:35px; background-color:#FFF; border-radius:5px; border:2px solid #d6d6d6; vertical-align:top; text-align:left; position:relative; text-indent:10px;}
.login_wrap .midl .form_wrap form .captcha .code_wrap .code_pic{ margin-top:9px;}
.login_wrap .midl .form_wrap form .captcha .code_wrap i{ display:inline-block; position:absolute; right:5px; top:3px; cursor:pointer;}
.login_wrap .midl .form_wrap form .Save_p{ padding:12px 0 28px; text-align:left;}
.login_wrap .midl .form_wrap form .Save_p .save{ color:#666; margin-left:29px;}
.login_wrap .midl .form_wrap form .Save_p .save span{ display:inline-block; width:20px; height:20px; background:url(assets/images/save_bg.png) no-repeat; margin-right:12px;}
.login_wrap .midl .form_wrap form .Save_p .save span.true{ background-position:0 bottom;}
.login_wrap .midl .form_wrap form .Save_p .forget{ color:#FFF; display:none;}
.login_wrap .midl .form_wrap form .Save_p .forget:hover{ text-decoration:underline;}
.login_wrap .midl .form_wrap form .btns_p{ padding-bottom:30px;}
.login_wrap .midl .form_wrap form .btns_p button{ width:270px; height:36px; border:2px solid #f6813a; color:#FFF; border-radius:4px; background:#f6813a; font-size:18px; margin:0 13px; cursor:pointer;}
.login_wrap .midl .form_wrap form .btns_p button:hover{ border-color:#fe904d; background:#fe904d;}
.login_wrap .midl .form_wrap form .taobao_login{ height:84px; background:#f6f4f4; text-align:center;}
.login_wrap .midl .form_wrap form .taobao_login span{ color:#999; display:inline-block; padding:8px 0 15px; font-size:14px;}
.login_wrap .midl .form_wrap form .taobao_login img{ vertical-align:middle;}
.login_wrap .midl .form_wrap form .taobao_login a{ color:#666; text-decoration:underline; margin-left:15px;}
</style>
<div class="login_wrap">
	<div class="top"><img src="assets/images/login_hotline.png" width="17" height="23"><span>400-680-9510</span><i>|</i><a href="#"><img src="assets/images/login_online_help.png" width="26" height="23">在线帮助</a></div>
    <div class="midl">
    	<div class="picture_wrap">
        	<p class="picture_icon"><a class="curr" href="javascript:void(0)">1</a><a href="javascript:void(0)">2</a><a href="javascript:void(0)">3</a></p>
            <div class="picture_cont" style="display:block"><img src="assets/images/picture_01.png"></div>
            <!--<div class="picture_cont"><img src="assets/images/picture_02.png"></div>
            <div class="picture_cont"><img src="assets/images/picture_03.png"></div>-->
        </div>
        <div class="form_wrap">
        	<img src="assets/images/form_top_pic.png" width="302" height="111">
            <form>
            	<p class="input_p">
	            	<input id="customer_name" type="text" class="merchant_code" placeholder="公司名称" width="226" height="35" value="<?php echo @$_COOKIES['remember_customer_name'];?>"/>
		            <span><img src="assets/images/login_remind_icon.png" width="16" height="16">请填写有效的公司名称</span>
		        </p>
                <p class="input_p"><input id="user_code" type="text" class="user_name" placeholder="用户名" /><span><img src="assets/images/login_remind_icon.png" width="16" height="16">请填写有效的用户</span></p>
                <p class="input_p"><input id="password" type="password" class="secret_code" placeholder="密码" /></p>
                <?php
                $style= 'style ="display:none";';
                if($response['show_captcha'] == '1'){
                    $style = '';
                }
                ?>
                <p class="captcha" id="captcha_p" <?php echo $style;?>><input id="captcha" type="text" placeholder="验证码"><span class="code_wrap"><img id="captcha_img" alt="验证码" src="?app_act=index/captcha&code=code" style="cursor:pointer;width:83px;height: 35px; vertical-align: middle;"/><i>
                <img src="assets/images/code_btn.png" width="32" height="28" id="captcha_img_flush"/></i></span></p>

                <p class="Save_p"><a href="javascript:void(0)" class="save"><span>&nbsp; </span>记住公司名称</a>
                <a href="#" class="forget">忘记密码？</a></p>
                <p class="btns_p"><button type="button" id="btnLogin">登录</button></p>
<!--
                <p class="taobao_login" href="#">

	                <span>第三方账号登陆</span>
                	<br><img src="assets/images/taobao_icon.png" width="29" height="22"><a href="#">淘宝账号登陆</a>

                </p>
-->
            </form>
        </div>
    </div>
</div>
﻿<?php echo load_js('base64.js');?>
<script>
$(function(){

/*	var i=-1; //第i+1个tab开始
var offset = 4000; //轮换时间
var timer = null;
function autoroll(){
	n = $(".picture_icon a").length-1;
	i++;
	if(i > n){
	i = 0;
	}
	slide(i);
	timer = window.setTimeout(autoroll, offset);
}

function slide(i){
	$(".picture_icon a").eq(i).addClass('curr').siblings().removeClass('curr');
	$(".picture_cont").eq(i).show().siblings('.picture_cont').hide();
}

function settime(){
	$(".picture_icon a").hover(
	function(){
		if(timer){
			clearTimeout(timer);
			i = $(this).prevAll().length;
			slide(i);
		}
	},function(){
		timer = window.setTimeout(autoroll, offset);
		this.blur();
		return false;
	});
}

	autoroll();
	settime();*/

	$(".save").click(function(){
		$(".save span").toggleClass("true")
		})
	})

$( function(){
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
	    var remember = $(".Save_p span").attr('class') == 'true' ? 1 :0 ;
        var params = {'_do_': 1,
            customer_name: $('#customer_name').val(),
            user_code: $('#user_code').val(),
            password: new Base64().encode('@@@'+$('#password').val()+'@@@'),// 临时实现，后面改成rsa算法
            captcha: $('#captcha').val(),
            _t: new Date(),
            remember: remember
        };
        //console.log(params);
        $.post('<?php echo get_app_url('index/login');?>', params, function(data) {
            var ret = eval('('+data+')');
            if (ret.status == 1) {
                //alert(ret.data);
                location.href = ret.data;
            } else {
                if(ret.status == -10){
                    alert(ret.message);
	                $("#captcha_p").css("display","block");
                    flush_captcha();
                    return;
                }
                // 错误信息提示
                alert(ret.message);
                //多次出错后，要输入验证码
                if (ret.data.show_captcha == 1){
	                $("#captcha_p").css("display","block");
                    flush_captcha();
                }
            }
        });
    });

    $("#customer_name").val("<?php echo @$_COOKIE['remember_customer_name'];?>");
    $("#user_code").val("<?php echo @$_COOKIE['remember_user_code'];?>");

    $("#captcha_img_flush").click(function(){
        flush_captcha();
    });

    function flush_captcha(){
        var v_src = "?app_act=index/captcha&code=code&_t="+Math.round(Math.random(0)*1000);
        $("#captcha_img").attr("src",v_src);
    }

    
})
</script>