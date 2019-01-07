<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
<title>首页</title>

<?php app_css('efast365_wechat.css,swiper.3.1.7.min.css');?>

</head>

<body>
<div class="index_wrap">

    <?php app_tpl('web_app_top');?>
    
    <p class="update_time">更新时间:<span id="update_time"></span></p>
    <table class="compass">
    	<tr>
        	<td><p class="num" id="sell_num">0</p><p class="name"><i class="icon cjl"></i>成交量</p></td><td><p class="num" id="sell_money">0</p><p class="name"><i class="icon cjje"></i>成交金额</p></td>
        </tr>
        <tr>
        	<td><p class="num" id="wait_confirm">0</p><p class="name"><i class="icon dqr"></i>待确认</p></td><td><p class="num" id="wait_create_waves">0</p><p class="name"><i class="icon djh"></i>待拣货</p></td>
        </tr>
        <tr>
        	<td><p class="num" id="wait_scan">0</p><p class="name"><i class="icon dsm"></i>待扫描</p></td><td><p class="num" id="oms_send">0</p><p class="name"><i class="icon yfh"></i>已发货</p></td>
        </tr>
        <tr>
        <td><p class="num" id="pre_sell_num"><?php echo  $response['pre_data']['sell_num'] ?></p><p class="name"><i class="icon yest_cj"></i>昨日成交量</p></td><td><p class="num" id="pre_oms_send"><?php echo  $response['pre_data']['oms_send'] ?></p><p class="name"><i class="icon yest_fh"></i>昨日发货量</p></td>
        </tr>
    </table>
    <!--div class="items_query_warp swiper-container">
      <ul class="items_query swiper-wrapper">
          <li class="one_screen swiper-slide">
              <a class="unit dingd" href="#"><i class="icon"></i>订单查询</a>
              <a class="unit kucun" href="#"><i class="icon"></i>库存查询</a>
              <a class="unit dingd" href="#"><i class="icon"></i>订单查询</a>
          </li>
          <li class="one_screen swiper-slide">
              <a class="unit dingd" href="#"><i class="icon"></i>订单查询</a>
              <a class="unit kucun" href="#"><i class="icon"></i>库存查询</a>
              <a class="unit dingd" href="#"><i class="icon"></i>订单查询</a>
          </li>
          
      </ul>
      <div class="swiper-pagination"></div>
    </div-->
    <?php app_tpl('web_app_bottom');?>
    <div class="pop_layer">
    	<div class="explan_cont">
        	<h2 class="title">说 明</h2>
            <p class="txt">* 今日成交订单数和金额均为全店铺合计数据</p>
            <p class="txt">*  数据动态刷新，默认15分钟更新一次</p>
            <button class="close" type="button">关 闭</button>
        </div>
    </div>
    
</div>


<?php app_js('jquery.min.js','2.1.4');?>
<?php app_js('swiper.3.1.7.jquery.min.js');?>
<?php app_js('app.js');?>
<script>
$(function(){
        $('.explain').show();
	$(".top_title .explain").click(function(){
		$(".pop_layer").show();
		});
	$(".pop_layer .close").click(function(){
		$(".pop_layer").hide();
		});
	$(".top_title .menu").click(function(){
		$(".menu_pop").toggle();
		});
	
      get_luopan_data();

});
function unix_to_datetime(unix) {
    var now = new Date(parseInt(unix) * 1000);
    return now.toLocaleString().replace(/-|-/g, "-").replace(/ /g, " ");
}
function get_luopan_data(){
      var url = "?app_act=oms/report_day/get_data";
                var data = {app_fmt:'json'};
                  $.post(url,data,function(ret){
                         $('#update_time').html(ret.update_time);
                      $.each(ret.data,function(index,row){
                          $('#'+row.type).html(row.report_data);
                      });
      },'json'); 
      setTimeout(function(){get_luopan_data();},600000);
}



</script>
</body>
</html>
