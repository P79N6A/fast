
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>首页</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="zh-CN" />
<meta content="all" name="robots" />

<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" type="text/css" href="echarts/css/basic.css"/>
<link rel="stylesheet" type="text/css" href="echarts/css/common.css"/>
<link rel="stylesheet" type="text/css" href="echarts/css/system.css"/>
    <style>
        a:hover,a:active,a:visited{!important;text-decoration: none;}
    </style>
</head>

<body>
<script>
    document.domain = 'baotayun.com';
</script>
<?php echo load_js('jquery-1.8.1.min.js');?>
<?php echo load_js('core.min.js');?>
<?php echo load_js('base64.js',true);?>
<div class="container">
    <div class="inner">
          <div class="orderTitle"> 
              <a href="javascript:echarts_index()" class="current">新人上手</a>
              <a href="javascript:echarts_index_data()" class="current_2">数据统计</a>
          </div>
          <div class="order_state">
              <?php echo load_js('util/echarts.common.min.js'); ?>
              <!--=====新人上手 start=====-->
              <div class="people_handle">
                  <div class="system_deploy">
                        <p><a href="javascript:;">系统配置</a></p>
                        <div>
                            <ul class="clearfix">
                                <li class="warehouse"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/warehouse_init/view') ?>', '?app_act=home/warehouse_init/view', '仓库初始化');"><i></i><em>仓库初始化</em></a></li>
                                <li class="store"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/store_init/view') ?>', '?app_act=home/store_init/view', '店铺初始化');"><i></i><em>店铺初始化</em></a></li>
                                <li class="goods"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/goods_init/view') ?>', '?app_act=home/goods_init/view', '商品初始化');"><i></i><em>商品初始化</em></a></li>
                            </ul>
                            <ul class="clearfix">
                                <li class="stock"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/stock_init/view') ?>', '?app_act=home/stock_init/view', '库存初始化');"><i></i><em>库存初始化</em></a></li>
                                <li class="user"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/userauth_init/view') ?>', '?app_act=home/userauth_init/view', '用户权限初始化');"><i></i><em>用户权限初始化</em></a></li>
                                <li class="roof"><a href="javascript:;"><i></i><em>平台商品绑定设置</em></a></li>
                            </ul>
                            <ul class="clearfix">
                                <li class="send"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/shipping_module/view') ?>', '?app_act=home/shipping_module/view', '配送方式及模板设置');"><i></i><em>配送方式及模板设置</em></a></li>
                                <li class="order"><a href="javascript:openPage('<?php echo base64_encode('?app_act=oms/order_check_strategy/do_list') ?>', '?app_act=oms/order_check_strategy/do_list', '订单审核规则设置');"><i></i><em>订单审核规则设置</em></a></li>
                                <li class="system"><a href="javascript:openPage('<?php echo base64_encode('?app_act=sys/schedule/do_list') ?>', '?app_act=sys/schedule/do_list', '系统自动服务设置');"><i></i><em>系统自动服务设置</em></a></li>
                            </ul>
                        </div>
                  </div><!--系统配置 ed-->
                  <div class="opera_guide">
                      <p><a href="javascript:;">操作引导</a></p>
                        <div>
                            <ul class="clearfix">
                                <li class="onload"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/onload_trade/view') ?>', '?app_act=home/onload_trade/view', '下载平台交易');"><i></i><em>下载平台交易</em></a></li>
                                <li class="look"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/order_guide/view') ?>', '?app_act=home/order_guide/view', '订单审核');"><i></i><em>订单审核</em></a></li>
                                <li class="list"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/sendgoods_guide/view') ?>', '?app_act=home/sendgoods_guide/view', '打单发货');"><i></i><em>打单发货</em></a></li>
                            </ul>
                            <ul class="clearfix">
                                <li class="back"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/back_guide/view') ?>', '?app_act=home/back_guide/view', '下载平台退单');"><i></i><em>下载平台退单</em></a></li>
                                <li class="exchange"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/exchange_goods/view') ?>', '?app_act=home/exchange_goods/view', '审核退单/换货');"><i></i><em>审核退单/换货</em></a></li>
                                <li class="obtain"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/return_guide/view') ?>', '?app_act=home/return_guide/view', '收包裹入库');"><i></i><em>收包裹入库</em></a></li>
                            </ul>
                            <ul class="clearfix">
                                <li class="select"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/select_guide/view') ?>', '?app_act=home/select_guide/view', '采购入库');"><i></i><em>采购入库</em></a></li>
                                <li class="wholesale"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/wholesale_guide/view') ?>', '?app_act=home/wholesale_guide/view', '批发出库');"><i></i><em>批发出库</em></a></li>
                                <li class="check"><a href="javascript:openPage('<?php echo base64_encode('?app_act=home/stock_guide/view') ?>', '?app_act=home/stock_guide/view', '库存盘点');"><i></i><em>库存盘点</em></a></li>
                            </ul>
                        </div>
                  </div><!--操作引导 ed-->
              </div>
              <!--=====新人上手 ed=====-->
              <div class="none"></div>
          </div>
    </div><!--inner ed-->
</div><!--container ed-->

<script>
    function echarts_index(){
        window.location.href = '?app_act=sys/echarts/view_index';
    }
    function echarts_index_data(){
        window.location.href = '?app_act=sys/echarts/view_index_data';
    }
</script>
</body>
</html>