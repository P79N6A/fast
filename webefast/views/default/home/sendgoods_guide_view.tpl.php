<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>打单发货引导</title>
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
        <!--====打单发货引导 主体 start====-->
        <div class="warehouse_init send_init">
            <p>操作流程</p>
            <div class="clearfix">
                <div class="send_fl fl">
                    <div class="send_f11">
                         <span class="icon2 icon_o11">订单波次生成</span>
                         <p class="send1">
                            <span>生成波次</span>
                            <span class="icon4"></span>
                         </p>
                        <span class="icon2 icon_o12">订单波次打印</span>
                    </div><!--send_f11 ed-->
                    <div class="send_f12"></div><!--send_f12 ed-->
                </div><!--send_fl ed-->
                <div class="send_f2 fl">
                     <div class="send_f21">
                         <div class="send_211">
                              <p class="send2">
                                <span>获取热敏单号</span>
                                <span class="icon4 icon_o13"></span>
                             </p>
                             <p class="send3">
                                <span>打印热敏快递单</span>
                                <span class="icon4 icon_o14"></span>
                             </p>
                         </div><!--send_211 ed-->
                         <div class="send_212">
                             <p class="send4">
                                <span>打印发货单（可选）</span>
                                <span class="icon4 icon_o15"></span>
                             </p>
                             <p class="send5">
                                <span>打印波次单（可选）</span>
                                <span class="icon4 icon_o16"></span>
                             </p>
                             <p class="send_text">热敏快递</p>
                         </div><!--send_212 ed-->
                     </div><!--send_f21-->
                     <div class="send_f21 send_f22">
                         <div class="send_211">
                              <p class="send2">
                                <span>快递单号匹配</span>
                                <span class="icon4 icon_o13"></span>
                             </p>
                             <p class="send3 send31">
                                <span>打印普通纸质快递单</span>
                                <span class="icon4 icon_o14"></span>
                             </p>
                         </div><!--send_211 ed-->
                         <div class="send_212">
                             <p class="send4">
                                <span>打印发货单（可选）</span>
                                <span class="icon4 icon_o15"></span>
                             </p>
                             <p class="send5">
                                <span>打印波次单（可选）</span>
                                <span class="icon4 icon_o16"></span>
                             </p>
                             <p class="send_text">普通快递</p>
                         </div><!--send_212 ed-->
                     </div><!--send_f21-->
                </div><!--send_f2 ed-->
                <div class="send_f3 fl"></div><!--send_f3 ed-->
                <div class="send_f4 fl">
                    <span class="icon2 icon_o17">波次单验收</span>
                </div><!--send_f4 ed--><!--send_f4 ed-->
                <div class="send_f5 fl">
                    <span class="icon2 icon_o18">整单发货</span>
                    <span class="send6 send_no">否</span>
                    <span class="icon7 icon_o19">扫描验货</span>
                    <span class="send6 send_yes">是</span>
                    <span class="icon2 icon_o20">扫描验货出库</span>
                </div><!--send_f5 ed-->
                <div class="send_f6 fl">
                    <p class="send7">
                        <span>操作试试</span>
                        <span class="icon3 icon_o21"></span>
                    </p>
                     <span class="icon6 icon_o22">
                         <a href="http://operate.baotayun.com:8080/operate-demo/page/order/scbc/scbc1.php" target="_blank">还是不会<br>（操作演示）</a></span>
                </div><!--send_f6 ed-->
            </div>
        </div>
        <!--====打单发货引导 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
</body>
</html>

