<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>eFAST365</title>
        ﻿<?php echo load_js('jquery-1.8.1.min.js'); ?>
        <?php echo load_js('core.min.js'); ?>
        <link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
        <style>
            /*reset*/
            body,div,p,h1,h2,h3,h4,h5,h6,a,ul,li,ol,span,img,input,marquee{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
            li{ list-style:none;}
            /*a,a:hover,a:focus,a:active{ text-decoration:none;}*/
            input,img{ border:none;}

            .login_blue{ width:100%; height:100%; position:absolute; left:0; top:0; background:#FFF; overflow:hidden;}
            .login_blue .top{ width:980px; margin:0 auto; padding:20px 0; line-height:32px; height:45px;}
            .login_blue .top .contact,.login_blue .top .vertical{ float:right; font-size:12px; color:#999; vertical-align:top; margin:0 10px;}
            .login_blue .top .online:hover,.login_blue .top .help:hover{ text-decoration:underline;}
            .login_blue .top .contact i{ display:inline-block; width:26px; height:26px; background:url(assets/img/login_blue/contact_icon.png) no-repeat; vertical-align:middle; margin-right:5px;}
            .login_blue .top .telephone i{ background-position:5px 0;}
            .login_blue .top .online i{ background-position:-150px 0;}
            .login_blue .top .help i{ background-position:-277px 0;}
            .login_blue .banner{ position:relative;background:#1695ca;}
            .login_blue .banner .content{ width:51%; height:85%; position:absolute; left:12%; top:8%; min-width:650px;}
            .login_blue .banner .content .leftpic{ display:block; float:left; position:relative; left:-150px;}
            .login_blue .banner .content .rightlogin{ width:60%; height:100%; text-align:center; position:absolute; top:-10px; right:0px; min-width:278px;}
            .login_blue .banner .content .rightlogin .loginform{ padding:6%; background:#FFF; border-radius:3px; margin-top:5px; overflow:hidden;}
            .login_blue .banner .content .rightlogin .loginform .p_input{ margin-bottom:1%;}
            .login_blue .banner .content .rightlogin .loginform .p_input input{ font-size:14px; width:92%; padding:2% 0 2% 1%; border:2px solid #d6d6d6; border-radius:3px; color:#333; }
            .login_blue .banner .content .rightlogin .loginform .p_input input:focus{ border-color:#2d9fcf;}
            .login_blue .banner .content .rightlogin .loginform .p_input input.error{ border-color:#f74d4d;}
            .login_blue .banner .content .rightlogin .loginform .p_input .company{background-position:4% 5px;}
            .login_blue .banner .content .rightlogin .loginform .p_input .username{background-position:4% -43px;}
            .login_blue .banner .content .rightlogin .loginform .p_input .password{background-position:4% -92px;}
            .login_blue .banner .content .rightlogin .loginform .p_captchas{ overflow:hidden;}
            .login_blue .banner .content .rightlogin .loginform .p_captchas .captchas{ width:44%; float:left; background-position:9% -142px;margin-left: 5px}
            .login_blue .banner .content .rightlogin .loginform .p_captchas .captchas_obtain{ display:inline-block; width:47%; padding:2% 0 1.5%; border:2px solid #d6d6d6; border-radius:3px; color:#333; vertical-align:top;float:left;}
            .login_blue .banner .content .rightlogin .loginform .p_captchas .captchas_obtain img{ vertical-align:middle; margin-right:10%;}
            .login_blue .banner .content .rightlogin .loginform .p_captchas .captchas_obtain .refresh{ display:inline-block; width:16px; height:14px; background:url(assets/img/login_blue/refresh.png) no-repeat; vertical-align:middle; cursor:pointer;}
            .login_blue .banner .content .rightlogin .loginform .p_keep{text-align:left; margin-bottom:3.5%;}
            .login_blue .banner .content .rightlogin .loginform .p_keep .keep{ color:#666; font-size:14px; display:inline-block; vertical-align:top; height:20px; cursor:pointer;}
            .login_blue .banner .content .rightlogin .loginform .p_keep .keep .icon{ display:inline-block; width:20px; height:20px; background:url(assets/img/login_blue/keep.png) no-repeat; margin-right:5px;}
            .login_blue .banner .content .rightlogin .loginform .p_keep .keep .icon.active{ background-position:0 -30px;}
            .login_blue .banner .content .rightlogin .loginform .loginbtn{ width:95%; font-size:20px; padding:3% 0; color:#FFF; background:#f4bf3e; border:none; border-radius:3px; cursor:pointer; margin-bottom:3.5%;float:left;margin-left: 4px}
            .login_blue .banner .content .rightlogin .loginform .loginbtn:hover{box-shadow:0 0 5px #ccc;}
            .login_blue .bottom{ text-align:center; padding-top:4%; position:relative;}
            .login_blue .bottom .part4-1{position:absolute; left:0; top:0; width: 100%;}
            .login_blue .bottom .part4-film{width: 100%;height: 60px;background: transparent url("assets/img/login_blue/part4_film.png") no-repeat scroll 50% 0px;position:absolute; left:0; top:0; margin: -34px auto 0px;}
            .login_blue .bottom .regards{ color:#666; padding-top:3%; font-size:18px;}


            #personal,#company {width: 48.5%;height: 40px; border-radius:3px;font-size:18px;border-radius:3px;border:2px solid #d6d6d6}
            .province,.city,.district{width: 31.5%; border: 2px solid #d6d6d6; height: 35px}
        </style>
        <link href="assets/css/ripple.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="login_blue">
            <div class="top">
                <img width="116" height="32" src="assets/img/login_blue/efast5_logo_blue.png">
                <a class="contact help" href="javascript:void(0)"><i></i>帮助</a><span class="vertical">|</span><a class="contact online" href="http://wpa.qq.com/msgrd?v=3&amp;uin=2990598394&amp;site=qq&amp;menu=yes" target="_blank"><i></i>在线客服</a><span class="contact telephone"><i></i>400-600-9585</span>
            </div>
            <div class="banner" style="background:#1695ca">
                <img src="assets/img/login_blue/login_blue_banner.jpg" width="100%" style="min-height:800px">
                <div class="content">
                    <div class="rightlogin" style=" margin-bottom:3%;">        
                        <form class="loginform" onsubmit="return false;" id="personal_from">
                            <div class="button-group" style="margin-bottom: 3.5%;width: 97%;height: 40px;">
                                <button class="button" name = 'personal' id="personal">个人注册</button>
                                <button class="button" name = 'company' id="company">公司注册</button>
                            </div>
                            <p class="p_input"><input class="username"  id="user_code"  placeholder="登录名"><span style="color:red;"> *</span></p>
                            <p class="p_input"><input class="password" id="password" type="password"  placeholder="密码"><span style="color:red;"> * </span></p>
                            <!--<p class="p_input"><input class="company" id="company_name" type="text"   placeholder="公司名称" value="" /></p>-->
                            <p class="p_input">
                            <div style=" margin-right: 3%;margin-bottom:1%;">
                                <select class="province" id="province1" name="province" data-rules="{required : true}">
                                    <option value ="">请选择省份</option>
                                    <?php foreach ($response['area']['province'] as $k => $v) { ?>
                                        <option  value ="<?php echo $v['id']; ?>"  ><?php echo $v['name']; ?></option>
                                    <?php } ?>
                                </select>
                                <select class="city" id="city1" name="city" data-rules="{required : true}"></select>
                                <select class="district" id="district1" name="district" style=" margin-left: 1px;"></select>
                            </div>
                            </p>
                            <p class="p_input"><input class="company" id="address" type="text"   placeholder="详细地址" value="" /><span style="color:red;"> * </span></p>
                            <p class="p_input"><input class="company" id="user_name" type="text"   placeholder="姓名" value="" /><span style="color:red;"> *</span></p>
                            <p class="p_input"><input class="company" id="phone" type="text"   placeholder="手机号码" value="" /><span style="color:red;"> *</span></p>
                            <p class="p_input p_captchas"><input class="captchas" id="captcha"  placeholder="验证码"><span style="width : 40; " class="captchas_obtain"><img id="captcha_img" src="assets/img/login_blue/captchas.jpg" width="50" height="22"><i class="refresh" id="captcha_img_flush"></i></span></p>
                            <button class="loginbtn"  id="btnLogin">注 册</button>
                            <!--                    <button class="loginbtn"  id="btnFxLogin">分销后台登录</button>-->
                            <!--<button id="register_search">查看帐号审核进度</button>-->
                            <div style="float:left;">账号审批进度，<a href="" class ="register_search">请查看</a></div>
                            <div style="float:right;">已有审批账号，<a href="" class ="register_search_log">请登录</a></div>
                        </form>
                        <form class="loginform" onsubmit="return false;" id="company_from">
                            <div class="button-group" style="margin-bottom: 3.5%;width: 97%;height: 40px;">
                                <button class="button" name = 'personal' id="personal">个人注册</button>
                                <button class="button" name = 'company' id="company">公司注册</button>
                            </div>
                            <p class="p_input"><input class="username"  id="user_code2"  placeholder="登录名"><span style="color:red;"> *</span></p>
                            <p class="p_input"><input class="password" id="password2" type="password"  placeholder="密码"><span style="color:red;"> * </span></p>
                            <p class="p_input"><input class="company" id="company_name2" type="text"   placeholder="公司名称" value="" /><span style="color:red;"> * </span></p>
                            <p class="p_input">
                            <div style=" margin-right: 3%;margin-bottom:1%;">
                                <select class="province" id="province2" name="province" data-rules="{required : true}">
                                    <option value ="">请选择省份</option>
                                    <?php foreach ($response['area']['province'] as $k => $v) { ?>
                                        <option  value ="<?php echo $v['id']; ?>"  ><?php echo $v['name']; ?></option>
                                    <?php } ?>
                                </select>
                                <select class="city" id="city2" name="city" data-rules="{required : true}"></select>
                                <select class="district" id="district2" name="district" style=" margin-left: 1px;"></select>
                            </div>
                            </p>
                            <p class="p_input"><input class="company" id="address2" type="text"   placeholder="详细地址" value="" /><span style="color:red;"> * </span></p>
                            <p class="p_input"><input class="company" id="user_name2" type="text"   placeholder="联系人姓名" value="" /><span style="color:red;"> *</span></p>
                            <p class="p_input"><input class="company" id="phone2" type="text"   placeholder="联系人手机" value="" /><span style="color:red;"> *</span></p>
                            <p class="p_input p_captchas"><input class="captchas" id="captcha2"  placeholder="验证码"><span class="captchas_obtain"><img id="captcha_img2" src="assets/img/login_blue/captchas.jpg" width="50" height="21"><i class="refresh" id="captcha_img_flush2"></i></span></p>
                            <button class="loginbtn"  id="btnLogin2">注 册</button>
                            <!--<button class="loginbtn"  id="btnFxLogin2">分销后台登录</button>-->
                            <!--<button id="register_search2">查看帐号审核进度</button>-->
                            <div style="float:left;">账号审批进度，<a href="" class ="register_search">请查看</a></div>                    
                            <div style="float:right;">已有审批账号，<a href="" class ="register_search_log">请登录</a></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php echo load_js('comm_util.js') ?>
        <script type="text/javascript" >
            if (window.location !== window.top.location) {
                window.top.location = window.location;
            }

            var kh_id = "<?php echo $request['kh_id'] ?>";
            $(function () {
                $('#company_from').hide();
                $(":button[name='personal']").click(function () {
                    $('#company_from').hide();
                    $('#personal_from').show();
                });
                $(":button[name = 'company']").click(function () {
                    $('#company_from').show();
                    $('#personal_from').hide();
                });
                $('#btnLogin').click(function () {


                    if ($("#user_code").val() == '') {
                        BUI.Message.Tip('登录名不能为空', 'error');
                        return false;
                    }
                    if ($("#password").val() == '') {
                        BUI.Message.Tip('密码不能为空', 'error');
                        return false;
                    }
                    if ($("#user_name").val() == '') {
                        BUI.Message.Tip('姓名不能为空', 'error');
                        return false;
                    }
                    if ($("#phone").val() == '') {
                        BUI.Message.Tip('手机不能为空', 'error');
                        return false;
                    }
                    if ($("#captcha").val() == '') {
                        BUI.Message.Tip('请填写验证码', 'error');
                        return false;
                    }
                    if ($("#province1").val() == '' || $("#city1").val() == '' || $("#address").val() == '') {
                        BUI.Message.Tip('请将地址填写完整', 'error');
                        return false;
                    }
                    var params = {
                        kh_id: kh_id,
                        user_code: $('#user_code').val(),
                        password: $('#password').val(),
                        user_name: $('#user_name').val(),
                        phone: $('#phone').val(),
                        captcha: $('#captcha').val(),
                        province: $("#province1").val(),
                        city: $("#city1").val(),
                        district: $("#district1").val(),
                        address: $('#address').val(),
                    };
                    $.ajax({type: 'POST', dataType: 'json',
                        url: '<?php echo get_app_url('base/custom/do_register'); ?>', data: params,
                        success: function (ret) {
                            var type = ret.status == 1 ? 'success' : 'error';
                            if (type == 'success') {
                                var html = "您已注册成功，请等待管理员审核！<br />";
                                html += "以下为您的注册账号信息，请牢记：<br />";
                                html += "登录账号：" + $('#user_code').val() + "<br />";
                                html += "登录密码：" + $('#password').val() + "<br />";
                                html += "详细地址：" + $("#province1 :selected").text() + ' ' + $("#city1 :selected").text() + ' ' + $("#district1 :selected").text() + ' ' + $('#address').val() + "<br />";
                                html += "姓名：" + $('#user_name').val() + "<br />";
                                html += "手机号码：" + $('#phone').val() + "<br />";
                                BUI.Message.Alert(html, type);
                            } else {
                                BUI.Message.Alert(ret.message, type);
                                flush_captcha();
                            }
                        }
                    });
                });
                $('#btnLogin2').click(function () {
                    if ($("#user_code2").val() == '') {
                        BUI.Message.Tip('登录名不能为空', 'error');
                        return false;
                    }
                    if ($("#password2").val() == '') {
                        BUI.Message.Tip('密码不能为空', 'error');
                        return false;
                    }
                    if ($("#company_name2").val() == '') {
                        BUI.Message.Tip('请填写公司名称', 'error');
                        return false;
                    }
//                    if ($("#address2").val() == '') {
//                        BUI.Message.Tip('请填写详细地址', 'error');
//                        return false;
//                    }
                    if ($("#user_name2").val() == '') {
                        BUI.Message.Tip('姓名不能为空', 'error');
                        return false;
                    }
                    if ($("#phone2").val() == '') {
                        BUI.Message.Tip('手机不能为空', 'error');
                        return false;
                    }
                    if ($("#captcha2").val() == '') {
                        BUI.Message.Tip('请填写验证码', 'error');
                        return false;
                    }
                    if ($("#province2").val() == '' || $("#city2").val() == '' || $("#address2").val() == '') {
                        BUI.Message.Tip('请将地址填写完整', 'error');
                        return false;
                    }

                    var params = {
                        kh_id: kh_id,
                        user_code: $('#user_code2').val(),
                        password: $('#password2').val(),
                        company_name: $('#company_name2').val(),
                        user_name: $('#user_name2').val(),
                        phone: $('#phone2').val(),
                        address: $('#address2').val(),
                        captcha: $('#captcha2').val(),
                        province: $("#province2").val(),
                        city: $("#city2").val(),
                        district: $("#district2").val(),
                    };
                    $.ajax({type: 'POST', dataType: 'json',
                        url: '<?php echo get_app_url('base/custom/do_register'); ?>', data: params,
                        success: function (ret) {
                            var type = ret.status == 1 ? 'success' : 'error';
                            if (type == 'success') {
                                var html = "您已注册成功，请等待管理员审核！<br />";
                                html += "以下为您的注册账号信息，请牢记：<br />";
                                html += "登录账号：" + $('#user_code2').val() + "<br />";
                                html += "登录密码：" + $('#password2').val() + "<br />";
                                html += "公司名称：" + $('#company_name2').val() + "<br />";
                                html += "详细地址：" + $("#province2 :selected").text() + ' ' + $("#city2 :selected").text() + ' ' + $("#district2 :selected").text() + ' ' + $('#address2').val() + "<br />";
                                html += "详细地址：" + $('#address2').val() + "<br />";
                                html += "姓名：" + $('#user_name2').val() + "<br />";
                                html += "手机号码：" + $('#phone2').val() + "<br />";
                                BUI.Message.Alert(html, type);
                                flush_captcha();
                            } else {
                                BUI.Message.Alert(ret.message, type);
                                flush_captcha();
                            }
                        }
                    });
                });

                $(".register_search").click(function () {
                    var url = '?app_act=base/custom/register_search&kh_id=' + kh_id;
                    openPage(window.btoa(url), url, '查看帐号审核进度');
                });
                //    $("#btnFxLogin").click(function (){
                //        window.location.href = "?app_act=index/login";
                //    })
                $("#captcha_img_flush").click(function () {
                    flush_captcha();
                });

                $("#captcha_img_flush2").click(function () {
                    flush_captcha();
                });

                flush_captcha();
                $(".register_search_log").click(function () {
                    var url = '?app_act=index/login';
                    openPage(window.btoa(url), url);
                });

                var url = '<?php echo get_app_url('base/store/get_area'); ?>';
                $('#province1').change(function () {
                    var parent_id = $(this).val();
                    areaChange(parent_id, 1, url, 1);
                });
                $('#city1').change(function () {
                    var parent_id = $(this).val();
                    areaChange(parent_id, 2, url, 1);
                });
                $('#district1').change(function () {
                    var parent_id = $(this).val();
                    areaChange(parent_id, 3, url, 1);
                });
                $('#province2').change(function () {
                    var parent_id = $(this).val();
                    areaChange(parent_id, 1, url, 2);
                });
                $('#city2').change(function () {
                    var parent_id = $(this).val();
                    areaChange(parent_id, 2, url, 2);
                });
                $('#district2').change(function () {
                    var parent_id = $(this).val();
                    areaChange(parent_id, 3, url, 2);
                });
                $(".province").find("option[value='']").attr("selected", "selected");
                $(".province").change();
            });

            function flush_captcha() {
                var v_src = "?app_act=base/custom/captcha&code=code&_t=" + Math.round(Math.random(0) * 1000);
                $("#captcha_img").attr("src", v_src);
                $("#captcha_img2").attr("src", v_src);
            }
            //区域联动
            function areaChange(parent_id, level, url, callback) {
                $.ajax({type: 'POST', dataType: 'json',
                    url: url, data: {parent_id: parent_id},
                    success: function (data) {
                        var len = data.length;
                        var html = '';

                        switch (level) {
                            case 1:
                                html = "<option value=''>请选择市</option>";
                                for (var i = 0; i < len; i++) {
                                    html += "<option value='" + data[i].id + "'  >" + data[i].name + "</option>";
                                }
                                $("#city" + callback).html(html);
                                $("#district" + callback).html("<option value=''>请选择区/县</option>");
                                break;
                            case 2:
                                html = "<option value=''>请选择区/县</option>";
                                for (var i = 0; i < len; i++) {
                                    html += "<option value='" + data[i].id + "'  >" + data[i].name + "</option>";
                                }
                                $("#district" + callback).html(html);
                                break;
                        }

                        if (typeof callback == "function") {
                            callback();
                        }
                    }
                });
            }
        </script>
    </body>
</html>
