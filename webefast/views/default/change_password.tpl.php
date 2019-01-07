<!doctype html>
<html>
<head>
<meta charset="utf-8">
<?php echo load_js('jquery-1.8.1.min.js');?>
<?php echo load_js('base64.js',true);?>
<title>修改密码</title>
<style>
/*reset*/
body,div,a,p,ul,li,img,h1,h2,h3,h4,h5,h6,ol,table,tr,td,form,input,button{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
a{ text-decoration:none;}
li{ list-style:none;}
img,input{ border:none;}

.change_pass{ position:absolute; left:0; top:0; width:100%; height:100%; overflow:hidden; font-size:16px;}
.change_pass .cont{ position:absolute; width:610px; left:50%; margin-left:-305px; top:50%; margin-top:-201px;}
.change_pass .cont h2{ color:#666; text-align:center;}
.change_pass .cont .expires{ padding:30px 0; color:#e2412b; text-align:center;}
.change_pass .cont form{ width:356px; height:194px; margin:0 auto; background:#f6f5f1; padding:24px;}
.change_pass .cont form .p_input{ height:30px; position:relative; margin-bottom:20px;}
.change_pass .cont form .p_input label{ display:inline-block; width:138px; height:30px; line-height:30px; vertical-align:top; color:#666;}
.change_pass .cont form .p_input label i{ font-style:normal; color:#e26e11; position:relative; top:-3px; left:3px;}
.change_pass .cont form .p_input input{ width:202px; height:26px; background:#f5f5f1; border:2px solid #b7b3a5; border-radius:3px; padding-left:10px;}
.change_pass .cont form .p_input input:focus{ border-color:#f1c570;}
.change_pass .cont form .p_input input.error{ border-color:#f74d4d;}
.change_pass .cont form .p_input .remind{ position:absolute; left:138px; top:30px; color:#f74d4d; font-size:14px;}
.change_pass .cont form .p_remark{ color:#666; font-size:14px; padding-top:5px;}
.change_pass .cont .btns{ text-align:center; padding-top:20px;}
.change_pass .cont .btns button{ width:80px; height:28px; border:1px solid #c2c2c2; background:#FFF; text-align:center; color:#666; margin:0 3px; border-radius:3px; cursor:pointer;}
.change_pass .cont .btns button:hover{ color:#ec6d3a;}
</style>
</head>

<body>
    <?php include get_tpl_path('web_page_top'); ?>
<div class="change_pass">
	<img src="assets/images/change_pass.jpg" width="100%" height="100%">
    <div class="cont">
    	<h2>修改密码</h2>
        <p class="expires">
		<?php if ($response['reason'] == 'psw_strong'){?>
		尊敬的用户，您好，密码强度控制为强密码，请按下面说明，修改密码！
		<?php }elseif ($response['reason'] == 'psw_period'){?>
		尊敬的用户，您好，您的密码期限已到，请重新设置密码，新密码需与上次密码不同！
		<?php }else{?>
		尊敬的用户，您好，初次登陆，请修改密码！
		<?php }?>
		</p>
        <form>
        	<p class="p_input"><label>当前密码<i>*</i></label><input type="password"  name="current_pwd" /></p>
            <p class="p_input"><label>新密码<i>*</i></label><input type="password"  name="new_pwd" /></p>
            <p class="p_input"><label>确认密码<i>*</i></label><input type="password" name="sure_pwd" /></p>
            <p class="p_remark">
			<?php if($response['psw_strong']==1){?>
			注：密码长度为8-20位，须为数字、大写字母、小写字母和特殊符号的组合
			<?php }else{ ?>
			注：密码长度为8-20位，须为数字和字母的组合
			<?php }?>
			</p>
			<input type="hidden" name="psw_strong" value ="<?php echo $response['psw_strong']; ?>"/>
        </form>
        <p class="btns"><button type="button" id="change_pwd">确定</button><button type="button" id="cancle">取消</button></p>
    </div>
</div>

<script>

$("#change_pwd").click(function(){
    var data = {
        //current_pwd:$(".change_pass input[name=current_pwd]").val(),
        current_pwd:get_pwd($(".change_pass input[name=current_pwd]").val()),// 临时实现，后面改成rsa算法
        psw_strong:$(".change_pass input[name=psw_strong]").val(),
        //new_pwd:$(".change_pass input[name=new_pwd]").val(),
        new_pwd:get_pwd($(".change_pass input[name=new_pwd]").val()),// 临时实现，后面改成rsa算法
        // sure_pwd:$(".change_pass input[name=sure_pwd]").val()
        sure_pwd:get_pwd($(".change_pass input[name=sure_pwd]").val())
    };
    
    $.post("?app_act=index/change_pwd",data,function(ret){
        if(ret.status=='-1'){
            alert(ret.message);
        }else  if(ret.status=='-10'){
             alert(ret.message);
            window.open(ret.data) ;
        }else{
            alert(ret.message);
            window.location.href = "?app_act=index/logout";
        }
    },'json');
});  
    function get_pwd(pwd){
                    var password =  new Base64().encode(pwd);
              var  l = password.substr(3,3);

             return new Base64().encode(l+password);
    }

$("#cancle").click(function(){
   $(".change_pass input[name=current_pwd]").val("");
   $(".change_pass input[name=new_pwd]").val("");
   $(".change_pass input[name=sure_pwd]").val("");
});  
</script>
<?php include get_tpl_path('j_secure_sdk'); ?>

</body>
</html>
