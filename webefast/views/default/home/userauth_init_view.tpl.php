<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>用户权限初始化</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="zh-CN" />
<meta content="all" name="robots" />
<meta name="Copyright" content="" />
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" type="text/css" href="echarts/css/basic.css"/>
<link rel="stylesheet" type="text/css" href="echarts/css/common.css"/>
<link rel="stylesheet" type="text/css" href="echarts/css/system.css"/>
    <style>
        a:hover,a:active,a:visited,a:link{text-decoration: none;}
        a:focus {
            outline:none;
            -moz-outline:none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="inner">
        <!--====用户权限初始化 主体 start====-->
        <div>
            <!--====用户权限初始化 步骤一 start====-->
            <div class="warehouse_init userauth_init">
                <p>步骤一：<em>根据岗位职责添加角色</em></p>
                <div>
                    <div class="line"></div>
                    <span class="icon1 icon_w1" id="icon1 icon_w1">角色列表</span>
                    <p class="auth3">
                        <span>新增角色</span>
                        <span class="icon4 icon_a3"></span>
                    </p>
                    <p class="auth4">
                        <span class="auth_save">保存</span>
                        <span class="icon4 icon_a4"></span>
                    </p>
                    <span class="icon5 icon_a5">分配对应角色<br/>的系统功能权限</span>
                </div>
            </div>
            <!--====用户权限初始化 步骤一 ed====-->
            <!--====用户权限初始化 步骤二 start====-->
            <div class="warehouse_init userauth_init">
                <p>步骤二：<em>添加系统操作用户</em></p>
                <div>
                    <div class="line"></div>
                    <span class="icon1 icon_w1" id="icon1 icon_w11">用户列表</span>
                    <p class="auth3">
                        <span>新增角色</span>
                        <span class="icon4 icon_a3"></span>
                    </p>
                    <p class="auth4">
                        <span class="auth_save">保存</span>
                        <span class="icon4 icon_a4"></span>
                    </p>
                    <span class="icon5 icon_a5">点击账号前角色列表<br/>按钮设置角色</span>
                </div>
            </div>
            <!--====用户权限初始化 步骤二 ed====-->
        </div>
        <!--====用户权限初始化 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
<script>
    document.getElementById('icon1 icon_w1').onclick=function(){ openPage('<?php echo base64_encode('?app_act=sys/role/do_list') ?>', '?app_act=sys/role/do_list', '角色列表');}

    document.getElementById('icon1 icon_w11').onclick=function(){ openPage('<?php echo base64_encode('?app_act=sys/user/do_list') ?>', '?app_act=sys/user/do_list', '用户列表');}

</script>
</body>
</html>