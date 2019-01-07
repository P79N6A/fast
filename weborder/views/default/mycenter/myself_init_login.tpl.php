<?php echo load_css('bui.css', true) ?>
<?php echo load_css('dpl.css', true) ?>
<?php echo load_css('order.css', true); ?>
<?php echo load_css('common.css', true); ?>
<?php echo load_js('bui.js', true); ?>
<?php echo load_js('comm_util.js', TRUE); ?>
<style>
    .button{
        width: 79px;
        height: 33px;
        border: 3px solid #e95513;
        text-align: center;
        font-size: 15px;
        margin-right: 2px;
        cursor: pointer;
        background: #e95513;
        color: #FFF;
    }
    .button:hover{
        background:#f25c1e;
        border-color:#ee571b;
        color:#eee;
    }
</style>
<div class="order_wrap">
    <?php include get_tpl_path('top') ?>
    <div class="content" style="margin: 120px 0 8.2% 30%;font-size: 12px;">
        <h2 style="margin:0px 0 20px 15%"><strong>初始化系统用户名和密码</strong></h2>
        <form id="J_Form" action="?app_act=mycenter/myself/set_login&app_fmt=json" method="post" class="form-horizontal">
            <div class="control-group">
                <label class="control-label"><s>*</s>登录名：</label>
                <div class="controls">
                    <input name="user_code" type="text" class="input-large" data-rules="{required : true}" id="user_code">
                    <div class="alertmsg" style="color:red; font-size: 12px;"></div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><s>*</s>初始密码：</label>
                <div class="controls">
                    <input name="password" type="password" class="input-large" data-rules="{required : true}" id="password">
                    <div class="alertmsg2" style="color:red; font-size: 12px;"></div>
                </div>
                <strong>&nbsp;&nbsp;密码长度为8-20位，须为数字、大写字母、小写字母和特殊符号的组合</strong>
            </div>
            <div class="control-group">
                <label class="control-label"><s>*</s>确认密码：</label>
                <div class="controls">
                    <input name="re_password" type="password"  class="input-large" data-rules="{required : true}" id="re_password">
                    <div class="alertmsg1" style="color:red; font-size: 12px;"></div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><s>*</s>真实名：</label>
                <div class="controls">
                    <input name="user_name" type="text" class="input-large" data-rules="{required : true}">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">电话号码：</label>
                <div class="controls">
                    <input name="tel" type="text" class="input-large">
                </div>
            </div>
            <div class="control-group" style="margin:0 0 40px 60px;color:red"><strong>温馨提示：管理员账号只能设置一次，请谨慎操作！</strong></div>
            <div class="row actions-bar">
                <div class="form-actions span13 offset3">
                    <button type="submit" class="button button-primary" id="submit_btn">提交</button>
                    <button type="reset" class="button">重置</button>
                </div>
            </div>
        </form>
    </div>
    <div class="order_bottom">
        <p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#user_code").focus();
        $("#user_code").blur(function () {
            check_user_info();
        });
        $("#user_code").focus(function () {
            $("#user_code").attr("style", "");
            $(".alertmsg").hide();
        });
        $("#password").blur(function () {
            check_pass_strong();
        });
        $("#password,#re_password").focus(function () {
            $("#password,#re_password").attr("style", "");
            $(".alertmsg1,.alertmsg2").hide();
        });
        //验证两次密码是否一致
        $("#re_password").blur(function () {
            p = $("#password").val();
            rp = $("#re_password").val();
            if (p !== rp) {
                $("#password,#re_password").attr("style", "border: 1px dotted #F00;");
                $(".alertmsg1").show();
                $(".alertmsg1").html("<span class='x-icon x-icon-mini x-icon-error'>!</span>两次密码不一致！");
            }
        });
    });
    //检测用户名是否存在
    function check_user_info() {
        var user_code = $("#user_code").val();
        if (user_code != '') {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('mycenter/myself/check_user_info'); ?>',
                data: {user_code: user_code},
                success: function (ret) {
                    var type = ret.status === 1 ? 'success' : 'error';
                    if (type === 'error') {
                        $("#user_code").attr("style", "border: 1px dotted #F00;");
                        $(".alertmsg").show();
                        $(".alertmsg").html("<span class='x-icon x-icon-mini x-icon-error'>!</span>用户名已存在！");
                        return -1;
                    }
                }
            });
        }
    }
    //验证密码强度是否足够
    function check_pass_strong() {
        var password = $("#password").val();
        var valid = RegExp(/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/).test(password);
        if (!valid) {
            $("#password").attr("style", "border: 1px dotted #F00;");
            $(".alertmsg2").show();
            $(".alertmsg2").html("<span class='x-icon x-icon-mini x-icon-error'>!</span>密码强度不够！");
            return -1;
        }
    }

    BUI.use('bui/form', function (Form) {
        new Form.HForm({
            srcNode: '#J_Form',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'success');
                    $(".bui-stdmod-footer button").click(function () {
                        location.href = '<?php echo $response['pra_serverpath'] ?>';
                    })
                } else {
                    BUI.Message.Alert(data.message, 'error！');
                }
            }
        }).render();
    })
</script>