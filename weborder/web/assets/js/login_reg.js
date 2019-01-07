$(function(){
	$("#login").click(function(){
            $("#account_register").slideUp();
            $("#account_login").slideToggle();
	})
                
	$("#account_login .close,#account_login .back").click(function(){
            $("#account_login").slideUp();
	})
        
	$("#account_login .btns .btn_02").click(function(){
            $("#account_login").slideUp();
           
            $("#account_register").slideDown(1000);
	})
        
	$("input").focusin(function(){
            $(this).siblings(".clean").css({"visibility":"visible","color":"#edb64e"})
	})
	
	$(".clean").click(function(){
            $(this).siblings("input").val("");
	})
        
	$("input").focusout(function(){
		$(this).siblings(".clean").css({"color":"#999"})
	})	
        


	$("#register").click(function(){
            $("#account_login").slideUp();
            $("#account_register").slideToggle();
	})
        
	$("#account_register .close,#account_register .back").click(function(){
            $("#account_register").slideUp();
	})	
                        
	$("#account_register .btns .btn_02").click(function(){
            var h = typeof(scrollHeight) == "undefined" ? 0 : scrollHeight;
            $('html,body').animate({
                scrollTop: h
            }, 600);
            $("#account_register").slideUp();
            $("#account_login").slideDown(1000);
	})
        
	$("#account_register .btns .btn_03").click(function(){
            var h = typeof(scrollHeight) == "undefined" ? 0 : scrollHeight;
            $('html,body').animate({
                scrollTop: h
            }, 600);
            $("#account_register").slideUp();
	})

	$("#personal").click(function(){
            $("#account_register form").hide().eq(1).show();
	})
	$("#company").click(function(){
            $("#account_register form").hide().eq(0).show();
	})
	
	function account_size(){
            var h = $(window).height();
            h = h - 82;
            $("#account_login").css('height',h);
            $("#account_register").css('height',h);
            if($("#account_login").height()>740){
                $("#account_login").children(".third_login").addClass("third_login_02")
            }else{
                $("#account_login").children(".third_login").removeClass("third_login_02")
            }
	}
	
	account_size();
	$(window).resize(function(){
            account_size();
	});
        
        $("#kh_code").blur(function(){
            checkusername(1);
	});
        $("#kh_code_p").blur(function(){
            checkusername(2);
	});
        
        $("#kh_email").blur(function(){
            checkSubmitEmail(1);
	});
        $("#kh_email_p").blur(function(){
            checkSubmitEmail(2);
	});
        
        $("#kh_login_pwd").blur(function(){
            checkPassword(1);
	});
        $("#kh_login_pwd_p").blur(function(){
            checkPassword(2);
	});
        $("#kh_login_pwd2").blur(function(){
            checksamePassword(1);
	});
        $("#kh_login_pwd2_p").blur(function(){
            checksamePassword(2);
	});
        $("#kh_name").blur(function(){
            checkkh_name(1);
	});
        $("#kh_name_p").blur(function(){
            checkkh_name(2);
	});
        
        $("#kh_itname").blur(function(){
            checkitname(1);
	});
        $("#kh_itname_p").blur(function(){
            checkitname(2);
	});
        $("#kh_itphone").blur(function(){
            checkSubmitMobil(1);
	});
        $("#kh_itphone_p").blur(function(){
            checkSubmitMobil(2);
	});
        
        $("#kh_address").blur(function(){
            //checkaddress();
	});
        $("#kh_tel").blur(function(){
            //checkkh_tel();
	});
        
        $("#is_agree").click(function(){
            checkagreement(1);
	});
        $("#is_agree_p").click(function(){
            checkagreement(2);
	});
        
        $("#kh_licence_num").blur(function(){
            checklicencenum(1);
	});
        $("#kh_licence_num_p").blur(function(){
            checklicencenum(2);
	});
        
        //绑定上传扫描件事件
        $("#upfileclick").click(function(){
            $("#upfile").click();
        });
        
        //绑定注册事件
        $("#enrol").click(function(){
            //有效性验证
            if(!checkusername(1)) return;
            if(!checkSubmitEmail(1)) return;
            if(!checkPassword(1)) return;
            if(!checksamePassword(1)) return;
            if(!checkkh_name(1)) return;
            if(!checkitname(1)) return;
            if(!checkSubmitMobil(1)) return;
            //if(!checkaddress()) return;
            //if(!checkkh_tel()) return;
            if(!checklicencenum(1)) return;
            if(!checklicencenumimg()) return;
            //提交注册
            var url = "?app_act=index/do_client_register";
            $.ajax({ type: 'POST', dataType: 'json',  
                url:url,
                data: {kh_code: $("#kh_code").val(),kh_email: $("#kh_email").val(),kh_login_pwd:$("#kh_login_pwd").val(),
                    kh_name:$("#kh_name").val(),kh_licence_num:$("#kh_licence_num").val(),kh_licence_img:$("#kh_licence_img").val(),
                    kh_itname:$("#kh_itname").val(),kh_itphone:$("#kh_itphone").val(),kh_address:$("#kh_address").val(),kh_tel:$("#kh_tel").val(),kh_account_type:0}, 
                success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        //注册成功
                        //自动登录
                        reglogin();
                        $("#account_register").slideUp();
                        $("#registered").show();
                        setTimeout(function(){$("#registered").hide();},3000);
                    } else {
                        if(ret.message.split(":")[0]=="1"){
                            $("#kh_codeMsg").html(ret.message.split(":")[1]); 
                            $("#kh_code").focus(); 
                        }
                        if(ret.message.split(":")[0]=="2"){
                            $("#kh_nameMsg").html(ret.message.split(":")[1]); 
                            $("#kh_name").focus(); 
                        }
                        if(ret.message.split(":")[0]=="3"){
                            $("#emailMsg").html(ret.message.split(":")[1]); 
                            $("#kh_email").focus(); 
                        }
                    }
                }
            });
        });
        
        //绑定注册事件—个人
        $("#enrol_p").click(function(){
            //有效性验证
            if(!checkusername(2)) return;
            if(!checkSubmitEmail(2)) return;
            if(!checkPassword(2)) return;
            if(!checksamePassword(2)) return;
            if(!checkkh_name(2)) return;
            if(!checkitname(2)) return;
            if(!checkSubmitMobil(2)) return;
            if(!checklicencenum(2)) return;
            if(!checkagreement(2)) return;
            //提交注册
            var url = "?app_act=index/do_client_register";
            $.ajax({ type: 'POST', dataType: 'json',  
                url:url,
                data: {kh_code: $("#kh_code_p").val(),kh_email: $("#kh_email_p").val(),kh_login_pwd:new Base64().encode('@@@'+$("#kh_login_pwd_p").val(),+'@@@'), kh_name:$("#kh_name_p").val(),kh_licence_num:$("#kh_licence_num_p").val(),kh_itname:$("#kh_itname_p").val(),
                    kh_itphone:$("#kh_itphone_p").val(),kh_account_type:1}, 
                success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        //注册成功
                        //自动登录
                        reglogin();
                        $("#account_register").slideUp();
                        $("#registered").show();
                        setTimeout(function(){$("#registered").hide();},3000);
                    } else {
                        if(ret.message.split(":")[0]=="1"){
                            $("#kh_codeMsg_p").html(ret.message.split(":")[1]); 
                            $("#kh_code_p").focus(); 
                        }
                        if(ret.message.split(":")[0]=="2"){
                            $("#kh_nameMsg_p").html(ret.message.split(":")[1]); 
                            $("#kh_name_p").focus(); 
                        }
                        if(ret.message.split(":")[0]=="3"){
                            $("#emailMsg_p").html(ret.message.split(":")[1]); 
                            $("#kh_email_p").focus(); 
                        }
                    }
                }
            });
        });
        
        //注册成功后登录操作
        function reglogin(){
            var kh_code="";
            var kh_login_pwd="";
            if($("#personalform").css('display')=="none"){
                kh_code=$("#kh_code").val();
                kh_login_pwd=$("#kh_login_pwd").val();
            }else{
                kh_code=$("#kh_code_p").val();
                kh_login_pwd=$("#kh_login_pwd_p").val();
            }
            $.ajax({type: "POST",dataType: 'json',      
                url: "?app_act=index/do_user_login",   
                data: {username: kh_code,userpwd: kh_login_pwd},
                success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success'){
                        //登录成功
                        $("#usercenter").removeClass("btns");
                        $("#usercenter").addClass("person_icon");
                        $("#usercenter").html("<a href=\"?app_act=mycenter/myself/self_info';\"><img src='assets/img/person_icon.png'></a>"
                                            +"<ul class='relate'>"
                                            +"    <li class='acc_set' onclick=\"location.href ='?app_act=mycenter/myself/self_info';\">个人中心</li>"
                                            +"    <li class='exit' onclick=\"location.href ='?app_act=index/do_logout';\">退出</li>"
                                            +"    <i><img src='assets/img/relate_icon.png' width='10' height='6'></i>"
                                            +"</ul>");
                    }
                }
            });
        }
        
        //jquery验证用户名
        function checkusername(type){ 
            var inputfiled;
            var inputfiledMsg;
            if(type==1){
                inputfiled=$("#kh_code");
                inputfiledMsg=$("#kh_codeMsg");
            }else{
                inputfiled=$("#kh_code_p");
                inputfiledMsg=$("#kh_codeMsg_p");
            }
            if(inputfiled.val()==""){ 
                inputfiledMsg.html("用户名不能为空！"); 
                inputfiled.focus(); 
                return false; 
            }else{
                $.ajax({
                 type: "POST",
                 dataType: 'json',      
                 url: "?app_act=index/check_kh_code",   
                 data: {kh_code: inputfiled.val()},
                 success: function(ret) {
                    if(ret.status == "-1"){
                        inputfiledMsg.html("用户名已存在！");
                        inputfiled.focus();
                        return false;
                    }
                }
            });
            }
            inputfiledMsg.html(""); 
            return true;
        }

    //jquery验证邮箱 
    function checkSubmitEmail(type){ 
        var inputfiled;
        var inputfiledMsg;
        if(type==1){
            inputfiled=$("#kh_email");
            inputfiledMsg=$("#emailMsg");
        }else{
            inputfiled=$("#kh_email_p");
            inputfiledMsg=$("#emailMsg_p");
        }
        if(inputfiled.val()==""){ 
            inputfiledMsg.html("邮箱地址不能为空！"); 
            inputfiled.focus(); 
            return false; 
        } 
        if(!inputfiled.val().match(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/)){ 
            inputfiledMsg.html("邮箱格式不正确！请重新输入！"); 
            inputfiled.focus(); 
            return false; 
        } 
        inputfiledMsg.html(""); 
        return true; 
    } 
    
    //jquery验证手机号码 
    function checkSubmitMobil(type){ 
        var inputfiled;
        var inputfiledMsg;
        if(type==1){
            inputfiled=$("#kh_itphone");
            inputfiledMsg=$("#moileMsg");
        }else{
            inputfiled=$("#kh_itphone_p");
            inputfiledMsg=$("#moileMsg_p");
        }
       if(inputfiled.val()==""){ 
            inputfiledMsg.html("手机号码不能为空！"); 
            inputfiled.focus(); 
            return false; 
        } 
        if(!inputfiled.val().match(/^1\d{10}$/)){
            inputfiledMsg.html("手机号码格式不正确！请重新输入！"); 
            inputfiled.focus(); 
            return false; 
        }
        inputfiledMsg.html("");
        return true; 
    }
    
    //jquery验证密码
    function checkPassword(type){ 
        var inputfiled;
        var inputfiledMsg;
        if(type==1){
            inputfiled=$("#kh_login_pwd");
            inputfiledMsg=$("#pwdMsg");
        }else{
            inputfiled=$("#kh_login_pwd_p");
            inputfiledMsg=$("#pwdMsg_p");
        }
        if(inputfiled.val()==""){ 
            inputfiledMsg.html("密码不能为空！"); 
            inputfiled.focus(); 
            return false; 
        } 
        if(inputfiled.val().length < 6){ 
            inputfiledMsg.html("密码最少6个字符哦!"); 
            inputfiled.focus(); 
            return false; 
        }
        var valid = RegExp(/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/).test(inputfiled.val());
        if (!valid) {
            inputfiledMsg.html("密码长度为8-20位，包含数字、大写字母、小写字母和特殊符号!"); 
            inputfiled.focus(); 
            return false; 
        }
        inputfiledMsg.html("");
        return true; 
    }
    
    
    //jquery验证密码是否一致
    function checksamePassword(type){ 
        var inputfiled;
        var inputfiled2;
        var inputfiledMsg;
        if(type==1){
            inputfiled=$("#kh_login_pwd2");
            inputfiled2=$("#kh_login_pwd");
            inputfiledMsg=$("#pwdMsg2");
        }else{
            inputfiled=$("#kh_login_pwd2_p");
            inputfiled2=$("#kh_login_pwd_p");
            inputfiledMsg=$("#pwdMsg2_p");
        }
        if(inputfiled.val()==""){ 
            inputfiledMsg.html("确认密码不能为空！"); 
            inputfiled.focus(); 
            return false; 
        } 
        var pwd1 = inputfiled.val();
        var pwd2 = inputfiled2.val();
        if (pwd1 != pwd2) {
            inputfiled.focus(); 
            inputfiledMsg.html("两次输入密码不一致，请重新输入。");
            return false;
        }
        inputfiledMsg.html("");
        return true; 
    }
    
    //jquery验证公司名称
    function checkkh_name(type){ 
        var inputfiled;
        var inputfiledMsg;
        if(type==1){
            inputfiled=$("#kh_name");
            inputfiledMsg=$("#kh_nameMsg");
        }else{
            inputfiled=$("#kh_name_p");
            inputfiledMsg=$("#kh_nameMsg_p");
        }
        if(inputfiled.val()==""){ 
            if(type==1) 
                inputfiledMsg.html("公司名称不能为空！"); 
            else
                inputfiledMsg.html("姓名不能为空！"); 
            inputfiled.focus(); 
            return false; 
        }else{
             $.ajax({
                 type: "POST",
                 dataType: 'json',      
                 url: "?app_act=index/check_kh_name",   
                 data: {kh_name: inputfiled.val()},
                 success: function(ret) {
                    if(ret.status == "-1"){
                        inputfiledMsg.html("公司名称已存在！");
                        inputfiled.focus();
                        return false;
                    }
                }
            });
        }
        inputfiledMsg.html(""); 
        return true;
    }
    
    //jquery验证联系人
    function checkitname(type){ 
        var inputfiled;
        var inputfiledMsg;
        if(type==1){
            inputfiled=$("#kh_itname");
            inputfiledMsg=$("#kh_itnameMsg");
        }else{
            inputfiled=$("#kh_itname_p");
            inputfiledMsg=$("#kh_itnameMsg_p");
        }
       if(inputfiled.val()==""){ 
            inputfiledMsg.html("联系人不能为空！"); 
            inputfiled.focus(); 
            return false; 
        }
        inputfiledMsg.html(""); 
        return true;
    }
    
    //企业营业执照号或者个人身份证验证必填
    function checklicencenum(type){ 
        if(type==1){ //表示企业营业执照号
            if($("#kh_licence_num").val()==""){ 
                $("#kh_licence_numMsg").html("营业执照号不能为空！"); 
                $("#kh_licence_num").focus(); 
                return false; 
            }else{
                $.ajax({
                 type: "POST",
                 dataType: 'json',      
                 url: "?app_act=index/check_licence_num",   
                 data: {kh_licence_num: $("#kh_licence_num").val()},
                 success: function(ret) {
                    if(ret.status == "-1"){
                        $("#kh_licence_numMsg").html("营业执照号已存在！");
                        inputfiled.focus();
                        return false;
                    }
                }
            });
            }
            $("#kh_licence_numMsg").html(""); 
            return true;
        }else{
            if($("#kh_licence_num_p").val()==""){ 
                $("#kh_licence_numMsg_p").html("身份证不能为空！"); 
                $("#kh_licence_num_p").focus(); 
                return false; 
            }
            var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;  
            if(reg.test($("#kh_licence_num_p").val()) === false)  
            {  
                $("#kh_licence_numMsg_p").html("身份证输入不合法！"); 
                $("#kh_licence_num_p").focus(); 
                return false;  
            } 
            $("#kh_licence_numMsg_p").html(""); 
            return true;
        }
    }
    
    //企业营业执照扫描件验证
    function checklicencenumimg(){
        if($("#kh_licence_img").val()==""){ 
            $("#kh_licence_imgMsg").html("扫描件不能为空"); 
            //$("#kh_licence_img").focus(); 
            return false; 
        }
        $("#kh_licence_imgMsg").html(""); 
        return true;
    }
	
     //jquery验证客户地址
    function checkaddress(){ 
       if($("#kh_address").val()==""){ 
            $("#kh_addressMsg").html("客户地址不能为空！"); 
            $("#kh_address").focus(); 
            return false; 
        }
        $("#kh_addressMsg").html(""); 
        return true;
    }
	
	
    //jquery验证客户电话
    function checkkh_tel(){ 
       if($("#kh_tel").val()==""){ 
            $("#kh_telMsg").html("公司电话不能为空！"); 
            $("#kh_tel").focus(); 
            return false; 
        }
        $("#kh_telMsg").html(""); 
        return true;
    }
    
    //jquery验证用户协议
    function checkagreement(type){
        var inputfiled;
        var inputenrol;
        if(type==1){
            inputfiled=$("#is_agree");
            inputenrol=$("#enrol");
        }else{
            inputfiled=$("#is_agree_p");
            inputenrol=$("#enrol_p");
        }
        inputenrol.attr("disabled","disabled");
        if(!inputfiled.attr("checked")){ //DOM方式判断
            inputenrol.attr("disabled","disabled");
            return false;
        }else{
            inputenrol.removeAttr("disabled");
            return true;
        }
    }
    

    $("#username").blur(function(){
        check_userlogin();
    });
    $("#userpwd").blur(function(){
        check_userpwd();
    });
    
    //登录相关默认
    if($("#loginsum").val()!=""){
        if(parseInt($("#loginsum").val())>=3){
            $("#capdiv").show();
        }
    }else{
        $("#loginsum").val("0");
    }
    
    //绑定登录事件
    $("#userlogin").click(function(){
        if(!check_userlogin()) return;
        if(!check_userpwd()) return;
        var captcha=$.trim($("#captcha").val());
        if(parseInt($("#loginsum").val())>=3){
            if(captcha==""){
                $("#captchaMsg").html("验证码不能为空！"); 
                $("#captcha").focus(); 
                return false;
            }
        }
        var captcha=$.trim($("#captcha").val());
        $.ajax({type: "POST",dataType: 'json',      
            url: "?app_act=index/do_user_login",   
            data: {username: $("#username").val(),userpwd: new Base64().encode('@@@'+$("#userpwd").val()+'@@@'),remb:isRemember(),captcha:captcha},
            success: function(ret) {
                if(ret.status == "-1"){
                    if($("#loginsum").val()!=""){
                        $("#loginsum").val(parseInt($("#loginsum").val())+1);
                    }else{
                        $("#loginsum").val("1");
                    }
                    if(parseInt($("#loginsum").val())>=3){
                        $("#capdiv").show();
                    }
                }
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success'){
                    //登录成功
                    location.href='?app_act=mycenter/myself/self_info';
                    $("#account_login").slideUp();
                    $("#usercenter").removeClass("btns");
                    $("#usercenter").addClass("person_icon");
                    $("#usercenter").html("<a href=\"?app_act=mycenter/myself/self_info';\"><img src='assets/img/person_icon.png'></a>"
                                        +"<ul class='relate'>"
                                        +"    <li class='acc_set' onclick=\"location.href ='?app_act=mycenter/myself/self_info';\">个人中心</li>"
                                        +"    <li class='exit' onclick=\"location.href ='?app_act=index/do_logout';\">退出</li>"
                                        +"    <i><img src='assets/img/relate_icon.png' width='10' height='6'></i>"
                                        +"</ul>");
                }else{
                    if(ret.message=="CaptchaError"){
                        $("#captchaMsg").html("验证码错误"); 
                        $("#userpwd").val(''); 
                        $("#userpwd").focus(); 
                    }else{
                        $("#userpwdMsg").html("用户名或密码错误");
                        $("#captchaMsg").html(""); 
                        $("#userpwd").val(''); 
                        $("#userpwd").focus(); 
                    }
                }
            }
        });
    });
    
    //绑定enter
    $(document).bind('keydown', function (e) {
        var key = e.which;
        if (key == 13) {
            $('#userlogin').click();
        }
    });
    
    //是否记住用户名密码
    function isRemember(){
        /*if($("#remb").is(":checked")){
            return $("#remb:checked").attr('value');
        }
        return 0;	
        */
        if(!$("#remb").attr("checked")){
            return 0;
        }
        return 1;
    }
    
    //jquery验证客户登录用户名
    function check_userlogin(){ 
       if($("#username").val()==""){ 
            $("#usernameMsg").html("用户名不能为空！"); 
            $("#username").focus(); 
            return false; 
        }
        $("#usernameMsg").html(""); 
        return true;
    }
    //jquery验证客户登录密码
    function check_userpwd(){ 
       if($("#userpwd").val()==""){ 
            $("#userpwdMsg").html("密码不能为空！"); 
            $("#userpwd").focus(); 
            return false; 
        }
        $("#userpwdMsg").html(""); 
        return true;
    }
});

//执行上传操作
function upfilechange(obj){
    var id = jQuery(obj).attr('id');
    //var url = '?app_act=common/upload_img/upfile&path=' + path + "&id=" + id;
    var url = "?app_act=index/uploadlicenceimg";
    jQuery.ajaxFileUpload({
        url: url,
        secureuri: false,
        fileElementId: id,
        dataType: 'json',
        success: function(data,status){
            var type = data.status == 1 ? 'success' : 'error';
            if (type == 'success'){
                $(".scan_pic").html("<img src='"+data.data['imgpath']+"' width='100%' height='100%'></i>");
                $("#kh_licence_img").val(data.data['imgpath']);
                $("#kh_licence_imgMsg").html(""); 
            }else{
                $("#kh_licence_img").val('');
                $("#kh_licence_imgMsg").html(data.message); 
            }
        },
        error: function(data,status,e){
            
        }
    });
}

//订购验证登录状态
function order_loginstate(){
    $.ajax({type: "POST",dataType: 'json',      
            url: "?app_act=product/soonbuy/check_user_info",   
            data: {},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success'){
                    window.location.href="?app_act=product/soonorder/show_order";
                }else{
                     $("#account_login").slideDown(600);
                }
            }
    });
}