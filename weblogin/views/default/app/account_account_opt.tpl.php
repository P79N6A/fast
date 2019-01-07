<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
        <title>eFAST365演示账号</title>
        <link href="app/css/efast365_wechat.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div class="opt_wrap">
            <div class="apply_form">
                <img class="logo" src="app/images/efast365_logo.png">
                <div class="opt">
                    <div>
                        <div class="large button blue" id="create">申请账号</div>
                    </div>
                    <div>
                        <div class="large button blue" id="change">重置密码</div>
                    </div>
                </div>
            </div>      
        </div>    
        <?php echo load_js('jquery-1.8.1.min.js'); ?>
        <script type="text/javascript" >

            $(function () {
                $('#create').click(function () {
                    window.location.href = '<?php echo get_app_url('app/account/validate&action=create');?>';
                });

                $('#change').click(function () {
                    window.location.href = '<?php echo get_app_url('app/account/validate&action=change');?>';
                });
            });
        </script>
    </body>
</html>
