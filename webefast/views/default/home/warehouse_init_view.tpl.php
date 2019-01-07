<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>仓库初始化</title>
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
        <!--====仓库初始化 主体 start====-->
        <div class="warehouse_init">
            <p>方式一：<em>手工添加仓库</em></p>
            <div>
                <div class="line"></div>
                <span class="icon1 icon_w1" id="icon1 icon_w1">仓库列表</span>
                <span class="icon2 icon_w2">添加仓库</span>
                <p class="ware3">
                    <span>设置是否允许负库存发货</span>
                    <span class="icon4 icon_w3"></span>
                </p>
                <p class="ware4">
                    <span class="ware_save">保存</span>
                    <span class="icon4 icon_w4"></span>
                </p>
            </div>
        </div>
        <!--====仓库初始化 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
<script>
    document.getElementById('icon1 icon_w1').onclick=function(){ openPage('<?php echo base64_encode('?app_act=base/store/do_list') ?>', '?app_act=base/store/do_list', '仓库列表');}
</script>
</body>
</html>