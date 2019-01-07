<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>配送方式及模板设置</title>
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
        <!--====配送方式及模板设置 主体 start====-->
        <div>
            <!--====配送方式及模板设置 步骤一 start====-->
            <div class="warehouse_init shipping_module">
                <p>步骤一：<em>设置打印模板</em></p>
                <div>
                    <div class="line"></div>
                    <span class="icon2 icon_m1">快递单模板</span>
                    <p class="shipping2">
                        <span>编辑模板</span>
                        <span class="icon4 icon_m2"></span>
                    </p>
                    <span class="icon5 icon_m3">调整模板成<br/>期望的打印格式</span>
                    <p class="ware4">
                        <span class="ware_save">保存</span>
                        <span class="icon4 icon_w4"></span>
                    </p>
                    <p class="detail_text">云栈打印模板、云打印模板请参考在线帮助相关专题设置说明进行配置</p>
                </div>
            </div>
            <!--====配送方式及模板设置 步骤一 start====-->
            <!--====配送方式及模板设置 步骤二 start====-->
            <div class="warehouse_init shipping_module">
                <p>步骤二：<em>配置配送方式</em></p>
                <div>
                    <div class="line"></div>
                    <span class="icon2 icon_m1" id="icon2 icon_m1">配送方式列表</span>
                    <p class="shipping22">
                        <span>启用/添加配送方式</span>
                        <span class="icon4 icon_m22"></span>
                    </p>
                    <p class="wshipping33">
                        <span class="shipp_type">设置打印类型<br/>（普通/直连/云栈）</span>
                        <span class="icon4 icon_w4"></span>
                    </p>
                    <span class="icon5 icon_m44">设置对应类型使用的打印模板<br/>（模板名称对应打印模板的名称）</span>
                </div>
            </div>
            <!--====配送方式及模板设置 步骤二 start====-->
        </div>
        <!--====配送方式及模板设置 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
<script>
    document.getElementById('icon2 icon_m1').onclick=function(){ openPage('<?php echo base64_encode('?app_act=base/shipping/do_list') ?>', '?app_act=base/shipping/do_list', '配送方式列表');}
</script>
</body>
</html>