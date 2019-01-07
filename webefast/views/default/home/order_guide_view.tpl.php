<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>订单审核引导</title>
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
        <!--====订单审核引导 主体 start====-->
        <div class="warehouse_init order_init">
            <p>操作流程</p>
            <div class="clearfix">
                <div class="order_fl fl">
                    <div>
                        <span class="icon5 icon_e11" id="icon5 icon_e11">订单列表（待确认<br>页签正常类订单）</span>
                    </div>
                    <div class="order_middle">
                        <span class="icon2 icon_e12" id="icon2 icon_e12">问题订单列表</span>
                        <p class="order1">
                            <span>返回正常单</span>
                            <span class="icon4 icon_e13"></span>
                        </p>
                        <p class="order2">
                            <span>解除缺货</span>
                            <span class="icon4 icon_e14"></span>
                        </p>
                        <span class="icon2 icon_e15" id="icon2 icon_e15">缺货订单列表</span>
                    </div>
                    <div>
                        <p class="order3">
                            <span>解挂</span>
                            <span class="icon4 icon_e16"></span>
                        </p>
                        <span class="icon2 icon_e17" id="icon2 icon_e17">挂起订单列表</span>
                    </div>
                </div><!--order_fl-->
                <div class="order_fr fl">
                     <div class="line_e1"></div>
                     <p class="order4">
                        <span>检查订单发货仓/配送方式</span>
                        <span class="icon4 icon_e21"></span>
                    </p>
                    <p class="order5">
                        <span>订单确认/批量确认</span>
                        <span class="icon4 icon_e22"></span>
                    </p>
                    <p class="order6">
                        <span>操作试试</span>
                        <span class="icon3 icon_e23"></span>
                    </p>
                    <span class="icon6 icon_e24" id="icon6 icon_e24">
                         还是不会<br>（操作演示）</span>
                </div><!--order_fr ed-->
            </div>
        </div>
        <!--====订单审核引导 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
<script>
    document.getElementById('icon5 icon_e11').onclick=function(){ openPage('<?php echo base64_encode('?app_act=oms/sell_record/ex_list') ?>', '?app_act=oms/sell_record/ex_list', '订单列表');}

    document.getElementById('icon2 icon_e12').onclick=function(){ openPage('<?php echo base64_encode('?app_act=oms/sell_record/question_list') ?>', '?app_act=oms/sell_record/question_list', '问题订单列表');}

    document.getElementById('icon2 icon_e15').onclick=function(){ openPage('<?php echo base64_encode('?app_act=oms/sell_record/short_list') ?>', '?app_act=oms/sell_record/short_list', '缺货订单列表');}

    document.getElementById('icon2 icon_e17').onclick=function(){ openPage('<?php echo base64_encode('?app_act=oms/sell_record/pending_list') ?>', '?app_act=oms/sell_record/pending_list', '挂起订单列表');}

    document.getElementById('icon6 icon_e24').onclick=function(){ window.open("http://operate.baotayun.com:8080/operate-demo/page/order/kfsd/kfsd1.php", "_blank");}
</script>
</body>
</html>

