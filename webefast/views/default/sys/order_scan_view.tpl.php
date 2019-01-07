<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>初始化demo</title>
<link href="assets/css/perfect-scrollbar.css" rel="stylesheet" type="text/css">
<style>
/*reset*/
body,div,a,p,ul,li,img,h1,h2,h3,h4,h5,h6,ol,table,tr,td,form,input,button{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
a{ text-decoration:none;}
li{ list-style:none;}
img,input{ border:none;}

body{background:url(assets/img/order_scan_bg.jpg) no-repeat; background-size:cover;}
body{ background:#efeee8;}
.intialise{min-width:1050px; margin:0 auto; padding-bottom:20px; position:relative;}
.message_pop{ width:100%; border:1px solid #ffcd03; border-radius:3px; background:#fff8e6; text-align:center; height:28px; line-height:28px; box-sizing:border-box;}
.message_pop .icon{ display:inline-block; width:24px; height:18px; background:url(assets/img/ui/mess_icon.png) no-repeat; margin-right:15px;}
.message_pop .mess{ color:#a29b95;}
.message_pop .mess:hover{ text-decoration:underline;}
.message_pop .readbtn{ display:inline-block; color:#1695ca; padding:0px 8px; border:1px solid #adc8dc; line-height:18px; border-radius:3px; margin-left:15px; cursor:pointer;}
.message_pop .closebtn{ float:right; font-size:27px; color:#666; margin-right:5px; cursor:pointer;}
.message_pop .closebtn:hover{ color:#333;}
.intialise .wljk{ height:325px; background:#FFF; border-radius:5px; margin-top:15px; position:relative;}
.intialise .wljk .headline{ height:40px; border-bottom:1px solid #c5cfc5; line-height:40px;}
.intialise .wljk .headline strong{ margin-left:1%; font-size:18px;}
.intialise .wljk .headline .update_time{ margin-left:20px; font-size:14px; color:#666;}
.intialise .wljk .headline .refresh{ float:right; margin-right:1%; color:#666; font-size:16px;}
.intialise .wljk .headline .refresh img{ vertical-align:middle; margin-right:5px;}
.intialise .wljk .pro_bar{ height:16px; padding:68px 15px;}
.intialise .wljk .mainarea_wrap{position:absolute; left:0; top:41px; width:98%; padding:31px 1% 0;}
.intialise .wljk .mainarea{width:100%;}
.intialise .wljk .mainarea .unit{ margin-right:1%; text-align:center; vertical-align:top;}
.intialise .wljk .mainarea .unit strong{ display:inline-block; font-size:34px; cursor:pointer; margin-bottom:19px;}
.intialise .wljk .mainarea .unit strong:hover{ color:#e74d4d;}
.intialise .wljk .mainarea .unit .point{ display:block;}
.intialise .wljk .mainarea .unit .title{ color:#333; font-size:16px; margin:7px 0 14px;}
.intialise .wljk .mainarea .unit .itemize_icon{ height:6px; background:url(assets/images/itemize_icon.png) no-repeat center; position:relative; top:1px; z-index:10;}
.intialise .wljk .mainarea .unit .itemize{ position:relative; overflow:hidden; border:1px dashed #999; padding:10px 5px; font-size:14px; color:#666; height:118px;}
.intialise .wljk .mainarea .unit .itemize li{ margin-bottom:12px;}
.intialise .wljk .mainarea .unit .itemize li span{ cursor:pointer;}
.intialise .wljk .mainarea .unit .itemize li span:hover{ color:#e74d4d;}

.intialise .tdjk{ height:200px; background:#FFF; border-radius:5px; margin-top:15px; position:relative;}
.intialise .tdjk .headline{ height:40px; border-bottom:1px solid #c5cfc5; line-height:40px;}
.intialise .tdjk .headline strong{ margin-left:1%; font-size:18px;}
.intialise .tdjk .headline .update_time{ margin-left:20px; font-size:14px; color:#666;}
.intialise .tdjk .headline .refresh{ float:right; margin-right:1%; color:#666; font-size:16px;}
.intialise .tdjk .headline .refresh img{ vertical-align:middle; margin-right:5px;}
.intialise .tdjk .pro_bar{ height:16px; padding:68px 15px;}
.intialise .tdjk .mainarea_wrap{position:absolute; left:0; top:35px; width:98%; padding:31px 1% 0;}
.intialise .tdjk .mainarea{width:100%;margin-left: 10px;}
.intialise .tdjk .mainarea .unit{ float:left;margin-right:3%; text-align:center; vertical-align:top;}
.intialise .tdjk .mainarea .unit strong{ display:inline-block; font-size:34px; cursor:pointer; margin-bottom:19px;}
.intialise .tdjk .mainarea .unit strong:hover{ color:#e74d4d;}
.intialise .tdjk .mainarea .unit .point{ display:block;}
.intialise .tdjk .mainarea .unit .title{ color:#333; font-size:16px; margin:7px 0 14px;}
.intialise .tdjk .mainarea .unit .itemize_icon{ height:6px; background:url(assets/images/itemize_icon.png) no-repeat center; position:relative; top:1px; z-index:10;}
.intialise .tdjk .mainarea .unit .itemize{ position:relative; overflow:hidden; border:1px dashed #999; padding:10px 5px; font-size:14px; color:#666; height:118px;}
.intialise .tdjk .mainarea .unit .itemize li{ margin-bottom:12px;}
.intialise .tdjk .mainarea .unit .itemize li span{ cursor:pointer;}
.intialise .tdjk .mainarea .unit .itemize li span:hover{ color:#e74d4d;}

.intialise .xsgz_wrap{ overflow:hidden; margin-top:15px;}
.intialise .xsgz_wrap .xsgz{ width:49.5%; background:#FFF; border-radius:5px;}
.intialise .xsgz_wrap .xsgz h3{ font-size:18px; text-indent:2%; height:40px; line-height:40px; border-bottom:1px solid #c5cfc5;}
.intialise .xsgz_wrap .xsgz .main{ padding:25px 2%;}
.intialise .xsgz_wrap .xsgz_left{ float:left;}
.intialise .xsgz_wrap .xsgz_right{ float:right;}
</style>
</head>
<?php echo load_js('jquery-1.8.1.min.js');?>
<?php echo load_js('core.min.js');?>
<body>
    <?php include get_tpl_path('web_page_top'); ?>
<div class="intialise">
	<!--div class="message_pop"><i class="icon"></i><a href="javascript:void(0)" class="mess">教您玩转双十一会员营销，eFAST软件全新改版！</a><span class="readbtn">已读</span><span class="closebtn">&times;</span></div-->
	
        <div class="wljk">
    	<div class="headline">
        	<strong>网络订单监控</strong><span class="update_time">数据更新时间：<span class="time" id= "time" >0</span></span><a class="refresh" href="#" onclick = "refresh()"><img src="assets/images/refresh.png" width="21" height="21">刷新</a>
        </div>
        <p class="pro_bar"><img src="assets/images/pro_bar.png" width="100%" height="100%"></p>
        <div class="mainarea_wrap">
        <table class="mainarea">
        <tr>
        	<td class="unit">
            	<strong id= "pay_num" >0</strong>
                <span class="point"><img src="assets/images/pro_point.png" width="11" height="11"></span>
                <p class="title">今日已付款订单数</p>
<!--                <p class="itemize_icon">&nbsp; </p>-->
<!--                <ul class="itemize" id = "category_num">-->
<!--                	-->
<!--                	-->
<!--                </ul>-->
            </td>
            <td class="unit">
            	<strong id= "transform_num" >0</strong>
                <span class="point"><img src="assets/images/pro_point.png" width="11" height="11"></span>
                <p class="title">今日已转单数</p>
<!--                <p class="itemize_icon">&nbsp; </p>-->
<!--                <ul class="itemize">-->
<!--                	<li>异常转单数：<span>0</span></li>-->
<!--                </ul>-->
            </td>
            <td class="unit">
            	<strong id= "unconfirm_num" >0</strong>
                <span class="point"><img src="assets/images/pro_point.png" width="11" height="11"></span>
                <p class="title">待确认订单数</p>
                <p class="itemize_icon">&nbsp; </p>
                <ul class="itemize">
                	<li>正常单：<span id= "normal_num" >0</span></li>
                    <li>问题单：<span id= "problem_num" >0</span></li>
                    <li>缺货单：<span id= "stockout_num" >0</span></li>
                    <li>挂起单：<span id= "pending_num" >0</span></li>
                </ul>
            </td>
            <td class="unit">
            	<strong id= "unnotice_num" >0</strong>
                <span class="point"><img src="assets/images/pro_point.png" width="11" height="11"></span>
                <p class="title">待通知配货订单数</p>
            </td>
            <td class="unit">
            	<strong id= "unpick_num" >0</strong>
                <span class="point"><img src="assets/images/pro_point.png" width="11" height="11"></span>
                <p class="title">待拣货订单数</p>
            </td>
            <td class="unit">
            	<strong id= "unscan_num" >0</strong>
                <span class="point"><img src="assets/images/pro_point.png" width="11" height="11"></span>
                <p class="title">待扫描订单数</p>
            </td>
            <td class="unit">
            	<strong id= "deliver_num" >0</strong>
                <span class="point"><img src="assets/images/pro_point.png" width="11" height="11"></span>
                <p class="title">今日已发货订单数</p>
            </td>
            <td class="unit">
            	<strong id= "back_num" >0</strong>
                <span class="point"><img src="assets/images/pro_point.png" width="11" height="11"></span>
                <p class="title">今日网单回写订单数</p>
                <p class="itemize_icon">&nbsp; </p>
                <ul class="itemize">
                	<li>回写失败订单数：<span id= "back_error_num">0</span></li>
                </ul>
            </td>
           </tr> 
        </table>
        </div>
    </div>
    
    <script>
 function refresh(){
	 
    $.ajax({ type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('sys/order_scan/getData');?>', data: {},
        success: function(ret) {
           ret = eval(ret) 
           for(var i=0; i<ret.length; i++) 
           { 
           $('#'+ret[i].key).html(ret[i].num); 
           }   
        }
    });


//    $.ajax({ type: 'POST', dataType: 'json',
//        url: '<?php //echo get_app_url('sys/order_scan/getDataByShop');?>', data: {},
//        success: function(ret) {
//           ret = eval(ret);
//           var html = '<li>监控漏单数：<span>0</span></li>'; 
//           for(var i=0; i<ret.length; i++) 
//           { 
//           html+= '<li title = "'+ret[i].shop_name+'">店铺'+ret[i].short_name+'订单数：<span>'+ret[i].num+'</span></li>';
//           }  
//           $('#category_num').html(html);  
//        }
//    });
 }
 
 function refresh_refund(){
     $.ajax({ type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('sys/order_scan/getRefundOrderData'); ?>', data: {},
        success: function(ret) {
           ret = eval(ret) 
           for(var i=0; i<ret.length; i++)
           { 
           $('#'+ret[i].key).html(ret[i].num); 
           }
        }
    });
 }
 
     function openPageOms(url,name) {
            openPage(window.btoa(url),url,name);
    }
 $(function(){
	 refresh();
         refresh_refund();
         $('#normal_num').click(function(){
             openPageOms('?app_act=oms/sell_record/ex_list&is_normal=1','订单列表'); 
         });
          $('#problem_num').click(function(){
             openPageOms('?app_act=oms/sell_record/question_list','问题订单列表');
         });
          $('#stockout_num').click(function(){
             openPageOms('?app_act=oms/sell_record/short_list','缺货订单列表');
         });
          $('#pending_num').click(function(){
             openPageOms('?app_act=oms/sell_record/pending_list','挂起订单列表');
         });
         $('#back_error_num').click(function(){
             openPageOms('?app_act=api/sys/order_send/index','平台网单回写列表');
         })
	// setInterval('refresh()',60000);
	});

    </script>
    
    <div class="tdjk">
        <div class="headline">
            <strong>网络退单监控</strong><span class="update_time">数据更新时间：<span class="time" id="time_ro" >0</span></span><a class="refresh" href="#"  onclick = "refresh_refund()"><img src="assets/images/refresh.png" width="21" height="21">刷新</a>
        </div>
        <p class="pro_bar"><img src="assets/images/pro_bar.png" width="100%" height="100%"></p>
        <div class="mainarea_wrap">
            <ul class="mainarea">
                <li class="unit">
                    <strong id="ro_num">0</strong><br>
                    <span><img src="assets/images/pro_point.png" width="11" height="11"></span>
                    <p class="title">今日已申请的退单数</p>
    <!--                <p class="itemize_icon">&nbsp; </p>
                    <ul class="itemize">
                        <li>监控漏单数：<span>0</span></li>
                        <li>店铺A订单数：<span>300</span></li>
                        <li>店铺B订单数：<span>300</span></li>
                        <li>店铺C订单数：<span>300</span></li>
                    </ul>-->
                </li>
                <li class="unit">
                    <strong id="tran_ro_num">0</strong><br>
                    <span><img src="assets/images/pro_point.png" width="11" height="11"></span>
                    <p class="title">今日已转退单数</p>
    <!--                <p class="itemize_icon">&nbsp; </p>
                    <ul class="itemize">
                        <li>异常转单数：<span>0</span></li>
                    </ul>-->
                </li>
                <li class="unit">
                    <strong id="unconfirm_ro_num">0</strong><br>
                    <span><img src="assets/images/pro_point.png" width="11" height="11"></span>
                    <p class="title">待确认退单数</p>
    <!--                <p class="itemize_icon">&nbsp; </p>
                    <ul class="itemize">
                        <li>正常单：<span>0</span></li>
                        <li>问题单：<span>300</span></li>
                        <li>缺货单：<span>300</span></li>
                        <li>挂起单：<span>300</span></li>
                    </ul>-->
                </li>
                <li class="unit">
                    <strong id="unreceipt_ro_num">0</strong><br>
                    <span><img src="assets/images/pro_point.png" width="11" height="11"></span>
                    <p class="title">待收货退单数</p>
                </li>
                <li class="unit">
                    <strong id="unrefund_ro_num">0</strong><br>
                    <span><img src="assets/images/pro_point.png" width="11" height="11"></span>
                    <p class="title">待退款退单数</p>
                </li>
                <li class="unit">
                    <strong id="receipt_ro_num">0</strong><br>
                    <span><img src="assets/images/pro_point.png" width="11" height="11"></span>
                    <p class="title">今日收到包裹数</p>
                </li>
                <li class="unit">
                    <strong id="refund_ro_num">0</strong><br>
                    <span><img src="assets/images/pro_point.png" width="11" height="11"></span>
                    <p class="title">今日退款退单数</p>
                </li>
                </li>
            </ul>
        </div>
    </div>
<!--    <div class="xsgz_wrap">-->
<!--    	<div class="xsgz xsgz_left">-->
<!--        	<h3>近七天销售跟踪</h3>-->
<!--            <div class="main">-->
<!--            	<img src="assets/images/xsgz.png" width="100%" height="100%">-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="xsgz xsgz_right">-->
<!--        	<h3>近七天销售跟踪</h3>-->
<!--            <div class="main">-->
<!--            	<img src="assets/images/xsgz.png" width="100%" height="100%">-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
</div>
<script src="assets/js/perfect-scrollbar.jquery.js"></script>
<script>
$(".itemize").perfectScrollbar();
$(".message_pop .closebtn").click(function(){
		$(".message_pop").hide();
		})
</script>
</body>
</html>
