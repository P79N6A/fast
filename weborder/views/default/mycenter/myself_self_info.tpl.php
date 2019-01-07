<div class="order_wrap">
    <?php include get_tpl_path('top')?>
    <div class="person_wrap">
    	<div class="person">
            <div class="sidebar">
            	<p class="person_pic"><img src="assets/img/person_pic.png"></p>
                <p class="person_name"><?php echo $response['data']['kh_name'] ?></p>
                <ul class="person_options" id="person_options">
                    <li class="li_01 curr">账号信息</li>
                    <li class="li_02"><a href='?app_act=mycenter/myself/order_info'>我的订单</a></li>
                    <li class="li_03"><a href="?app_act=mycenter/myself/receipt_info"/>发票信息</li>
                </ul>
            </div>
            <div class="content" style="display:block;">
            	<div class="zhxx">
                    <div class="jbxx_tt"><strong>基本信息</strong><a id="edit" href="javascript:void(0)">编辑</a><a id="abolish" class="cancel" href="javascript:void(0)">取消</a></div>
                    <input type="hidden" value="<?php echo $response['data']['kh_id'] ?>" id="kh_id" />
                    <input type="hidden" value="<?php echo $response['data']['kh_account_type'] ?>" id="kh_account_type" />
                    <input type="hidden" value="<?php echo $response['data']['kh_email'] ?>" id="hd_kh_email" />
                    <input type="hidden" value="<?php echo $response['data']['kh_itname'] ?>" id="hd_kh_itname" />
                    <input type="hidden" value="<?php echo $response['data']['kh_itphone'] ?>" id="hd_kh_itphone" />
                    <input type="hidden" value="<?php echo $response['data']['kh_address'] ?>" id="hd_kh_address" />
                    <input type="hidden" value="<?php echo $response['data']['kh_tel'] ?>" id="hd_kh_tel" />
                    <input type="hidden" value="<?php echo $response['data']['kh_licence_num'] ?>" id="hd_kh_licence_num" />
                    <?php if($response['data']['kh_account_type']!='1') {?>
                    <div class="jbxx">
                        <p class="p_input">
                            <label>登录名：</label>
                            <input id="kh_code" type="text" disabled="disabled" value="<?php echo $response['data']['kh_code'] ?>">
                        </p>
                        <p class="p_input">
                            <label>公司名称：</label>
                            <input id="kh_name" type="text" disabled="disabled" value="<?php echo $response['data']['kh_name'] ?>">
                        </p>
                        <p class="p_input">
                            <label>公司Email：</label>
                            <input id="kh_email" class="c_edit" type="text" disabled="disabled" value="<?php echo $response['data']['kh_email'] ?>">
                            <span class="prompt">请填写您常用的邮箱</span>
                            <span class="remand" id="emailMsg"></span>
                        </p>
                        <p class="p_input">
                            <label>营业执照号：</label>
                            <input id="kh_licence_num" type="text" disabled="disabled" value="<?php echo $response['data']['kh_licences_num'] ?>">
                            <!--span class="prompt">请确保您的营业执照号填写正确</span-->
                        </p>
                        <p class="p_input">
                            <label>扫描件：</label>
                            <?php if(empty($response['data']['kh_licence_img'])) {?>
                                <span class="scan_pic">暂无图片</span>
                            <?php } else {?> 
                                <img src='<?php echo $response['data']['kh_licence_img'] ?>' width='118px' height='118px'>
                            <?php } ?>
                            <a class="xztp" href="javascript:void(0)">选择图片</a>
                        </p>
                        <p class="p_input">
                            <label>联系人：</label>
                            <input id="kh_itname" class="c_edit" type="text" disabled="disabled" value="<?php echo $response['data']['kh_itname'] ?>">
                            <span id="kh_itnameMsg" class="remand"></span>
                        </p>
                        <p class="p_input">
                            <label>联系人电话：</label>
                            <input id="kh_itphone" class="c_edit"  type="text" disabled="disabled" value="<?php echo $response['data']['kh_itphone'] ?>">
                            <span id="moileMsg" class="remand"></span>
                        </p>
                        <p class="p_input">
                            <label>公司地址：</label>
                            <input id="kh_address" class="c_edit" type="text" disabled="disabled" value="<?php echo $response['data']['kh_address'] ?>">
                            <span class="prompt">请尽可能的填写您公司的详细地址</span>
                        </p>
                        <p class="p_input">
                            <label>公司电话：</label>
                            <input id="kh_tel" class="c_edit" type="text" disabled="disabled" value="<?php echo $response['data']['kh_tel'] ?>">
                        </p>
                    </div>
                    <?php }else {?>
                    <div class="jbxx">
                        <p class="p_input">
                            <label>登录名：</label>
                            <input id="kh_code" type="text" disabled="disabled" value="<?php echo $response['data']['kh_code'] ?>">
                        </p>
                        <p class="p_input">
                            <label>真实姓名：</label>
                            <input id="kh_name" type="text" disabled="disabled" value="<?php echo $response['data']['kh_name'] ?>">
                        </p>
                        <p class="p_input">
                            <label>Email：</label>
                            <input id="kh_email" class="c_edit" type="text" disabled="disabled" value="<?php echo $response['data']['kh_email'] ?>">
                            <span class="prompt">请填写您常用的邮箱</span>
                            <span class="remand" id="emailMsg"></span>
                        </p>
                        <p class="p_input">
                            <label>身份证号：</label>
                            <input id="kh_licence_num" class="c_edit" type="text" disabled="disabled" value="<?php echo $response['data']['kh_licence_num'] ?>">
                            <span class="prompt">请确保您的身份证号填写正确</span>
                            <span class="remand" id="kh_licence_numMsg"></span>
                        </p>
                        <p class="p_input">
                            <label>联系人：</label>
                            <input id="kh_itname" class="c_edit" type="text" disabled="disabled" value="<?php echo $response['data']['kh_itname'] ?>">
                            <span id="kh_itnameMsg" class="remand"></span>
                        </p>
                        <p class="p_input">
                            <label>联系人电话：</label>
                            <input id="kh_itphone" class="c_edit"  type="text" disabled="disabled" value="<?php echo $response['data']['kh_itphone'] ?>">
                            <span id="moileMsg" class="remand"></span>
                        </p>
                    </div>
                    <?php }?>
                <div class="zhaq_tt"><strong>账户安全</strong><a id="modify" href="javascript:void(0)">修改密码</a><a id="abrogate" class="cancel" href="javascript:void(0)">取消</a></div>
                <div class="zhaq">
                    <p class="p_input p_input_01">
                    	<label>密码：</label>
                        <input type="password" disabled="disabled" value="************">
                    </p>
                    <p class="p_input p_input_02">
                    	<label>当前密码：</label>
                        <input id="old_user_pwd" type="password" value="">
                        <span class="prompt">请正确输入当前密码</span>
                        <span class="remand" id="pwdMsg"></span>
                    </p>
                    <p class="p_input p_input_02">
                    	<label>新密码：</label>
                        <input id="new_user_pwd" type="password" value="">
                        <span class="prompt">新密码必须与当前密码不同</span>
                        <span class="remand" id="pwdMsg_new"></span>
                    </p>
                    <p class="p_input p_input_02">
                    	<label>确认新密码：</label>
                        <input id="new_user_pwd2" type="password" value="">
                        <span class="prompt">两次输入新密码必须相同</span>
                        <span class="remand" id="pwdMsg_new2"></span>
                    </p>
                </div>
            </div>
        </div>
        <!--保存成功提醒-->
        <div class="registered" id="registered">
            <img src="assets/img/registered.png" width="30" height="30">保存成功。
        </div>
    </div>
    </div>
    <div class="order_bottom">
    	<p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
    </div>
