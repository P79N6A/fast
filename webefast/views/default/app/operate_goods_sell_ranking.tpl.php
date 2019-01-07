
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
<title>商品销售排行</title>
<?php app_css('efast365_wechat.css');?>
</head>

<body style="background:#e7e7e7;">
<div class="bible_wrap">
    <?php app_tpl('web_app_top_back');?>
    <p class="last_seven">
    	<span class="day day_left ac">近7天</span><span class="day day_right">近30天</span>
    </p>
        
 
   <table class="goods_list" id="day7"><tr><th>商品</th><th>商品规格</th><th>销售数量</th></tr></table>
      <table class="goods_list" id="day30" style="display:none"><tr><th>商品</th><th>商品规格</th><th>销售数量</th></tr></table>
    
      
    <?php app_tpl('web_app_bottom');?>
    <div class="pop_layer">
	<div class="explan_cont">
        	<h2 class="title">说 明</h2>
            <p class="txt">*  不包含当天数据</p>
            <p class="txt">*  某一天销售金额计算方法，以付款时间在这一天的所有有效订单中销售此商品的数量</p>
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
      
        $(".last_seven .day").click(function(){
		$(this).addClass("ac").siblings().removeClass("ac");
                var day = $(this).hasClass('day_left')?7:30;
                   get_goods_sell_data(day);
		});
                get_goods_sell_data(7);
});
function get_goods_sell_data(day){
    var td = $('#day'+day+" td");
    $('.goods_list').hide();
     $('#day'+day).show();

    if(td.length==0){ 
        var url = '?app_act=app/operate/get_goods_sell_ranking&app_fmt=json';
        var data = {};
        data.day=day;
        var  html ='';
        $.post(url,data,function(ret){
            $.each(ret.data,function(i,row){
                html+='  <tr><td class="img_td">'+row.goods_name+'</td><td>'+row.spec+'</td><td>'+row.num+'</td></tr>';
           });
               $('#day'+day).append(html);
        },'json');
    
    }
}


</script>
</body>
</html>
