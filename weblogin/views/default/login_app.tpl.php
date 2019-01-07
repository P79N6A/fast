<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
<title>登录</title>
<link href="app/css/efast365_wechat.css" rel="stylesheet" type="text/css">
</head>

<body>

   <div class="login_wrap">
	<form class="login_form" onsubmit="return false;">
    	<img class="logo" src="app/images/efast365_logo.png">

              <input class="gsmc" id="customer_name" type="text"   placeholder="公司名称" value="<?php echo @$_COOKIES['remember_customer_name'];?>" />
                        
    
        
               <input class="yhm"  id="user_code" type="text"   placeholder="用户名">
               

           <input class="mima" id="password" type="password"  placeholder="密码">
                   <p class="p_captchas" <?php if($response['show_captcha'] != '1'){echo 'style="display:none"';}?>><input class="captchas" id="captcha" placeholder="验证码"><span class="captchas_obtain"><img src="app/images/captchas.jpg" id="captcha_img" width="59" height="18"><i class="refresh"></i></span></p>
             <button class="login"  id="btnLogin">登 录</button>
    </form>
</div> 
    
    
    
    
﻿<?php echo load_js('jquery-1.8.1.min.js');?>
<script src="assets/js/bc.js"></script>
<script>
    $(function(){
    $('#btnLogin').click(function() { 
	    var remember = 1;
                var password =   new  bc().ec($('#password').val());
          var  l = password.substr(3,3);

           password =  new  bc().ec(l+password);
        var params = {'do': 1,
            customer_name: $('#customer_name').val(),
            user_code: $('#user_code').val(),
            password: password,
            captcha: $('#captcha').val(),
            _t: new Date(),
            remember: remember,          
            is_app:1
        };
        //console.log(params);
     //   return ;
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

    $(".refresh").click(function(){
        flush_captcha();
    });

    function flush_captcha(){
        var v_src = "?app_act=index/captcha&code=code&_t="+Math.round(Math.random(0)*1000);
        $("#captcha_img").attr("src",v_src);
    }
<?php                 if($response['show_captcha'] == '1'):?>
    flush_captcha();
    <?php            endif;?>
    
});

</script>
</body>
</html>