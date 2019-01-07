<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="assets/css/login.css" rel="stylesheet" type="text/css" />
<?php echo load_js('jquery-1.8.1.min.js');?>
<title>eFAST电商快车</title>
</head>

<body>
<input type="hidden" id="params_data" value="<?php echo $_REQUEST['data']; ?>"/>
<input type="hidden" id="params_time" value="<?php echo $_REQUEST['time']; ?>"/>
<input type="hidden" id="params_sign" value="<?php echo $_REQUEST['sign']; ?>"/>

<div class="page" id="ask_box">
    <img class="page_bg" src="assets/images/page_bg.jpg" />
    <div class="loader">
        <div class="loader_cont">
            <div class="efast_logo"><img src="assets/images/efast.jpg" /></div>
            <h2>请确认，您是否用其它店铺订购过EFAST?</h2>
            <a class="btn" href="#" style="letter-spacing:0px" onclick="op_box('new_customer_box')">未订购过EFAST</a>
            <a class="btn" href="#" style="letter-spacing:0px;background:blue;"  onclick="op_box('input_auth_code_box')">已订购过EFAST</a>
        </div>
    </div>
</div>

<div class="page" id="input_auth_code_box"  style="display:none;">
    <img class="page_bg" src="assets/images/page_bg.jpg" />
    <div class="loader">
        <div class="loader_cont">
            <div class="efast_logo"><img src="assets/images/efast.jpg" /></div>
            <input class="input_tt"  id="auth_key" type="text" value="" placeholder="请输入你已订购EFAST系统的授权码" />
            <a class="btn" href="#" id="btnAuthCode">确认</a>
            <a class="btn back_btn" href="#" style="background:#003300;">返回</a>
        </div>
    </div>
</div>

<div class="page" id="new_customer_box" style="display:none;">
    <img class="page_bg" src="assets/images/page_bg.jpg" />
    <div class="loader">
        <div class="loader_cont">
            <div class="efast_logo"><img src="assets/images/efast.jpg" /></div>
            <input class="input_tt"  type="text" value="" id="customer_name" placeholder="请输入您的公司名称" />
            <input class="input_tt"  type="text" value="admin" id="user_name" name="user_name" readonly/>
            <input class="input_tt" type="password" value="" id="password" name="password" placeholder="管理员密码"/>
            <a class="btn" href="#" id="btnNewCustomer" style="letter-spacing:0px;">创建EFAST应用平台</a>
            <a class="btn back_btn" href="#" style="background:#003300;">返回</a>
        </div>
    </div>
</div>

<script type="text/javascript" >
function op_box(box_id){
    $("#ask_box").css("display","none");
    $("#"+box_id).css("display","block");
}

$(".back_btn").click(function(){
    $("#ask_box").css("display","block");
    $("#input_auth_code_box").css("display","none");
    $("#new_customer_box").css("display","none");
});

$("#btnAuthCode").click(function(){
    var params = {};
    params['d'] = $("#params_data").val();
    params['time'] = $("#params_time").val();
    params['sign'] = $("#params_sign").val();

    params['auth_key'] = $("#auth_key").val();

    $.post('<?php echo get_app_url('index/login_by_auth_key');?>', params, function(data) {
        var ret = eval('('+data+')');
        if (ret.status == 1) {
            //alert(ret.data);
            location.href = ret.data;
        } else {
            alert(ret.message);
        }
    });

});

$("#btnNewCustomer").click(function(){
    var params = {};
    params['d'] = $("#params_data").val();
    params['time'] = $("#params_time").val();
    params['sign'] = $("#params_sign").val();

    params['customer_name'] = $("#customer_name").val();
    params['user_name'] = $("#user_name").val();
    params['password'] = $("#password").val();

    $.post('<?php echo get_app_url('index/create_new_customer');?>', params, function(data) {
        var ret = eval('('+data+')');
        if (ret.status == 1) {
            //alert(ret.data);
            location.href = ret.data;
        } else {
            alert(ret.message);
        }
    });

});


if(window.location !== window.top.location){
    window.top.location=window.location;
}
$( function(){
    $("label.remember").click(function(){
        if($("a[name='checkWeek']").hasClass('remember_cked')){
            $("a[name='checkWeek']").removeClass('remember_cked');
        }else{
            $("a[name='checkWeek']").addClass('remember_cked');
        }
    });


    $('#user_code').keydown(function(e){
        if(e.keyCode==13){
           $('#btnLogin').click();
        }
    });
    $('#password').keydown(function(e){
        if(e.keyCode==13){
           $('#btnLogin').click();
        }
    });


    $('#btnLogin').click(function() {

        var params = {'do': 1,
            user_code: $('#user_code').val(),
            password: $('#password').val(),
            remember: $("a[name='checkWeek']").hasClass('remember_cked') ? 1 : 0
        };
        $.post('<?php echo get_app_url('index/login');?>', params, function(data) {
            var ret = eval('('+data+')');
            if (ret.status == 1) {
                window.location.href = '<?php echo get_app_url('index/do_index');?>';
            } else {
                // 错误信息提示
                alert(ret.message);
            }
        });
    });

})
</script>
</body>
</html>
