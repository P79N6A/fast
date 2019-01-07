<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
<title>销售分析</title>
<?php app_css('efast365_wechat.css');?>
</head>

<body style="background:#FFF;">
<div class="analysis_wrap">
   <?php app_tpl('web_app_top_back');?>
        <p class="last_seven">
    	<span class="day day_left ac">近7天</span><span class="day day_right">近30天</span>
    </p>
    <div class="sales_data" id="sales_data7">
    	<div class="total_money"><span class="name">销售总金额（元）</span><span class="val"  >0</span></div>
        <div class="total_num"><span class="name">销售订单数量</span><span class="val"  >0</span></div>
    </div>
    <div class="sales_chart" id="sales_chart7">
    	<p class="tap_head"><span class="tap_title curr" value="money">销售总金额</span><span class="tap_title" value="num">订单数量</span></p>
        <div class="tap_box curr" id="sell_money7" ></div>
        <div class="tap_box" id="sell_goods7" ></div>
        <p class="update_time"><span>更新时间:<label id="update_time7"></label></span></p>
    </div>
   
    
    <div class="sales_data" id="sales_data30" style="display:none">
    	<div class="total_money"><span class="name">销售总金额（元）</span><span class="val"  id="all_money">0</span></div>
        <div class="total_num"><span class="name">销售订单数量</span><span class="val"  id="all_goods">0</span></div>
    </div>
    <div class="sales_chart" id="sales_chart30"  style="display:none">
    	<p class="tap_head"><span class="tap_title curr" value="money">销售总金额</span><span class="tap_title" value="num">订单数量</span></p>
        <div class="tap_box curr" id="sell_money30" ></div>
        <div class="tap_box" id="sell_goods30" ></div>
        <p class="update_time"><span>更新时间:<label id="update_time30"></label></span></p>
    </div>
    
    
      
  <?php app_tpl('web_app_bottom');?>
    <div class="pop_layer">
    	<div class="explan_cont">
        	<h2 class="title">说 明</h2>
            <p class="txt">*  不包含当天数据，当天数据请进入主页查看</p>
            <p class="txt">*  某一天销售金额计算方法，以付款时间在这一天的所有有效订单，考虑到事后可能因买家退款作废订单，所以此数据仅供参考</p>
            <button class="close" type="button">关 闭</button>
        </div>
    </div>
    
</div>

<?php app_js('jquery.min.js','2.1.4');?>
<?php app_js('acharts-min.js','1.0.32');?>
<?php app_js('app.js');?>
<script>
 var now_day = 7;
$(function(){
	$(".top_title .explain").click(function(){
		$(".pop_layer").show();
		})
	$(".pop_layer .close").click(function(){
		$(".pop_layer").hide();
		})
	$(".last_seven .day").click(function(){
		$(this).addClass("ac").siblings().removeClass("ac");
                   now_day = $(this).hasClass('day_left')?7:30;
                  $('.sales_data').hide();
                  $('.sales_chart').hide();
                  $('#sales_data'+now_day).show();
                  $('#sales_chart'+now_day).show();
                  setTimeout(function(){ create_chart();},50);
		});
	
	$(".tap_head .tap_title").each(function(index, element) {
        $(this).click(function(){
			$(this).addClass("curr").siblings().removeClass("curr");
			$(".sales_chart .tap_box").removeClass("curr").eq(index).addClass("curr");
                        create_chart();
			});
    });	
    
    get_sell_analysis(7);
    get_sell_analysis(30);
});
var sell_money_data ={};
var sell_goods_data ={};
var date_arr={};
function get_sell_analysis(day){
    var url = '?app_act=app/operate/get_sell_analysis&app_fmt=json';
        var data = {};
        data.day=day;
        var  html ='';
        $.post(url,data,function(ret){
     
         sell_money_data[day] = ret.data.money_data;
         sell_goods_data[day] = ret.data.goods_data;
          date_arr[day] = ret.data.date_data;
         //set_chart('sell_goods'+day,ret.data.date_data,ret.data.goods_data);
         $('#sales_data'+day+' .total_money .val').html(ret.data.sale_money);
         $('#sales_data'+day+' .total_num .val').html(ret.data.sale_num);
         $('#update_time'+day).html(ret.data.update_time);
            if(day==7){
                set_chart('sell_money'+day,date_arr[day], sell_money_data[day] ); 
            }
        },'json');
    
}
 var width = 0;
 var height = 0;
