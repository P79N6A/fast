<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>宝塔eFAST 365</title>
﻿<?php echo load_js('jquery-1.8.1.min.js');?>
<style>
/*reset*/
body,div,p,h1,h2,h3,h4,h5,h6,a,ul,li,ol,span,img,input,marquee{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
li{ list-style:none;}
a,a:hover,a:focus,a:active{ text-decoration:none;}
input,img{ border:none;}

.login_blue{ width:100%; height:100%; position:absolute; left:0; top:0; background:#FFF; overflow:hidden;}
.login_blue .top{ width:980px; margin:0 auto; padding:20px 0; line-height:32px; height:32px;}
.login_blue .top .contact,.login_blue .top .vertical{ float:right; font-size:12px; color:#999; vertical-align:top; margin:0 10px;}
.login_blue .top .online:hover,.login_blue .top .help:hover{ text-decoration:underline;}
.login_blue .top .contact i{ display:inline-block; width:26px; height:26px; background:url(assets/img/login_blue/contact_icon.png) no-repeat; vertical-align:middle; margin-right:5px;}
.login_blue .top .telephone i{ background-position:5px 0;}
.login_blue .top .online i{ background-position:-150px 0;}
.login_blue .top .help i{ background-position:-277px 0;}
.login_blue .banner{ position:relative;}
.login_blue .banner .content{ width:51%; height:85%; position:absolute; left:24.5%; top:12%; min-width:650px;}
.login_blue .banner .content .leftpic{ display:block; float:left; position:relative; left:-125px;}
.login_blue .banner .content .rightlogin{ width:33%; height:100%; text-align:center; position:absolute; top:-10px; right:0px; min-width:278px;}
.login_blue .banner .content .rightlogin .loginform{ padding:6%; background:#FFF; border-radius:3px; margin-top:5px; overflow:hidden;}
.login_blue .banner .content .rightlogin .loginform .p_input{ margin-bottom:3.5%;}
.login_blue .banner .content .rightlogin .loginform .p_input input{ font-size:14px; width:83.5%; padding:2% 0 2% 15%; border:2px solid #d6d6d6; border-radius:3px; color:#333; background:url(assets/img/login_blue/input_icon_blue.png) no-repeat;}
.login_blue .banner .content .rightlogin .loginform .p_input input:focus{ border-color:#2d9fcf;}
.login_blue .banner .content .rightlogin .loginform .p_input input.error{ border-color:#f74d4d;}
.login_blue .banner .content .rightlogin .loginform .p_input .company{background-position:4% 5px;}
.login_blue .banner .content .rightlogin .loginform .p_input .username{background-position:4% -43px;}
.login_blue .banner .content .rightlogin .loginform .p_input .password{background-position:4% -92px;}
.login_blue .banner .content .rightlogin .loginform .p_captchas{ overflow:hidden;}
.login_blue .banner .content .rightlogin .loginform .p_captchas .captchas{ width:32%; float:left; background-position:9% -142px;}
.login_blue .banner .content .rightlogin .loginform .p_captchas .captchas_obtain{ display:inline-block; width:47%; padding:1.5% 0 1.5%; border:2px solid #d6d6d6; border-radius:3px; color:#333; vertical-align:top; float:right;}
.login_blue .banner .content .rightlogin .loginform .p_captchas .captchas_obtain img{ vertical-align:middle; margin-right:10%;}
.login_blue .banner .content .rightlogin .loginform .p_captchas .captchas_obtain .refresh{ display:inline-block; width:16px; height:14px; background:url(assets/img/login_blue/refresh.png) no-repeat; vertical-align:middle; cursor:pointer;}
.login_blue .banner .content .rightlogin .loginform .p_keep{text-align:left; margin-bottom:3.5%;}
.login_blue .banner .content .rightlogin .loginform .p_keep .keep{ color:#666; font-size:14px; display:inline-block; vertical-align:top; height:20px; cursor:pointer;}
.login_blue .banner .content .rightlogin .loginform .p_keep .keep .icon{ display:inline-block; width:20px; height:20px; background:url(assets/img/login_blue/keep.png) no-repeat; margin-right:5px;}
.login_blue .banner .content .rightlogin .loginform .p_keep .keep .icon.active{ background-position:0 -30px;}
.login_blue .banner .content .rightlogin .loginform .loginbtn{ width:100%; font-size:20px; padding:3% 0; color:#FFF; background:#f4bf3e; border:none; border-radius:3px; cursor:pointer; margin-bottom:3.5%;}
.login_blue .banner .content .rightlogin .loginform .loginbtn:hover{box-shadow:0 0 5px #ccc;}
.login_blue .bottom{ text-align:center; padding-top:4%; position:relative;}
.login_blue .bottom .part4-1{position:absolute; left:0; top:0; width: 100%;}
.login_blue .bottom .part4-film{width: 100%;height: 60px;background: transparent url("assets/img/login_blue/part4_film.png") no-repeat scroll 50% 0px;position:absolute; left:0; top:0; margin: -34px auto 0px;}
.login_blue .bottom .regards{ color:#666; padding-top:3%; font-size:18px;}
.message_pop {
	background: #fff8e6 none repeat scroll 0 0;
    border: 1px solid #ffcd03;
    border-radius: 3px;
    height: 28px;
    left: 0.1%;
    line-height: 28px;
    position: fixed;
    text-align: center;
    top: 0;
    width: 99.7%;
    z-index: 300;
    font-size:12px;
/*     display:none; */
}
.message_pop .icon {
    background: rgba(0, 0, 0, 0) url("assets/img/ui/mess_icon.png") no-repeat scroll 0 0;
    display: inline-block;
    height: 18px;
    margin-right: 15px;
    width: 24px;
    vertical-align: text-top;
}

.message_pop .mess {
    color: #a29b95;
}

.message_pop .readbtn {
    border: 1px solid #adc8dc;
    border-radius: 3px;
    color: #1695ca;
    cursor: pointer;
    display: inline-block;
    line-height: 18px;
    margin-left: 15px;
    padding: 0 8px;
}
.message_pop .closebtn {
    color: #666;
    cursor: pointer;
    float: right;
    font-size: 27px;
    margin-right: 5px;
}

.message_pop .firefox-download {color: red;text-decoration:underline;}

</style>
<link href="assets/css/ripple .css" rel="stylesheet" type="text/css">
</head>
<script language="JavaScript">
$(function() {
	/*if(navigator.userAgent.indexOf("Firefox") < 0){
		$("#message_pop").show();
	}*/
})
</script>


<body>
<div class="message_pop" id="message_pop">
	<i class="icon"></i>
	<span class="mess">
		<!--亲，为了让您得到最佳的系统操作体验，请使用更为标准化的火狐浏览器！-->
                温馨提示：亲，为了让您得到最佳的系统操作体验，请使用更为高效的谷歌浏览器！
                <a class="firefox-download" href="http://login.baotayun.com/Chrome_install/57.0.2987.133_chrome_installer.exe" target="_blank" >点击下载 </a>
                <!--<a class="firefox-download" href="http://login.baotayun.com/Firefox_install/firefox_44.0.2_install_x32.zip" target="_blank" >点击下载</a>-->
                <!--<a class="firefox-download" href="http://login.baotayun.com/Firefox_install/firefox_44.0.2_install_x64.zip" target="_blank" >64位版</a>-->
	</span>
	<!--span class="readbtn">已读</span--><span class="closebtn">&times;</span>
</div>
<div class="login_blue">
	<div class="top">
    	<img src="assets/img/login_blue/efast5_logo_blue.png" width="116" height="32">
        <a class="contact help" href="javascript:void(0)"><i></i>帮助</a><span class="vertical">|</span><a class="contact online" href="http://crm2.qq.com/page/portalpage/wpa.php?uin=4006809510&f=1&ty=1&aty=0&a=&from=5" target="_blank"><i></i>在线客服</a><span class="contact telephone"><i></i>400-600-9585</span>
    </div>
    <div class="banner">
    	<img src="assets/img/login_blue/login_blue_banner.jpg" width="100%" style="min-height:400px">
        <div class="content">
        	<img class="leftpic" src="assets/img/login_blue/banner_left_pic.png" height="100%">
            <div class="rightlogin">
            	<img class="logintop" src="assets/img/login_blue/right_login_top.png" width="70%">
                <form class="loginform" onsubmit="return false;">
                	<p class="p_input"><input class="company" id="customer_name" type="text"   placeholder="公司名称" value="<?php echo @$_COOKIES['remember_customer_name'];?>" /></p>
                    <p class="p_input"><input class="username error"  id="user_code"  placeholder="用户名"></p>
                    <p class="p_input"><input class="password" id="password" type="password"  placeholder="密码"></p>
                              <?php
                $style= 'style ="display:none";';
                
                if($response['show_captcha'] == '1'){
                    $style = '';
                }
 
                ?>
                    
                    <p class="p_input p_captchas" id="captcha_p"  <?php echo $style;?>><input class="captchas" id="captcha"  placeholder="验证码"><span class="captchas_obtain"><img id="captcha_img" src="assets/img/login_blue/captchas.jpg" width="70" height="22"><i class="refresh" id="captcha_img_flush"></i></span></p>
                    <p class="p_keep"><span class="keep"><i class="icon"></i>记住公司名称</span></p>
                    <button class="loginbtn"  id="btnLogin">登 录</button>
                </form>
            </div>
        </div>
    </div>
    <div class="bottom">
		<div id="part4_0" class="part4-0" style="display: block;">
				<p class="part4-0-0"></p>
				<p class="part4-0-1"></p>
				<p class="part4-0-2"></p>
				<p class="part4-0-3"></p>
				<p class="part4-0-4"></p>
				<p class="part4-0-5"></p>
				<p class="part4-0-6"></p>
				<p class="part4-0-7"></p>
				<p class="part4-0-8"></p>
				<p class="part4-0-9"></p>
				<p class="part4-0-10"></p>
				<p class="part4-0-11"></p>
				<p class="part4-0-12"></p>
				<p class="part4-0-13"></p>
				<p class="part4-0-14"></p>
				<p class="part4-0-15"></p>
			</div>
			<div class="part4-1">
				<p id="part4_film" class="part4-film" style="background-position: 50% -2460px;" index="52"></p>
			</div>
    	<img src="assets/img/login_blue/bottom_bg.png" width="1145" height="84">
        <p class="regards">感谢您对eFAST365一如既往的支持，祝您工作愉快！</p>
    </div>	
</div>

<script src="assets/js/bc.js"></script>
<script type="text/javascript">
$(".message_pop .closebtn").click(function(){
	$(".message_pop").hide();
})
$(function(){
	$(".p_keep").find(".keep").click(function(){
		$(this).children(".icon").toggleClass("active")
		});
	
	
	var win = {
		1:{initPs:{
		  'part4-0':{'display':"none"}
		  },
		  endPs:{
		  'part4-0':{'display':"block"}
		  },
		  filmData:{
		  tag:"part4_film",
		  dis:'part4_0',
		  ps:[,,,,,,,,,,0,-60,-120,-180,-240,-300,-360,-420,-480,-540,-600,-660,-720,-780,-840,-900,-960,-1020,-1080,-1140,-1200,-1260,-1320,-1380,-1440,-1500,-1560,-1620,-1680,-1740,-1800,-1860,-1920,-1980,-2040,-2100,-2160,-2220,-2280,-2340,-2400,-2460,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,]
		}}
		}
	function fnFilm(o){
    //逐帧函数
    var tag = $("#"+o.tag);
    var val = tag.attr("index")||0;

    if(o.ps[val] !== ""){
      tag.css("backgroundPosition","50% "+o.ps[val]+"px");
    }
	if(o.dis && (val==1)){
      $("#"+o.dis).css({'display':'block'});
    }else if(o.dis && (val== o.ps.length-1)){
      $("#"+o.dis).css({'display':'none'});
    }
    val++;
    tag.attr("index",val);
    (val>o.ps.length-1) && tag.attr("index",0);

  }
  setInterval(function(){
          fnFilm(win[1].filmData);
        },40);
	});
</script>
<script>
$( function(){
//    $('#user_code').keydown(function(e){
//        if(e.keyCode==13){
//           $('#btnLogin').click();
//        }
//    });
//    $('#password').keydown(function(e){
//        if(e.keyCode==13){
//           $('#btnLogin').click();
//        }
//    });
    $("#customer_name").blur(function(){
        check_customer_name();
    });

    $('#btnLogin').click(function() { 
	    var remember = $(".p_keep i").hasClass('active') == true ? 1 :0 ;
        var password =   new  bc().ec($('#password').val());
          var  l = password.substr(3,3);

           password =  new  bc().ec(l+password);
        var params = {'do': 1,
            customer_name: $('#customer_name').val(),
            user_code: $('#user_code').val(),
            password:password,
            captcha: $('#captcha').val(),
            _t: new Date(),
            remember: remember
        };
        //console.log(params);
     //   return ;
        $.post('<?php echo get_app_url('index/login_new');?>', params, function(data) {
            var ret = eval('('+data+')');
            if (ret.status == 1) {
                //alert(ret.data);
                parent.parent.location.href = ret.data;
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
    check_customer_name();
    $("#captcha_img_flush").click(function(){
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

  function check_customer_name(){
    var customer_name =$("#customer_name").val();
    if(customer_name==''){
        return false;
    }

   var param = {};
   var url = "?app_act=index/check_customer_name&customer_name="+customer_name;
   $('#check_c').attr('src',url);

  }

</script>


<?php if(CTX()->get_app_conf('is_strong_safe')):?>

<?php endif;?>

<iframe id="check_c" name="check_c" style="height: 0px;width: 0px;"></iframe>
</body>
</html>
