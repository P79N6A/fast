<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>退单下载引导</title>
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
        <!--====退单下载引导 主体 start====-->
            <div class="warehouse_init trade_init">
                <p>操作流程</p>
               <div>
                    <div class="clearfix stock_opera">
                        <div class="line_s2 line_w1">
                           <span class="icon5 icon_b11">开启下载退单<br>自动服务</span>
                           <p class="back1">
                                <span id="icon4 icon_b11">平台退单列表</span>
                                <span class="icon4 icon_b12"></span>
                           </p>
                       </div><!--line_t1 ed-->
                       <div class="line_s3 line_w2">
                            <span class="icon2 icon_b21">设置下载时间段</span>
                            <p class="back2">
                                <span>选择平台/店铺</span>
                                <span class="icon4 icon_b22"></span>
                            </p>
                            <p class="back3">
                                <span>点击查询</span>
                                <span class="icon4 icon_b23"></span>
                            </p>
                            <p class="back4">
                                <span>我去试试</span>
                                <span class="icon3 icon_b24"></span>
                            </p>
                            <span class="icon6 icon_b25" id="icon6 icon_b25">还是不会<br>（操作演示）</span>
                       </div><!--line_t2 ed-->
                    </div>
                </div>
        </div>
        <!--====退单下载引导主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
<script>
    document.getElementById('icon4 icon_b11').onclick=function(){ openPage('<?php echo base64_encode('?app_act=api/sys/order_refund/do_list') ?>', '?app_act=api/sys/order_refund/do_list', '平台退单列表');}
    document.getElementById('icon6 icon_b25').onclick=function(){ window.open("http://operate.baotayun.com:8080/operate-demo/page/refund/tdxz/tdxz1.php", "_blank");}
</script>
</body>
</html>

