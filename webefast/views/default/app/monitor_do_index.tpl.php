<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
<title>系统监控</title>
<?php app_css('efast365_wechat.css');?>
</head>

<body>
<div class="system_wrap">
  <?php 

  app_tpl('web_app_top');?>
    <table class="monitor_item">
    	<tr>
        	<!--td><a href="#" class="unit cwjk"><i class="icon"></i>错误监控</a></td-->
            <td><a  href="?app_act=app/monitor/api_record" class="unit ldcx"><i class="icon"></i>漏单监控</a></td>
            <!--td><a href="#" class="unit dpsq"><i class="icon"></i>店铺授权</a></td-->
        </tr>
        <!--tr>
        	<td><a href="#" class="unit erp"><i class="icon"></i>ERP接口</a></td>
            <td><a href="#" class="unit wms"><i class="icon"></i>WMS接口</a></td>
            <td><a href="#" class="unit cpjy"><i class="icon"></i>产品建议</a></td>
        </tr-->
    </table>

    <?php app_tpl('web_app_bottom');?>
    
</div>

<?php app_js('jquery.min.js','2.1.4');?>
<?php app_js('app.js');?>
<script>
$(function(){
	$(".top_title .explain").click(function(){
		$(".pop_layer").show();
		})
	$(".pop_layer .close").click(function(){
		$(".pop_layer").hide();
		})
	$(".top_title .menu").click(function(){
		$(".menu_pop").toggle();
		})
		
	
})
</script>
</body>
</html>
