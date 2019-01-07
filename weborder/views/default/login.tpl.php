<?php 
if(CTX()->get_session("LoginState")!=true){?>
    <div class="account_login" id="account_login">
        <div class="top_btn"><a class="back" href="#"><img src="assets/img/top_btn_back.png" width="8" height="16">回去再看看</a><a class="close" href="#"><img src="assets/img/top_btn_close.png" width="34" height="34"></a></div>
        <form><h3><img src="assets/img/login_title.png" width="215" height="81" alt="在线订购"></h3>
            <input name="4" type="hidden"  id="loginsum" value="<?php echo CTX()->get_session("loginsum") ?>" />
            <p class="name"><label>用户名</label><input type="text" id="username" name="username" placeholder="请输入您的用户名" value="<?php echo isset($_COOKIE['username'])?$_COOKIE['username']:'';?>"><i class="clean">&times;</i><span id="usernameMsg" class="remand"></span></p>
            <p class="passw error"><label>密&nbsp; 码</label><input type="password" id="userpwd" name="userpwd" placeholder="密码区分大小写，最少6个字符哦~" value="<?php echo isset($_COOKIE['userpwd'])?$_COOKIE['userpwd']:'';?>"><i class="clean">&times;</i><span id="userpwdMsg" class="remand"></span></p>
            <p class="captcha" id="capdiv" style="display:none">
                <label>验证码</label>
                <input type="text" id="captcha" />
                <span class="code">
                    <img title="看不清楚，双击图片换一张" alt="验证码" src="?app_act=index/captcha&code=code" onclick="this.src=this.src+'&'+Math.round(Math.random(0)*1000)" style="cursor:pointer;width: 195px;height: 40px; vertical-align: middle;"></img>
                </span>
                <span id="captchaMsg" class="remand"></span>
            </p>
            <p class="remember"><input type="checkbox" id="remb" <?php echo isset($_COOKIE['remember'])? 'checked':'';?>><label for="remb">记住密码</label></p>
            <p class="btns"><button type="button" id="userlogin" class="btn_01">登录</button><button type="button" class="btn_02">我还没有账号</button></p>
        </form>
        <!--div class="third_login">
            <h4>使用第三方账号登陆</h4>
            <p><a href="#"><img src="assets/img/qq_icon.png" width="29" height="32">QQ登录</a><a href="#"><img src="assets/img/sina_icon.png" width="34" height="28">微博登录</a><a href="#"><img src="assets/img/weixin_icon.png" width="34" height="27">微信登陆</a></p>
        </div-->
    </div>
<?php } ?>