<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>宝塔科技运维平台</title>
<!--?php echo load_css('login.css');?-->
<script type="text/javascript">
    if(window.location !== window.top.location){
        window.top.location=window.location; 
    }
</script>
<?php echo load_css('login.css',true);?>
<?php echo load_js('jquery-1.8.1.min.js');?>
<?php echo load_js('login.js',true);?>
</head>
<body>
 <div class="login_top">
 	<div>
       <img src="assets/img/logo_03.png" />
       <p class="f14 fgray"><a href="#">注册</a> | <a href="" onclick="this.style.behavior='url(#default#homepage)';this.setHomePage(window.location.href);">设为首页</a> | <a href="" id="collect">收藏</a> | <a href="#">帮助</a></p>
    </div>
    </div>
  <div id="login">
       <div  class="login_box">
           <div class="login_con">
               <input name="4" type="hidden"  id="loginsum" value="<?php echo CTX()->get_session("loginsum") ?>" />
               <p class="lab_title"><b>管理登录</b></p>
               <p class="lab">
               		<span class="text_bg focus">
                           <input name="1" type="text" placeholder="用户登录名" value="<?php echo isset($_COOKIE['username'])?$_COOKIE['username']:'';?>"  id="username" />
               		</span>
               </p>
               <p class="lab">
               		<span class="text_bg ">
	               		<input name="2" placeholder="请输入你的密码" type="password" value=""  id="password" />
               		</span>
               </p>
               <p id="capdiv" class="lab" style="display:none">
                   <span class="text_bg ">
                       <input name="3" type="text"  id="captcha" />
                       <img title="看不清楚，双击图片换一张" alt="验证码" src="?app_act=login/captcha&code=code" onclick="this.src=this.src+'&'+Math.round(Math.random(0)*1000)" style="cursor:pointer;width: 58px;height: 35px; vertical-align: middle;"></img>
                   </span>
               </p>
               
               <p class="lab_login">
                   <span class="s1"><input name="3" type="button" value="" class="login_b pointer" id="login_submit"/></span>
               		
               	</p>
               <p class="lab_check"><span class="s2"><input type="checkbox" value="1" id="remember" <?php echo (isset($_COOKIE['remember']) && ($_COOKIE['remember'] == 1))?'checked=checked':'';?>/>记住用户名</span></p>
               <p class="lab_t" id="msg_show">欢迎来到宝塔科技运维平台</p>
           </div>
           <div class="clearfloat"></div>
       </div>
  </div>
  <div style="clear:both;"></div>
  
  <?php if(CTX()->get_app_conf('is_strong_safe')):?>
<script type="text/javascript" src="http://g.tbcdn.cn/sj/securesdk/0.0.3/securesdk_v2.js" id="J_secure_sdk_v2" data-appkey="23272446"></script>
<?php endif;?>
</body>
<style>
.login_con{
        position: absolute;  
        right: -25px; 
}
#msg_show{
    top: 380px; 
}
</style>
</html>
