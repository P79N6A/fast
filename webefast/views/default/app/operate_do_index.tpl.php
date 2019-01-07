<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
<title>运营分析</title>
<?php app_css('efast365_wechat.css');?>
</head>

<body>
<div class="operation_wrap">
  <?php 
  app_tpl('web_app_top');?>
    <?php app_tpl('web_app_bottom');?>
        <table class="operation_item">
    	<tr>
        	<td><a href="?app_act=app/operate/sell_analysis" class="unit xsfx"><i class="icon"></i>销售分析</a></td>
<!--            <td>
                <a href="#" class="unit fhfx"><i class="icon"></i>发货分析</a>
            </td>-->
            <td><a href="?app_act=app/operate/goods_sell_ranking" class="unit xsph"><i class="icon"></i>商品销售排行</a></td>
        </tr>
        <tr>
<!--        	<td>
                    <a href="#" class="unit thfx"><i class="icon"></i>退货分析</a>
                </td>-->
            <td></td>
            <td></td>
        </tr>
    </table>

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
