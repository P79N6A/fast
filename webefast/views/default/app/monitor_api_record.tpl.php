
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
<title>漏单监控</title>
<?php app_css('efast365_wechat.css');?>
</head>

<body style="background:#e7e7e7;">
<div class="miss_wrap">
    <?php app_tpl('web_app_top_back');?>
   <ul class="last_three">
        <?php foreach($response['date_arr'] as $val):?>
    	<li><span class="day"><?php echo $val;?></span></li>
        <?php endforeach;?>
    </ul>

        
        
        <p class="update_time">更新时间:<span id="update_time">2015.10.10 12:00:00</span></p>
    <table class="order_list" >
    	<tr><th>店铺</th><th>平台单量</th><th>系统单量</th></tr>
    </table>
      
    <?php app_tpl('web_app_bottom');?>
    <div class="pop_layer">
    	<div class="explan_cont">
        	<h2 class="title">说 明</h2>
            <p class="txt">*  仅显示淘宝平台店铺</p>
            <p class="txt">*  根据交易创建时间进行下载对比</p>
            <p class="txt">*  数据动态刷新，默认15分钟更新一次</p>
            <button class="close" type="button">关 闭</button>
        </div>
    </div>
    
</div>

<?php app_js('jquery.min.js','2.1.4');?>
<?php app_js('app.js');?>
<script>
$(function(){  
	$(".top_title .explain").click(function(){
		$(".pop_layer").show();
		});
	$(".pop_layer .close").click(function(){
		$(".pop_layer").hide();
		});
	$(".last_three li").click(function(){
		$(this).addClass("curr").siblings().removeClass("curr");
                 var url = "?app_act=app/monitor/get_api_record";
                var data = {};
                 data.date= $(this).find("span").text();
                  $.post(url,data,function(ret){
                      $('#update_time').html(ret.update_time);
                      set_record_list(ret.data);
                  },'json');
                
                
		});
        
                $(".last_three li").eq(2).click();
});
function set_record_list(data){
    $('.order_record').remove();
     var order_record_list = '';
    $.each(data,function(i,row){
         order_record_list += '<tr class="order_record"><td>'+row.shop_name+'</td><td>'+row.taobao_order_total+'</td><td>'+row.base_order_total+'</td></tr>';
    });
    
    $('.order_list').append(order_record_list);
}
</script>
</body>
</html>