var  chart ;

function create_chart(){
   var select_value = $('#sales_chart'+now_day+" .tap_head .curr").eq(0).attr("value");
   if(now_day==7){
        if(select_value=='money'){
              set_chart('sell_money'+now_day,date_arr[now_day], sell_money_data[now_day] );   
              $('#sell_money'+now_day).show();
        }else{
             set_chart('sell_goods'+now_day,date_arr[now_day], sell_goods_data[now_day] );  
               $('#sell_goods'+now_day).show();
        }
   }else{
       if(select_value=='money'){
          set_chart30('sell_money'+now_day,date_arr[now_day], sell_money_data[now_day] );   
          $('#sell_money'+now_day).show();
        }else{
             set_chart30('sell_goods'+now_day,date_arr[now_day], sell_goods_data[now_day] );  
               $('#sell_goods'+now_day).show();
        }    
   }
   
   
}
function set_chart(id,date_arr,sell_data){
        if(chart!==undefined){
                chart.clear();
        }
            if(width==0){
            width = $('.tap_box').eq(0).width();

             width =  parseInt(width);

             height = parseInt(width*0.9);
            }
 
              chart = new AChart({
            theme : AChart.Theme.SmoothBase,
            id : id,
            width : width,
            height : height,
            forceFit : true, //自适应宽度
             fitRatio : 1, // 高度是宽度的 0.4
            colors: ['#1695ca'],
            plotCfg : {
              margin : [30,30,50] //画板的边距
            },
            xAxis : {
            	categories :date_arr           },
			seriesOptions : { //设置多个序列共同的属性
            lineCfg : { //不同类型的图对应不同的共用属性，lineCfg,areaCfg,columnCfg等，type + Cfg 标示
              smooth : true,
              "line": {
                  "stroke-width": 1,
                  "stroke-linejoin": "round",
                 "stroke-linecap": "round"
             },
			 "lineActived": {
                    "stroke-width": 1,
					"stroke-linejoin": "round",
                 "stroke-linecap": "round"
                },
              labels : { //标示显示文本
                label : { //文本样式
                  y : -17
                },
                //渲染文本
                renderer : function(value,item){ //通过item修改属性
                    item.fill = '#666';
                    item['font-weight'] = 'normal';
                    item['font-size'] = 12;
                  return value;
                }
              }
            }
          },
          
            tooltip : {
              
              valueSuffix : 'Point',
            //  shared : true, //是否多个数据序列共同显示信息
          // custom : true, //自定义tooltip
     
              itemTpl : '{value}'
            },
            series : [{
               name: '',
                data:sell_data           }]
        });
     
        chart.render();

    }

function set_chart30(id,date_arr,sell_data){
        if(chart!==undefined){
            
            chart.getXAxis().hide();
             chart.getYAxis().hide();
           //     chart.clear();
        }
        return ;
            if(width==0){
            width = $('.tap_box').eq(0).width();

             width =  parseInt(width);

             height = parseInt(width*0.9);
            }
 
              chart = new AChart({
            theme : AChart.Theme.SmoothBase,
            id : id,
            width : width,
           height : height,
            colors: ['#1695ca'],
            plotCfg : {
              margin : [30,30,50] //画板的边距
            },
            xAxis : {
            	categories :date_arr           },
	 seriesOptions : { //设置多个序列共同的属性
            lineCfg : { //不同类型的图对应不同的共用属性，lineCfg,areaCfg,columnCfg等，type + Cfg 标示
              markers:{
                single: true
              },
              smooth : true
            }
          },
            tooltip : {
              
              valueSuffix : ''
              // itemTpl : '{value}'
            },
            series : [{
               name: '',
                data:sell_data           }]
        });
     
        chart.render();

       
        

    }

  
</script>
</body>
</html>
