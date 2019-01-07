<style>
/*reset*/
body,div,a,p,ul,li,img,h1,h2,h3,h4,h5,h6,ol,table,tr,td{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
a{ text-decoration:none;}
li{ list-style:none;}
img,input{ border:none;}
span{ float:none;}

.autho_wrap{ font-size:16px;  position:absolute; top:0px;left:0px;width:100%; height:100%; background:url(assets/images/autho_bg.jpg) no-repeat; background-size:100% 100%;}
.autho_wrap .top{ width:100%; height:45px; background:#506e8f;}
.autho_wrap .top .top_cont{ width:1024px; height:45px; margin:0 auto; color:#FFF;}
.float_r{ float:right;}
.autho_wrap .top .top_cont a{ color:#FFF;}
.autho_wrap .top .top_cont img{ vertical-align:middle; margin-left:10px;}
.autho_wrap .top .top_cont span{ line-height:40px; margin:0 10px;}

.autho_wrap h2{ text-align:center; padding:3% 0;}
.autho_wrap .midl{ width:930px; margin:0 auto;}
.autho_wrap .midl .autho_proc a{ display:inline-block; padding:10px; background:#b4c6ca; color:#767ccd; border:2px solid #767ccd; margin:0 16px; border-radius:4px; cursor:default;}
.autho_wrap .midl .autho_proc a:hover{ text-decoration:none;}
.autho_wrap .midl .autho_proc a.past{ background:#767ccd; color:#FFF;}
.autho_wrap .midl .autho_proc a.curr{ background:#edb03b; color:#FFF; border-color:#edb03b;}
.autho_wrap .midl .autho_proc_num{ padding:12px 0 45px 80px;}
.autho_wrap .midl .autho_proc_num a{ display:inline-block; width:23px; height:23px; border-radius:50%; background:#c7d5d6; text-align:center; line-height:22px; color:#666; cursor:default;}
.autho_wrap .midl .autho_proc_num span{ float:none; display:inline-block; width:132px; height:7px; background:url(assets/images/proc_span_bg.png) repeat-x; vertical-align:middle; margin:0 3px;}
.autho_wrap .midl .autho_proc_num span.span_02{ width:146px;}
.autho_wrap .midl .autho_proc_num span.span_03{ width:164px;}
.autho_wrap .midl .autho_proc_num span.span_04{ width:153px; height:25px; background:url(assets/images/proc_span_05bg.png) no-repeat;}
.autho_wrap .midl .autho_proc_num a.past{ background:#FFF;}
.autho_wrap .midl .autho_proc_num a.curr{ background:#edb03b; color:#FFF;}
.autho_wrap .midl .autho_proc_num span.curr{ background-position:0 bottom;}
.autho_wrap .midl .autho_proc_cont{ width:812px; padding:28px; margin:0 auto; background:url(assets/images/autho_proc_contbg.png); border-radius:3px; display:none;}
.autho_wrap .midl .autho_proc_cont .autho_code{ width:512px; padding:28px 0; margin:0 auto;}
.autho_wrap .midl .autho_proc_cont .autho_code input{ width:378px; height:48px; border:1px solid #9296cd; background:#d7e1e0; border-radius:3px; font-size:16px; text-indent:10px; color:#666;}
.autho_wrap .midl .autho_proc_cont .autho_code button{ width:125px; height:50px; background:#edb03b; color:#FFF; text-align:center; border:none; border-radius:3px; float:right; font-size:20px; cursor:pointer;}
.autho_wrap .midl .autho_proc_cont .autho_code button:hover{ background:#f9b73a;}
.autho_wrap .midl .autho_proc_cont .autho_code p{ color:#666; line-height:26px; padding-top:10px;}
.autho_wrap .midl .autho_proc_cont .autho_code p a{ color:#FFF; text-decoration:underline;}
.autho_wrap .midl .autho_proc_cont .enter_account{ width:390px; margin:0 auto;}
.autho_wrap .midl .autho_proc_cont .enter_account p{ margin-bottom:18px;}
.autho_wrap .midl .autho_proc_cont .enter_account p label{ display:inline-block; height:40px; line-height:40px; color:#666;}
.autho_wrap .midl .autho_proc_cont .enter_account p input{ float:right; width:295px; height:38px; border:1px solid #9297ce; background:#e5e6e4; border-radius:3px; text-indent:10px; color:#666; font-size:14px;}
.autho_wrap .midl .autho_proc_cont .enter_account p input:focus{ border-color:#00b6ca;}
.autho_wrap .midl .autho_proc_cont .enter_account p input.error{ border-color:#e85824;}
.autho_wrap .midl .autho_proc_cont .enter_account button{ display:block; width:125px; height:50px; background:#edb03b; border-radius:3px; color:#FFF; text-align:center; border:none; cursor:pointer; font-size:20px; margin:0 auto;}
.autho_wrap .midl .autho_proc_cont .enter_account button:hover{ background:#f9b73a;}
.autho_wrap .midl .autho_proc_cont .check_account .call{ color:#666;}
.autho_wrap .midl .autho_proc_cont .check_account .call span{ color:#FFF;}
.autho_wrap .midl .autho_proc_cont .check_account .autho_succ{ color:#FFF; font-size:18px; text-align:center; padding:10px 0;}
.autho_wrap .midl .autho_proc_cont .check_account .autho_succ img{ vertical-align:middle; margin-right:15px;}
.autho_wrap .midl .autho_proc_cont .check_account .autho_succ span{ color:#767ccd; margin:0 3px;}
.autho_wrap .midl .autho_proc_cont .check_account .details{ padding-left:304px; color:#333; padding-bottom:12px;}
.autho_wrap .midl .autho_proc_cont .check_account .details span{ float:none; margin-left:0;}
.autho_wrap .midl .autho_proc_cont .check_account .details .span_01{ display:inline-block; width:108px;}
.autho_wrap .midl .autho_proc_cont .check_account .remind{ padding-left:304px; color:#666; padding-bottom:12px;}
.autho_wrap .midl .autho_proc_cont .check_account .btns{ text-align:center; padding-top:15px;}
.autho_wrap .midl .autho_proc_cont .check_account .btns button{ width:125px; height:50px; border-radius:3px; border:none; background:#edb03b; text-align:center; color:#FFF; font-size:20px; cursor:pointer; margin:0 5px;}
.autho_wrap .midl .autho_proc_cont .check_account .btns button:hover{ background:#f9b73a;}

.autho_wrap .botm{ width:1000px; position:absolute; left:50%; bottom:2.5%; margin-left:-500px; text-align:center; color:#999;}
</style>
<script src="http://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script>

<input type="hidden" id="params_data" value="<?php echo $_REQUEST['_data']; ?>"/>
<div class="autho_wrap">
	<div class="top">
    	<div class="top_cont">
        	<img src="assets/images/hotline.png" width="23" height="23"><span>400-680-9510</span><a class="float_r" href="#"><img src="assets/images/online_help.png" width="32" height="32"><span>帮助</span></a><span class="float_r">|</span><a class="float_r" href="#"><img src="assets/images/online_cus.png" width="32" height="32"><span>在线客服</span></a>
        </div>
    </div>
    <h2><img src="assets/images/midl_title.png"></h2>
    <div class="midl">
        <p class="autho_proc">
          <a class="past" href="javascript:void(0)">淘宝服务平台订购</a>
          <a class="curr" href="javascript:void(0)" id="step1">输入授权码</a>
          <a href="javascript:void(0)" id="step2">输入管理员帐号及密码</a>
          <a href="javascript:void(0)" id="step3">确认账号及密码</a>
          <a href="javascript:void(0)" id="step4">成功进入eFAST系统</a>
        </p>
        <p class="autho_proc_num">
          <a class="past" href="javascript:void(0)">1</a>

          <span class="curr" id="step1_span">&nbsp; </span>
          <a class="curr" href="javascript:void(0)" id="step1_a">2</a>

          <span class="span_02" id="step2_span">&nbsp; </span>
          <a href="javascript:void(0)" id="step2_a">3</a>

          <span class="span_03" id="step3_span">&nbsp; </span>
          <a href="javascript:void(0)" id="step3_a">4</a>

          <span class="span_04">&nbsp; </span>
          <a href="javascript:void(0)">5</a>
        </p>
        <div class="autho_proc_cont" id="autho_proc_cont1" style="display:block">
        	<div class="autho_code">
            	<input type="text" placeholder="请输入授权码" id = "auth_key"><button type="button" class="next" onclick="check_auth_code()">授&nbsp; 权</button>
                <p>授权码在订购平台中购买软件成功时，已发送给您，请登陆<a href="#">订购平台</a>查看若未能找到，请<a href="#">联系我们</a></p>
            </div>
        </div>
        <div class="autho_proc_cont"  id="autho_proc_cont2">
        	<div class="enter_account">
            	<p><label>公司名称</label><input type="text" id="pre_company_name" readonly></p>
                <p><label>管理员账号</label><input type="text" placeholder="请输入管理员账号" id = "login_user_name"></p>
                <p><label>管理员密码</label><input type="text" class="error" placeholder="请输入密码" id = "login_password"></p>
                <p><label>确认密码</label><input type="text" placeholder="请再次输入密码" id = "re_login_password"></p>
                <button type="button" class="next" onclick="create_new_customer()">下一步</button>
            </div>
        </div>
        <div class="autho_proc_cont" id="autho_proc_cont3">
        	<div class="check_account">
            	<p class="call">亲爱的<span id="company_name"></span>，您好！</p>
                <p class="autho_succ"><img src="assets/images/autho_succ.png" width="38" height="38">您的店铺<span id="shop_name"></span>已经授权成功</p>
                <p class="details"><span class="span_01">公司名称：</span><span class="span_02" id="company_name_2"></span></p>
                <p class="details"><span class="span_01">管理员账号：</span><span class="span_02" id="name"></span></p>
                <p class="details"><span class="span_01">密码：</span><span class="span_02" id="password"></span></p>
                <p class="remind">请牢记您的账号和密码！</p>
                <p class="btns">
                <button type="button" class="back" onclick="back_plan()">上一步</button>
                <button type="button" class="next" id ="confirm_btn">确定</button></p>
            </div>
        </div>
    </div>
    <div class="botm">版权所有：上海宝塔科技有限公司&nbsp; &nbsp; &nbsp; 沪ICP备14036881号-2</div>
</div>

<script>
function back_plan(){
   var next_idx = parseInt($(".autho_proc_num a[class='curr']").html());
   var pre_idx = next_idx-2;
   var cur_idx = next_idx-1;

   $("#autho_proc_cont"+cur_idx).hide();
   $("#step"+cur_idx).removeClass('curr');
   $("#step"+cur_idx+'_span').removeClass('curr');
   $("#step"+cur_idx+'_a').removeClass('curr');

   $("#autho_proc_cont"+pre_idx).show();
   $("#step"+pre_idx).addClass('curr');
   $("#step"+pre_idx+'_span').addClass('curr');
   $("#step"+pre_idx+'_a').addClass('curr');

   return;
}

function sw_plan(){
   var next_idx = parseInt($(".autho_proc_num a[class='curr']").html());
   var cur_idx = next_idx-1;

   $("#autho_proc_cont"+cur_idx).hide();
   $("#step"+cur_idx).removeClass('curr');
   $("#step"+cur_idx+'_span').removeClass('curr');
   $("#step"+cur_idx+'_a').removeClass('curr');

   $("#autho_proc_cont"+next_idx).show();
   $("#step"+next_idx).addClass('curr');
   $("#step"+next_idx+'_span').addClass('curr');
   $("#step"+next_idx+'_a').addClass('curr');

   return;
}

function check_auth_code(){
    var params = {};
    params['_data'] = $("#params_data").val();
    params['auth_key'] = $("#auth_key").val();
    //params['login_user_name'] = $("#login_user_name").val();
    //params['login_password'] = $("#login_password").val();

    $.post('<?php echo get_app_url('index/act_input_auth_key');?>', params, function(data) {
        var ret = eval('('+data+')');
        if (ret.status == 1) {
            //alert(ret.data);
            location.href = ret.data;
        }

	    if (ret.status == 2) {
		    alert('授权成功!');
		    return;
	    }

        if (ret.status == -3) {
            //alert(ret.message);
            $("#pre_company_name").val(ret['data']);
            sw_plan();
            return;
        }
        if (ret.status < 0) {
            alert(ret.message);
        }
    });
}

function create_new_customer(){
    var login_password = $("#login_password").val();
    var re_login_password = $("#re_login_password").val();
    var login_user_name = $("#login_user_name").val();
    var pre_company_name = $("#pre_company_name").val();

    if(login_password == '' || re_login_password == ''){
        alert("密码和确认密码不能为空");
        return;
    }else if (login_password != re_login_password){
        alert("密码和确认密码不一致");
        return;
    }else if (login_password.length<6  ||  login_password.length>12){
        alert("密码长度必须为6-12位");
        return;
    }

    var params = {};
    params['_data'] = $("#params_data").val();
    params['auth_key'] = $("#auth_key").val();
    params['login_user_name'] = $("#login_user_name").val();
    params['login_password'] = $("#login_password").val();

    $.post('<?php echo get_app_url('index/act_input_auth_key');?>', params, function(data) {
        var ret = eval('('+data+')');
        if (ret.status == 1) {
            var go_url = ret.data;
            //console.log(ret);
            //alert(go_url);
            //return;
            $("#confirm_btn").click(function(){
                location.href = go_url;
            });
            var shop_name = ret.message;
            $("#shop_name").html(shop_name);
            $("#company_name").html($("#pre_company_name").val());
            $("#company_name_2").html($("#pre_company_name").val());
            $("#name").html($("#login_user_name").val());
            $("#password").html($("#login_password").val());
            sw_plan();
            //console.log(ret.data);
        }
        if (ret.status == -3) {
            alert(ret.message);
        }
        if (ret.status < 0) {
            alert(ret.message);
        }
    });
}


</script>