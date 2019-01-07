<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>退货入库引导</title>
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
        a:hover,a:active,a:visited{!important;text-decoration: none;}
        a:link{
            outline: medium none;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="inner">
        <!--====退货入库引导 主体 start====-->
        <div>
            <!--====退货入库引导 方式一 start====-->
            <div class="warehouse_init userauth_init">
                <p>方式一：<em>售后服务单确认入库</em></p>
                <div>
                    <div class="line"></div>
                    <span class="icon2 icon_r1" id="icon2 icon_r1">售后服务单</span>
                    <p class="return1">
                        <span>确认入库</span>
                        <span class="icon4 icon_r2"></span>
                    </p>
                    <p class="return2">
                        <span>我去试试</span>
                        <span class="icon3 icon_r3"></span>
                    </p>
                    <span class="icon6 icon_r4">还是不会<br/>（操作演示）</span>
                </div>
            </div>
            <!--====退货入库引导 方式一 ed====-->
            <!--====退货入库引导 方式二 start====-->
            <div class="warehouse_init userauth_init">
                <p>方式二：<em>退货包裹单确认入库</em></p>
                <div>
                    <div class="line"></div>
                    <span class="icon2 icon_r1" id="icon2 icon_r11">退货包裹单</span>
                    <p class="return1">
                        <span>确认入库</span>
                        <span class="icon4 icon_r2"></span>
                    </p>
                    <p class="return2">
                        <span>我去试试</span>
                        <span class="icon3 icon_r3"></span>
                    </p>
                    <span class="icon6 icon_r4" id="icon6 icon_r4">还是不会<br/>（操作演示）</span>
                </div>
            </div>
            <!--====退货入库引导 方式二 ed====-->
        </div>
        <!--====退货入库引导 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
<script>
    document.getElementById('icon2 icon_r1').onclick=function(){ openPage('<?php echo base64_encode('?app_act=oms/sell_return/after_service_list') ?>', '?app_act=oms/sell_return/after_service_list', '售后服务单');}

    document.getElementById('icon2 icon_r11').onclick=function(){ openPage('<?php echo base64_encode('?app_act=oms/sell_return/package_list') ?>', '?app_act=oms/sell_return/package_list', '退货包裹单');}

    document.getElementById('icon6 icon_r4').onclick=function(){ window.open("http://operate.baotayun.com:8080/operate-demo/page/refund/qrsh/qrsh1.php", "_blank");}
</script>
</body>
</html>