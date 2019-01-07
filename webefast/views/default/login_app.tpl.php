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
	<form class="login_form">
    	<img class="logo" src="app/images/efast365_logo.png">
        <input class="gsmc" type="text" placeholder="公司名称">
        <input class="yhm" type="text" id="user_code" placeholder="用户名">
        <input class="mima" type="password" placeholder="密码" id="password">
      <button class="login" type="button" id="btnLogin">登 录</button>
    </form>
</div>
    <?php echo load_js('jquery-1.8.1.min.js');?>
<script type="text/javascript" >

$( function(){
 
    $('#btnLogin').click(function() {

        var params = {'do': 1,
            user_code: $('#user_code').val(),
            password: $('#password').val(),
            remember: 1,
            <?php echo CSRFHandler::TOKEN_NAME.":'".CSRFHandler::get_token()."'"?>
        };
        $.post('<?php echo get_app_url('index/login');?>', params, function(ret) {
                var url =  '<?php echo get_app_url('app/index/do_index');?>';
            if (ret.status == 1) {
                window.location.href = url;
            } else {
                if(ret.status == -5){
                  window.location.href = url;
                }else if(ret.status == -6){
                	  window.location.href = url;
                }else if(ret.status == -7){
                	  window.location.href = url;
                }else if(ret.status == -8){ 
                	  window.location.href = url;
                }else if (ret.status == -9){
                	alert("该用户被停用，禁止登录");
                }else{
                    // 错误信息提示
                	alert(ret.message);
                }
            }
        },'json');
    });

});
</script>
</body>
</html>
