<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>店铺初始化</title>
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
        <!--====店铺初始化 主体 start====-->
        <div>
            <!--====店铺初始化 方式一 start====-->
            <div class="warehouse_init trade_init">
                <p>方式一：<em>手工添加店铺</em></p>
                 <div>
                    <div class="store_handle">
                        <div>
                            <span class="icon5 icon_h11">
                                <a href="http://operate.baotayun.com:8080/efast365-help/?p=818" target="_blank">
                                参考对应平台<br>应用订购对接说明
                                </a>
                            </span>
                            <p class="store1">
                                <span>应用订购</span>
                                <span class="icon4 icon_h12"></span>
                            </p>
                            <span class="icon8 icon_h13" id="icon8 icon_h13">店铺列表</span>
                            <p class="store2">
                                <span>添加店铺</span>
                                <span class="icon4 icon_h14"></span>
                            </p>
                            <p class="store3">
                                <span>选择平台类型</span>
                                <span class="icon4 icon_h15"></span>
                            </p>
                            <p class="store4">
                                <span>设置默认收发货仓库</span>
                                <span class="icon4 icon_h16"></span>
                            </p>
                            <p class="store5">
                                <span>设置默认配送方式</span>
                                <span class="icon4 icon_h17"></span>
                            </p>
                            <p class="store6">
                                <span>保存</span>
                                <span class="icon4 icon_h18"></span>
                            </p>
                            <span class="icon5 icon_h19">点击对应店铺<br>[授权]按钮</span>
                            <p class="store7">
                                <span>设置默认收发货仓库</span>
                                <span class="icon4 icon_h110"></span>
                            </p>
                            <span class="icon2 icon_h111">填写店铺参数</span>
                            <p class="store8">
                                <span>店铺授权状态检查</span>
                                <span class="icon4 icon_h112"></span>
                            </p>
                        </div>
                    </div><!--leadin_goods ed-->
                </div>
            </div>
            <!--====店铺初始化 方式一 ed====-->
            <!--====店铺初始化 方式二 start====-->
            <div class="warehouse_init store_init">
                <p>方式二：<em>自动创建店铺</em></p>
               <div>
                    <div class="line"></div>
                    <span class="icon5 icon_h21" id="icon5 icon_h21">
                        参考对应平台<br>应用订购对接说明</a></span>
                     <p class="store9">
                        <span>应用订购</span>
                        <span class="icon4 icon_h22"></span>
                    </p>
                    <p class="store10">
                        <span>授权并自动生成对接店铺</span>
                        <span class="icon4 icon_h23"></span>
                    </p>
                    <p class="store11">
                        <span>配置检查</span>
                        <span class="icon4 icon_h24"></span> 
                    </p>
                </div>
            </div>
            <!--====店铺初始化 方式二 start====-->
        </div>
        <!--====店铺初始化 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
<script>
    document.getElementById('icon8 icon_h13').onclick=function(){ openPage('<?php echo base64_encode('?app_act=base/shop/do_list') ?>', '?app_act=base/shop/do_list', '店铺列表');}

    document.getElementById('icon6 icon_b25').onclick=function(){ window.open("http://operate.baotayun.com:8080/efast365-help/?p=818", "_blank");}
</script>
</body>
</html>

