<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>库存初始化</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="zh-CN" />
<meta content="all" name="robots" />
<meta name="Copyright" content="" />
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" type="text/css" href="echarts/css/basic.css"/>
<link rel="stylesheet" type="text/css" href="echarts/css/common.css"/>
<link rel="stylesheet" type="text/css" href="echarts/css/system.css"/>
</head>
<body>
<div class="container">
    <div class="inner">
        <!--====库存初始化 主体 start====-->
        <div>
            <!--====库存初始化 方式一 start====-->
            <div class="warehouse_init trade_init">
                <p>方式一：<em>通过盘点单初始化系统库存（将对应仓库对应商品库存调整成盘点单中商品对应的库存数量）</em></p>
                 <div>
                    <div class="stock_num">
                        <div>
                            <span class="icon2 icon_s11">盘点单</span>
                            <p class="stock1">
                                <span>添加盘点单</span>
                                <span class="icon4 icon_s12"></span>
                            </p>
                            <span class="icon2 icon_s13">设置盘点仓库</span>
                            <p class="stock2">
                                <span>保存</span>
                                <span class="icon4 icon_s14"></span>
                            </p>
                            <p class="stock3">
                                <span>添加盘点商品明细（条码级）</span>
                                <span class="icon4 icon_s15"></span>
                            </p>
                            <p class="stock4">
                                <span>确认</span>
                                <span class="icon4 icon_s16"></span>
                            </p>
                            <span class="icon2 icon_s17">一键盘点</span>
                             <p class="stock5">
                                <span>选择盘点日期/仓库</span>
                                <span class="icon4 icon_s18"></span>
                            </p>
                            <p class="stock6">
                                <span>选择盘点类型</span>
                                <span class="icon4 icon_s19"></span>
                            </p>
                            <span class="icon8 icon_s110">开始盘点</span>
                            <span class="icon5 icon_s111">完成库存初始化<br>（库存检查）</span>
                        </div>
                    </div><!--leadin_goods ed-->
                </div>
            </div>
            <!--====库存初始化 方式一 ed====-->
            <!--====库存初始化 方式二 start====-->
            <div class="warehouse_init trade_init">
                <p>方式二：<em>通过调整单初始化系统库存（在对应仓库现有库存记录上进行调整数量的增减操作）</em></p>
               <div>
                    <div class="clearfix stock_opera">
                        <div class="line_s2">
                           <span class="icon2 icon_s21">调整单</span>
                           <p class="stock7">
                                <span>添加仓库调整单</span>
                                <span class="icon4 icon_s22"></span>
                           </p>
                           <p class="stock8">
                                <span>设置调整仓库和调整原因</span>
                                <span class="icon4 icon_s23"></span>
                           </p>
                       </div><!--line_t1 ed-->
                       <div class="line_s3">
                             <p class="stock9">
                                <span>保存</span>
                                <span class="icon4 icon_s31"></span>
                            </p>
                            <span class="icon5 icon_s32">添加调整商品<br>明细数量（条码级）</span>
                            <p class="stock10">
                                <span>验收</span>
                                <span class="icon4 icon_s33"></span>
                           </p>
                           <span class="icon5 icon_s34">完成库存调整<br>（库存检查）</span>
                       </div><!--line_t2 ed-->
                    </div>
                </div>
            </div>
            <!--====库存初始化 方式二 ed====-->
            <p class="stock_detail">外部系统对接等情况，请咨询实施顾问或对应服务工程师如何操作。</p>
        </div>
        <!--====库存初始化 主体 ed====-->
    </div><!--inner ed-->
</div><!--container ed-->
<script src="echarts/js/jquery-1.8.3.min.js"></script>
</body>
</html>

