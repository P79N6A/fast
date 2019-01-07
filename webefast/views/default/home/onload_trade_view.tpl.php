<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>下载平台交易</title>
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
        <!--====下载平台交易 主体 start====-->
        <div class="warehouse_init trade_init">
            <p>操作流程</p>
            <div>
                <div class="clearfix onload_trade">
                    <div class="line_t1">
                        <span class="icon2 icon_t1" id="icon2 icon_t1">平台交易列表</span>
                        <p class="trade1">
                            <span>下载订单</span>
                            <span class="icon4 icon_t11"></span>
                        </p>
                        <span class="icon2 icon_t12">设置下载时间段</span>
                   </div><!--line_t1 ed-->
                   <div class="line_t2">
                        <p class="trade2">
                            <span>选择平台/店铺</span>
                            <span class="icon4 icon_t21"></span>
                        </p>
                         <p class="trade3">
                            <span>点击下载</span>
                            <span class="icon4 icon_t31"></span>
                        </p>
                         <p class="trade4">
                            <span>我去试试</span>
                            <span class="icon3 icon_t41"></span>
                        </p>
                        <span class="icon6 icon_t6" id="icon6 icon_t6">
                            <a href="http://operate.baotayun.com:8080/operate-demo/page/order/ddxz/ddxz1.php" target="_blank">还是不会<br>（操作演示）</a></span>
                   </div><!--line_t2 ed-->
                </div>
            </div>
        </div>
        <!--====下载平台交易 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
<script>
    document.getElementById('icon2 icon_t1').onclick=function(){ openPage('<?php echo base64_encode('?app_act=oms/sell_record/td_list') ?>', '?app_act=oms/sell_record/td_list', '平台交易列表');}
    document.getElementById('icon6 icon_t6').onclick=function(){ window.open("http://operate.baotayun.com:8080/operate-demo/page/order/ddxz/ddxz1.php", "_blank");}
</script>
</body>
</html>

