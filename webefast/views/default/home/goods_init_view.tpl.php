<!doctype html>
<html xmlns="http://www.w3.org/1999/html">
<head>
<meta charset="utf-8">
<title>商品初始化</title>
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
        <!--====商品初始化 主体 start====-->
        <div>
            <!--====商品初始化 方式一 start====-->
            <div class="warehouse_init trade_init">
                <p>方式一：<em>手工添加商品</em></p>
                <div>
                    <div class="clearfix onload_trade">
                        <div class="line_t1">
                            <span class="icon1 icon_d1" id="icon1 icon_d1">商品列表</span>
                            <p class="trade1">
                                <span>添加商品</span>
                                <span class="icon4 icon_t11"></span>
                            </p>
                            <span class="icon2 icon_t12">填写商品基础信息</span>
                       </div><!--line_t1 ed-->
                       <div class="line_t2">
                            <p class="goods2">
                                <span>保存</span>
                                <span class="icon4 icon_d21"></span>
                            </p>
                            <p class="goods3">
                                <span>设置商品规格信息</span>
                                <span class="icon4 icon_d31"></span>
                            </p>
                            <span class="icon5 icon_d6">填写对应规格<br>商品的条码信息</span>
                             <p class="goods4">
                                <span>保存</span>
                                <span class="icon4 icon_d41"></span>
                            </p>
                       </div><!--line_t2 ed-->
                    </div>
                </div>
            </div>
            <!--====商品初始化 方式一 ed====-->
            <!--====商品初始化 方式二 start====-->
            <div class="warehouse_init trade_init">
                <p>方式二：<em>批量导入商品资料</em></p>
                <div>
                    <div class="leadin_goods">
                        <div>
                            <span class="icon2 icon_g21">商品信息导入</span>
                             <p class="goods5">
                                <span>规格1（规格2）模板下载</span>
                                <span class="icon4 icon_g22"></span>
                            </p>
                            <p class="goods6">
                                <span>规格1（规格2）信息填写</span>
                                <span class="icon4 icon_g23"></span>
                            </p>
                            <p class="goods7">
                                <span>规格1（规格2）导入</span>
                                <span class="icon4 icon_g24"></span>
                            </p>
                            <span class="icon5 icon_g25">混合数据导入<br>模板下载</span>
                            <p class="goods8">
                                <span>商品信息填写</span>
                                <span class="icon4 icon_g26"></span>
                            </p>
                             <p class="goods9">
                                <span>商品资料混合数据导入</span>
                                <span class="icon4 icon_g27"></span>
                            </p>
                            <span class="icon5 icon_g28">商品列表/条码管理<br>导入数据检查</span>
                        </div>
                    </div><!--leadin_goods ed-->
                </div>
            </div>
            <!--====商品初始化 方式二 start====-->
        </div>
        <!--====商品初始化 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
<script>
    document.getElementById('icon1 icon_d1').onclick=function(){ openPage('<?php echo base64_encode('?app_act=prm/goods/do_list') ?>', '?app_act=prm/goods/do_list', '商品列表');}
</script>
</body>
</html>

