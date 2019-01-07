
$(document).ready(function(){
        if($("#loginsum").val()!=""){
            if(parseInt($("#loginsum").val())>=3){
                $("#capdiv").show();
            }
        }else{
            $("#loginsum").val("0");
        }
	$("#username,#password").focus(function(){
		$("#msg_show").css({'color':'rgb(23,124,226)'}).html('欢迎来到翼商网络运维平台');
	});
	//提交
	$("#login_submit").click(function(){
		var remember = isRemember();
		var name = $.trim($("#username").val());
		if(name == ''){
			$("#msg_show").css({'color':'rgb(263,24,13)'}).html('用户名不能为空! ');
			return false;
		}
		var password = $.trim($("#password").val());
		if(password == ''){
			$("#msg_show").css({'color':'rgb(263,24,13)'}).html('密码不能为空 !');
			return false;
		}
                var captcha=$.trim($("#captcha").val());
                if(parseInt($("#loginsum").val())>=3){
                    if(captcha==""){
                        $("#msg_show").css({'color':'rgb(263,24,13)'}).html('验证码不能为空 !');
			return false;
                    }
                }
		var url = "?app_act=login/do_login";
		$.post(url,{'username':name,'password':password,'remember':remember,'captcha':captcha},function(data){
                        if(data.status == "-1"){
                            if($("#loginsum").val()!=""){
                                $("#loginsum").val(parseInt($("#loginsum").val())+1);
                            }else{
                                $("#loginsum").val("1");
                            }
                            if(parseInt($("#loginsum").val())>=3){
                                $("#capdiv").show();
                            }
                        }
			if(data.status == "1"){
                            //登录成功跳转
                            window.location='?app_act=index/do_index';
			}else if(data.message=="UserOut"){
                            $("#password").val("");
                            $("#msg_show").css({'color':'rgb(263,24,13)'}).html('登录失败,用户已离职');
			}else if(data.message=="UserDisabled"){
                            $("#password").val("");
                            $("#msg_show").css({'color':'rgb(263,24,13)'}).html('登录失败,用户已锁定');
			}else if(data.message=="PwdError"){
                            $("#password").val("");
                            $("#msg_show").css({'color':'rgb(263,24,13)'}).html('登录失败,密码验证错误');
			}else if(data.message=="UserNotFind"){
                            $("#msg_show").css({'color':'rgb(263,24,13)'}).html('登录失败,用户不存在');
                        }else if(data.message=="CaptchaError"){
                            $("#password").val("");
                            $("#msg_show").css({'color':'rgb(263,24,13)'}).html('登录失败,验证码错误');
                        }else if(data.status == "10"){
	
                              window.location.href= data.data ;
                        }
		},"json");
	});
	
	//是否记住用户名
	function isRemember(){
		if($("#remember").is(":checked")){
			return $("#remember:checked").attr('value');
		}
		return 0;			
	}
	//绑定enter
	$(document).bind('keydown', function (e) {
		var key = e.which;
        if (key == 13) {
        	$('#login_submit').click();
        }
    });
	
	//收藏处理
	$("#collect").click(function(){
		var title = $("title").html();
		var href = window.location.href ;
		AddFavorite(title,href);
	});
	//收藏
	function AddFavorite(sTitle,sURL)
	{
	    try
	    {
	        window.external.addFavorite(sURL, sTitle);
	    }
	    catch (e)
	    {
	        try
	        {
	            window.sidebar.addPanel(sTitle, sURL, "");
	        }
	        catch (e)
	        {
	            return ture;
	        }
	    }
	}
	var _cache ={};
	function checkInputVal(){
		var account = $("#username"), pwd = $("#password");
        _cache.inputs = [account, pwd];
		if(_cache.inputs){
            for(var i = 0, len = _cache.inputs.length; i < len; i++){
                var input = _cache.inputs[i];
                var label = input.prev();
                if(input.val() != ""){   
                    label.css("display") != "none" && label.hide();
                }
                else{
                    label.css("display") == "none" && label.show();
                }
            }
        }
	}
	window.setInterval(checkInputVal, 100);
	
	//焦点处理
	var _focusTimer;
	$("#nameprev,#username").mouseover(function(){
		if(_focusTimer) window.clearTimeout(_focusTimer);
        _focusTimer = window.setTimeout(function(){
            $("#username").focus();
        }, 200);
	}).mouseout(function(){
		if(_focusTimer) window.clearTimeout(_focusTimer);
	});
	$("#pwdprev,#password").mouseover(function(){
		if(_focusTimer) window.clearTimeout(_focusTimer);
        _focusTimer = window.setTimeout(function(){
            $("#password").focus();
        }, 200);
	}).mouseout(function(){
		if(_focusTimer) window.clearTimeout(_focusTimer);
	});
});