</div>
<script>
$(function(){
	$("#edit").click(function(){
		if($(this).html()=="编辑"){
                    $(this).html("保存").addClass("keep");
                    $(this).bind('myclick',function(){
                        var kh_address="";
                        var kh_tel="";
                        var kh_account_type=$("#kh_account_type").val();
                        if(kh_account_type!="1"){
                            kh_address= $("#kh_address").val();
                            kh_tel= $("#kh_tel").val();
                        }
                        $.ajax({type: "POST",dataType: 'json',      
                            url: "?app_act=mycenter/myself/edit_client",   
                            data: {kh_id: $("#kh_id").val(),
                                kh_email:$("#kh_email").val(),
                                kh_licence_num:$("#kh_licence_num").val(),
                                kh_itname:$("#kh_itname").val(),
                                kh_itphone:$("#kh_itphone").val(),
                                kh_address:kh_address,
                                kh_tel:kh_tel},
                            success: function(ret) {
                                var type = ret.status == 1 ? 'success' : 'error';
                                if (type == 'success'){
                                    $("#hd_kh_email").val($("#kh_email").val());
                                    $("#hd_kh_licence_num").val($("#kh_licence_num").val());
                                    $("#hd_kh_itname").val($("#kh_itname").val());
                                    $("#hd_kh_itphone").val($("#kh_itphone").val());
                                    $("#hd_kh_address").val($("#kh_address").val());
                                    $("#hd_kh_tel").val($("#kh_tel").val());
                                    $("#registered").show();
                                    setTimeout(function(){$("#registered").hide();},2000);
                                }else{

                                }
                            }
                        });
                    });
		}else{
                    if(!checkSubmitEmail()) return;
                    if(!checkitname()) return;
                    if(!checkSubmitMobil()) return;
                    var kh_account_type=$("#kh_account_type").val();
                    if(kh_account_type =="1"){
                        if(!checklicencenum()) return;
                    }
                    $(this).trigger("myclick");
                    $(this).unbind("myclick");
                    $(this).html("编辑").removeClass("keep");
		}
		$(".jbxx .c_edit").toggleClass("edit_state");
		if($(".jbxx .c_edit").attr("disabled")){
                    $(".jbxx .c_edit").removeAttr("disabled")
		}else{
                    $(".jbxx .c_edit").attr("disabled","disabled")
		}
		//$(".jbxx .p_input .prompt,.jbxx .p_input .xztp").toggle();
                $(".jbxx .p_input .prompt").toggle();
		$(this).siblings(".cancel").toggle();				
	});
		
	$("#abolish").click(function(){
                $("#kh_email").val($("#hd_kh_email").val());
                $("#kh_licence_num").val($("#hd_kh_licence_num").val());
                $("#kh_itname").val($("#hd_kh_itname").val());
                $("#kh_itphone").val($("#hd_kh_itphone").val());
                $("#kh_address").val($("#hd_kh_address").val());
                $("#kh_tel").val($("#hd_kh_tel").val());
                $(".jbxx .remand").html("");
		$("#edit").html("编辑").removeClass("keep");
		$(".jbxx .c_edit").toggleClass("edit_state");
		if($(".jbxx .c_edit").attr("disabled")){
                    $(".jbxx .c_edit").removeAttr("disabled")
		}else{
                    $(".jbxx .c_edit").attr("disabled","disabled")
		}
		//$(".jbxx .p_input .prompt,.jbxx .p_input .xztp").toggle();
                $(".jbxx .p_input .prompt").toggle();
		$(".jbxx .p_input").toggleClass("p_input_nohover");
		$(this).hide();
	});
        
		
	$("#modify").click(function(){
		if($(this).html()=="修改密码"){
                    $(this).html("保存").addClass("keep");
                    $(".zhaq .p_input_01,.zhaq .p_input_02").toggle();
                    $(".zhaq .p_input .prompt").toggle();
                    $(this).siblings(".cancel").toggle();	
		}else{
                    if(!checkPassword()) return;
                    if(!checknew_user_pwd()) return;
                    if(!checknew_user_pwd2()) return;
                    $.ajax({type: "POST",dataType: 'json',      
                        url: "?app_act=mycenter/myself/do_chgpasswd",   
                        data: {kh_id: $("#kh_id").val(),
                            old_user_pwd:$("#old_user_pwd").val(),
                            new_user_pwd:$("#new_user_pwd").val(),},
                        success: function(ret) {
                            var type = ret.status == 1 ? 'success' : 'error';
                            if (type == 'success'){
                                $("#old_user_pwd").val("");
                                $("#new_user_pwd").val("");
                                $("#new_user_pwd2").val("");
                                $("#modify").html("修改密码").removeClass("keep");
                                $(".zhaq .p_input_01,.zhaq .p_input_02").toggle();
                                $(".zhaq .p_input .prompt").toggle();
                                $("#modify").siblings(".cancel").toggle();	
                                $("#registered").show();
                                setTimeout(function(){$("#registered").hide();},2000);
                            }else{
                                $("#pwdMsg").html("原密码错误"); 
                                $("#old_user_pwd").focus(); 
                            }
                        }
                    });
		}	
	});
        		
	$("#abrogate").click(function(){
                $(".zhaq .remand").html("");
                $("#old_user_pwd").val("");
                $("#new_user_pwd").val("");
                $("#new_user_pwd2").val("");
		$("#modify").html("修改密码").removeClass("keep");
		$(".zhaq .p_input_01,.zhaq .p_input_02").toggle();
		$(".zhaq .p_input .prompt").toggle();
		$(this).hide();
	});	
        
        $("#kh_email").blur(function(){
            checkSubmitEmail();
	});
        //jquery验证邮箱 
        function checkSubmitEmail(){ 
            var inputfiled;
            var inputfiledMsg;
            inputfiled=$("#kh_email");
            inputfiledMsg=$("#emailMsg");
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
        $("#kh_itname").blur(function(){
            checkitname();
	});
        //jquery验证联系人
        function checkitname(){ 
            var inputfiled;
            var inputfiledMsg;
            inputfiled=$("#kh_itname");
            inputfiledMsg=$("#kh_itnameMsg");
            if(inputfiled.val()==""){ 
                inputfiledMsg.html("联系人不能为空！"); 
                inputfiled.focus(); 
                return false; 
            }
            inputfiledMsg.html(""); 
            return true;
        }
        
        $("#kh_itphone").blur(function(){
            checkSubmitMobil();
	});
        //jquery验证手机号码 
        function checkSubmitMobil(){ 
            var inputfiled;
            var inputfiledMsg;
            inputfiled=$("#kh_itphone");
            inputfiledMsg=$("#moileMsg");
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
        
        $("#kh_licence_num").blur(function(){
            checklicencenum();
	});
        //个人身份证验证必填
        function checklicencenum(){ 
            if($("#kh_licence_num").val()==""){ 
                $("#kh_licence_numMsg").html("身份证不能为空！"); 
                $("#kh_licence_num").focus(); 
                return false; 
            }
            var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;  
            if(reg.test($("#kh_licence_num").val()) === false)  
            {  
                $("#kh_licence_numMsg").html("身份证输入不合法！"); 
                $("#kh_licence_num").focus(); 
                return false;  
            } 
            $("#kh_licence_numMsg").html(""); 
            return true;
        }
        
        $("#old_user_pwd").blur(function(){
            checkPassword();
	});
        //jquery验证密码
        function checkPassword(){ 
            var inputfiled;
            var inputfiledMsg;
            inputfiled=$("#old_user_pwd");
            inputfiledMsg=$("#pwdMsg");
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
            inputfiledMsg.html("");
            return true; 
        }
        
        $("#new_user_pwd").blur(function(){
            checknew_user_pwd();
	});
        function checknew_user_pwd(){ 
            var inputfiled;
            var inputfiledMsg;
            inputfiled=$("#new_user_pwd");
            inputfiledMsg=$("#pwdMsg_new");
            if(inputfiled.val()==""){ 
                inputfiledMsg.html("新密码不能为空！"); 
                inputfiled.focus(); 
                return false; 
            } 
            if(inputfiled.val().length < 6){ 
                inputfiledMsg.html("新密码最少6个字符哦!"); 
                inputfiled.focus(); 
                return false; 
            }
            if(inputfiled.val() ==$("#old_user_pwd").val()){ 
                inputfiledMsg.html("新密码不能与原密码相同"); 
                inputfiled.focus(); 
                return false; 
            }
            inputfiledMsg.html("");
            return true; 
        }
        $("#new_user_pwd2").blur(function(){
            checknew_user_pwd2();
	});
        function checknew_user_pwd2(){
            var inputfiled;
            var inputfiledMsg;
            inputfiled=$("#new_user_pwd2");
            inputfiledMsg=$("#pwdMsg_new2");
            if(inputfiled.val()==""){ 
                inputfiledMsg.html("确认密码不能为空！"); 
                inputfiled.focus(); 
                return false; 
            }
            if (inputfiled.val() !=$("#new_user_pwd").val()) {
                inputfiled.focus(); 
                inputfiledMsg.html("两次输入密码不一致，请重新输入。");
                return false;
            }
            inputfiledMsg.html("");
            return true; 
        }
});
</script>    

