<?php 
if(CTX()->get_session("LoginState")!=true){?>
    <div class="account_register" id="account_register">
        <div class="top_btn"><a class="back" href="#"><img src="assets/img/top_btn_back.png" width="8" height="16">回去再看看</a><a class="close" href="#"><img src="assets/img/top_btn_close.png" width="34" height="34"></a></div>
        <form id="companyform" style="display:block"><h3><img src="assets/img/login_title.png" width="215" height="81" alt="在线订购"></h3>
            <p class="register_type"><label>选择注册类型</label><a class="company curr" href="javascript:void(0)">公司</a>
            <p class="p_norm"><label>用户名<i>*</i></label><input type="text" id="kh_code" name="kh_code" placeholder="请输入您的用户名"><i class="clean">&times;</i><span id="kh_codeMsg" class="remand"></span></p>
            <p class="p_norm"><label>Email<i>*</i></label><input type="text" id="kh_email" name="kh_email" placeholder="请输入您的邮箱"><i class="clean">&times;</i><span id="emailMsg" class="remand"></span></p>
            <p class="p_norm error"><label>密码<i>*</i></label><input type="password" id="kh_login_pwd" name="kh_login_pwd" placeholder="密码区分大小写，最少6个字符哦~"><i class="clean">&times;</i><span id="pwdMsg" class="remand"></span></p>
            <p class="p_norm"><label>确认密码<i>*</i></label><input type="password" id="kh_login_pwd2" name="kh_login_pwd2" placeholder="再次输入您设置的密码，确保一样哦~"><i class="clean">&times;</i><span id="pwdMsg2" class="remand"></span></p>
            <p class="p_norm"><label>公司名称<i>*</i></label><input type="text" id="kh_name" name="kh_name" placeholder=""><i class="clean">&times;</i><span id="kh_nameMsg" class="remand"></span></p>
            <p class="p_norm"><label>营业执照号<i>*</i></label><input type="text" name="kh_licence_num" id="kh_licence_num" placeholder=""><i class="clean">&times;</i><span id="kh_nameMsg" class="remand"></span></p>
            <p class="scan">
                <input type="hidden" id="kh_licence_img" value=""/>
                <label>营业执照扫描件<i>*</i></label>
                <span class="scan_pic">暂无图片</span>
                <button id="upfileclick" type="button">选择文件</button>
                <input id="upfile" name='upfile' type='file' style="display:none " onchange="upfilechange(this);"/>
                <strong>上传的图片大小不要超过5MB哦<br></strong>
                <span id="kh_licence_imgMsg"></span>
            </p>
            <p class="p_norm"><label>联系人<i>*</i></label><input type="text" id="kh_itname" name="kh_itname" placeholder=""><i class="clean">&times;</i><span id="kh_itnameMsg" class="remand"></span></p>
            <p class="p_norm"><label>联系人电话<i>*</i></label><input type="text" id="kh_itphone" name="kh_itphone" placeholder="留个电话常联系呢o(∩_∩)o"><i class="clean">&times;</i><span id="moileMsg" class="remand"></span></p>
            <p class="p_norm"><label>公司地址</label><input type="text" id="kh_address" name="kh_address" placeholder=""><i class="clean">&times;</i><span id="kh_addressMsg" class="remand"></span></p>
            <p class="p_norm"><label>公司电话</label><input type="text" id="kh_tel" name="kh_tel" placeholder=""><i class="clean">&times;</i><span id="kh_telMsg" class="remand"></span></p>
<!--                        <p class="agree"><input type="checkbox" id="is_agree_p"><label>我已同意</label><a href="javascript:void(0)" onclick="window.open('?app_act=index/show_serarg');">在线订购服务协议</a></p>-->
            <p class="btns"><button class="btn_01" id="enrol" type="button">注册</button><button class="btn_02" type="button">我有账号了</button><button class="btn_03" type="button">取 消</button></p>
        </form>
        <!--div class="third_login">
            <h4>使用第三方账号登陆</h4>
            <p><a href="#"><img src="assets/img/qq_icon.png" width="29" height="32">QQ登录</a><a href="#"><img src="assets/img/sina_icon.png" width="34" height="28">微博登录</a><a href="#"><img src="assets/img/weixin_icon.png" width="34" height="27">微信登陆</a></p>
        </div-->
    </div>
    <!--注册成功提醒-->
    <div class="registered" id="registered">
    	<img src="assets/img/registered.png" width="30" height="30">您已注册成功。
    </div>
<?php } ?>
<?php echo load_js('ajaxfileupload.js',true);?>
<?php echo load_js('base64.js',true);?>