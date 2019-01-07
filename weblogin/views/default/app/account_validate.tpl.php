<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
        <title><?php echo $request['action'] == 'create' ? '申请账号' : '重置密码'; ?></title>
        <link href="app/css/efast365_wechat.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div class="login_wrap">
            <form class="apply_form">
                <img class="logo" src="app/images/efast365_logo.png">
                <input class="mobile" type="text" id="mobile" placeholder="手机号码">
                <div id="err_msg1"></div>
                <div>
                    <input class="verify_code" type="text" id="verify_code" placeholder="验证码"> 
                    <button class="get_code" type="button" id="get_code"  >获取验证码</button>
                    <div id="err_msg2"></div>
                </div>
                <button class="check" type="button" id="btnCheck"><?php echo $request['action'] == 'create' ? '申请' : '重置密码'; ?></button>
                <div class="bubble">                 
                    <div class="wrap">登录地址：baotayun.com</div>               
                </div>
                <div class="back">返回</div>
            </form>      
        </div>
        <div class="show_mask" style="display: none"></div>
        <div class="show_info" style="display: none">
            <div class="show_msg">
                <div class="msg" style="color: darkgreen"><?php echo $request['action'] == 'create' ? '申请' : '重置'; ?>成功</div>
                <div class="msg"><?php echo $request['action'] == 'create' ? '申请' : '重置'; ?>结果将发送至手机，请在电脑端登录。</div>
                <div class="msg">登录地址：baotayun.com</div>
                <div class="show_btn">
                    <button class="info_button" onclick="gotcha()">了解</button>
                </div>
            </div>
        </div>
        <?php echo load_js('jquery-1.8.1.min.js'); ?>
        <script type="text/javascript" >

            $(function () {
                $('#btnCheck').click(function () {
                    $("#btnCheck").attr('disabled', true);
                    var mobile_num = $("#mobile").val();
                    var c = check_mobile(mobile_num);
                    if (c == -1) {
                        $("#mobile").css("margin-bottom", '0px');
                        $("#err_msg1").css({'margin-bottom': '1%', 'color': 'red'});
                        $("#err_msg1").html('这手机号码不太对哦~');
                        return;
                    }
                    var verify_code = $("#verify_code").val();
                    var action = '<?php echo $request['action']; ?>'
                    check_mobile(mobile_num);
                    var params = {mobile_num: mobile_num, verify_code: verify_code, action: action};
                    $.post('<?php echo get_app_url('app/account/check_verify_code'); ?>', params, function (ret) {
                        if (ret.status == 1) {
                            //微信网页弹框
                            $('.show_mask').show();
                            $('.show_info').show();
                        } else {
                            $("#btnCheck").attr('disabled', false);
                            $("#err_msg2").css({'margin-bottom': '1%', 'color': 'red'});
                            $("#err_msg2").html(ret.message);
                        }
                    }, 'json');
                });

                $('.get_code').click(function () {
                    var mobile_num = $("#mobile").val();
                    var action = '<?php echo $request['action'];?>';
                    var c = check_mobile(mobile_num);
                    if (c == -1) {
                        $("#mobile").css("margin-bottom", '0px');
                        $("#err_msg1").css({'margin-bottom': '1%', 'color': 'red'});
                        $("#err_msg1").html('这手机号码不太对哦~');
                        return;
                    }
                    var params = {mobile_num: mobile_num,action: action};
                    $.post('<?php echo get_app_url('app/account/get_verify_code'); ?>', params, function (ret) {
                        if (ret.status == 1) {
                            //微信网页弹框
                            get_code();
                            $("#err_msg2").css({'margin-bottom': '1%', 'color': 'green'});
                            $("#err_msg2").html('验证码已发送至手机端！');
                        } else {
                            $("#mobile").css("margin-bottom", '0px');
                            $("#err_msg1").css({'margin-bottom': '1%', 'color': 'red'});
                            $("#err_msg1").html(ret.message);
                        }
                    }, 'json');
                });
                $("#mobile,#verify_code").focus(function () {
                    $("#err_msg1").css({'margin-bottom': '0', 'color': 'red'});
                    $("#err_msg1").html('');
                    $("#mobile").css("margin-bottom", '3%');
                    $("#err_msg2").html('');
                });
               $('.back').click(function () {
                   window.location.href = '<?php echo get_app_url('app/account/account_opt');?>';
               })  
            });
            
            function gotcha(){
                window.location.href = '<?php echo get_app_url('app/account/account_opt');?>';
            }
            
            var count = 5;
            function go_to() {
                if (count == 0) {
                    window.location.href = '<?php echo get_app_url('index/login_app');?>';
                } else {
                    $("#err_msg2").html('操作成功，操作结果将发送至手机端，跳转登录页面中(' + count + ')');
                    count--
                }
                setTimeout(function () {
                    go_to()
                }
                , 1000)
            }

            //验证码获取倒计时
            var countdown = 60;
            function get_code() {
                if (countdown == 0) {
                    $("#get_code").attr("disabled", false);
                    $("#get_code").attr('style', 'font-size:14px')
                    $("#get_code").html("重新获取验证码");
                    countdown = 60;
                    return;
                } else {
                    $("#get_code").attr("disabled", true);
                    $("#get_code").html("请稍等(" + countdown + ")");
                    countdown--;
                }
                setTimeout(function () {
                    get_code()
                }
                , 1000)
            }
            //校验手机号码的正确性
            function check_mobile(mobile_num) {
                var pattern = /(13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170[059]\d{7}/;
                if (!pattern.test(mobile_num)) {
                    return -1;
                }
            }
        </script>
    </body>
</html>
