<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>订单流程设置</title>
<style>
/*reset*/
body,div,a,p,ul,li,img,h1,h2,h3,h4,h5,h6,ol{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif; color:#6c5d49;}
a{ text-decoration:none;}
li{ list-style:none;}
img,input{ border:none;}

.process_set h3{ font-weight:normal;}
.process_set ul{ padding:20px 10px; overflow:hidden;}
.process_set ul li{ width:150px; float:left; position:relative;}
.process_set ul li b{ display:block; width:150px; height:79px; background:url(assets/images/i_bg.png) no-repeat; cursor:pointer;}
.process_set ul li:hover b{ opacity:0.8;}
.process_set ul li h5{ font-weight:normal; font-size:15px; text-align:center; width:139px; height:32px; border:1px solid #d3d3d3; border-top:none; line-height:32px; background:url(assets/images/h5_bg.png) no-repeat;}
.process_set ul li h5 span{ color:#c54c41; margin-left:5px;}
.process_set ul li p{ width:119px; height:304px; border:1px solid #d3d3d3; border-top:none; padding:10px 10px; font-size:14px; background:#fff3dc; line-height:22px;-webkit-border-bottom-right-radius: 10px;box-shadow:0px 5px 5px #d3d3d3;
-webkit-border-bottom-left-radius: 10px;
-moz-border-radius-bottomright: 10px;
-moz-border-radius-bottomleft: 10px;
border-bottom-right-radius: 10px;
border-bottom-left-radius: 10px; }
.process_set ul li .conf_item{ position:absolute; left:50%; bottom:15px; margin-left:-60px; display:inline-block; width:110px; height:30px; border:1px solid #f6b63a; border-radius:3px; color:#f6b63a; line-height:30px; text-align:center; text-decoration:none; font-size:15px;}
.process_set ul li .conf_item:hover{ background:#ffcf73; color:#FFF;}
.process_set .sentence{ text-align:right;}
.process_set .conf_item_box{ width:480px; min-height:360px; position:fixed; top:50%; left:50%; margin-top:-180px; margin-left:-240px; background:#FFF; border-radius:5px;display:none; z-index:100;}
.process_set .conf_item_box h4{ position:relative; height:40px; border-bottom:1px solid #c4c4c4; text-align:center; line-height:40px;}
.process_set .conf_item_box h4 .clo{ position:absolute; width:20px; height:20px; top:8px; right:10px; font-size:30px; font-weight:normal; color:#000; line-height:20px;}
.process_set .conf_item_box h4 .clo:hover{ font-size:32px;}
.process_set .conf_item_box .conf_item_cont{ padding:15px 50px 10px;}
.process_set .conf_item_box .confirm .p_title{ padding-top:55px; text-align:center; color:#333;}
.process_set .conf_item_box .confirm a{ display:block; width:326px; height:36px; border:2px solid #ed6d3b; margin:12px auto; line-height:36px; text-align:center; color:#ed6d3b; border-radius:3px;}
.process_set .conf_item_box .confirm a:hover{ background:#ed6d3b; color:#FFF;}
.process_set .conf_item_box .confirm input{ width:308px; padding:10px; border:1px solid #c4c4c4; border-radius:3px; margin:12px auto; display:block; font-size:16px; background:url(assets/images/input_text_icon.png) no-repeat 302px 14px;}
.process_set .conf_item_box .confirm .p_note{ font-size:13px; color:#a8a8a6;}
.process_set .conf_item_box .circular{ height:315px;}
.process_set .conf_item_box .circular .p_title{ color:#333; font-size:14px;}
.process_set .conf_item_box .circular .p_whether{ text-align:center; padding:10px 0;}
.process_set .conf_item_box .circular .p_whether input{ margin-right:5px; width:20px; height:20px; vertical-align:text-bottom;}
.process_set .conf_item_box .circular>input{width:308px; padding:10px; border:1px solid #c4c4c4; border-radius:3px; margin:12px auto; display:block; font-size:16px; background:url(assets/images/input_text_icon.png) no-repeat 302px -74px;}
.process_set .conf_item_box .circular .p_note{ font-size:12px; color:#a8a8a6;}
.process_set .conf_item_box .ccbtn{ text-align:center; position:absolute; left:0; bottom:0px; width:100%;}
.process_set .conf_item_box .ccbtn button{ display:inline-block; width:126px; height:36px; border:2px solid #ed6d3b; border-radius:3px; margin:0 5px; line-height:36px; color:#ed6d3b; font-size:18px;}
.process_set .conf_item_box .ccbtn a{ display:inline-block; width:126px; height:36px; border:2px solid #ed6d3b; border-radius:3px; margin:0 5px; line-height:36px; color:#ed6d3b; font-size:18px;}
.process_set .conf_item_box .ccbtn a:hover{ background:#ed6d3b; color:#FFF;}

</style>

<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
<link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <?php include get_tpl_path('web_page_top'); ?>
    <?php echo load_js('jquery-1.8.1.min.js,bui/bui.js,util/date.js');?>
<?php echo load_js('common.js');?>
<div class="process_set">
	<h3>订单流程设置</h3>
    <ul>
    	<li>
        	<b></b>
            <h5>系统订单<span>新增</span></h5>
            <p>如果是平台订单，如淘宝等，无需人工操作，系统会自动下载淘宝订单，转化成系统订单。</p>
        </li>
        <li>
        	<b style="background-position:-150px center"></b>
            <h5>系统订单<span>付款</span></h5>
            <p>新增的订单需要及时点击“付款”，因为系统是按照“付款”状态来锁定库存，如果没有及时点击，可能会出现因为其他客服操作同商品其他订单，而导致出现缺货情况。</p>
        </li>
        <li>
        	<b style="background-position:-301px center"></b>
            <h5>系统订单<span>确认</span></h5>
            <p>客服审单的关键环节，当确认系统订单信息无误后，点击“确认”。</p>
            <a href="javascript:void(0);" class="conf_item">配置项</a>
        </li>
        <li>
        	<b style="background-position:-452px center"></b>
            <h5>系统订单<span>财审</span></h5>
            <p>财审为非必须流程，配置项默认为0，表示不启用财审，若配置大于0的数字，当系统订单的应收金额低于配置金额，则订单需要财审，才能通知配货。</p>
            <a href="javascript:void(0);" class="conf_item">配置项</a>
        </li>
        <li>
        	<b style="background-position:-603px center"></b>
            <h5>系统订单<span>通知配货</span></h5>
            <p>通知配货，目的是通知仓库可以对订单进行配发货操作，默认不启用，如果商家的订单量较大，需要控制仓库的发货数据，或者针对计划发货时间很长的订单限制发货，可以启用。</p>
            <a href="javascript:void(0);" class="conf_item">配置项</a>
        </li>
    </ul>
    <p class="sentence"><img src="assets/images/sentence01.png"></p>
    <div class="conf_item_box">
    	<h4>编辑配置项<a href="javascript:void(0);" class="clo">&times;</a></h4>
        <div class="conf_item_cont confirm">
        	<p class="p_title">如果需要配置自动确认订单策略，请点击：</p>
            <a href="javascript:void(0)">配置自动确认订单</a>
            <p class="p_note">注：如果订单需要财审，则“确认”操作后，系统自动将订单解锁</p>
        </div>
        <p class="ccbtn">
        	<a href="javascript:void(0)">取消</a><a href="javascript:void(0)">确定</a>
        </p>
    </div>
    <div class="conf_item_box">
    <form  id="form1" action="?app_act=sys/sys_oms/update_params&app_fmt=json" method="post">
    	<h4>编辑配置项<a href="javascript:void(0);" class="clo">&times;</a></h4>
        <div class="conf_item_cont confirm">
        	<p class="p_title">低于以下金额，订单需要财审：</p>
            <input type="text" name = "fanance_money"  value="<?php echo $response['fanance_money'] ?>">
            <p class="p_note">注：只能配置0或大于0的整数</p>
        </div>
        <p class="ccbtn">
        	<a href="javascript:void(0)" class="clo">取消</a><button  type="submit">确定</button>
        </p>
        </form>
    </div>
    <div class="conf_item_box">
    <form  id="form2" action="?app_act=sys/sys_oms/update_params&app_fmt=json" method="post">
    	<h4>编辑配置项<a href="javascript:void(0);" class="clo">&times;</a></h4>
        <div class="conf_item_cont circular">
        	<p class="p_title">1、订单确认操作后（无需财审），系统自动通知配货：</p>
            <p class="p_whether"><input type="radio" name="oms_notice" value="1" id="circul"><label for="circul" style="margin-right:80px;">是</label><input type="radio" name="oms_notice" value="0" id="nocircul"><label for="nocircul">否</label></p>
            <p class="p_title">2、系统自动通知截止发货时间：</p>
            <input type="text"  name ="off_deliver_time" value="<?php echo $response['off_deliver_time'] ?>">
            <p class="p_note">注：<br>1、财审操作后的订单，系统自动通知配货，无需客服操作；<br>2、自动通知配货假定设置了3天，那么所有计划发货时间3天内的订单将自动通知配货，超过3天的订单不会自动通知配货，适用于预售场景；<br>3、通知配货操作后，系统将自动解锁相应的订单。</p>
        </div>
        <p class="ccbtn">
        	<a href="javascript:void(0)"  class="clo">取消</a><button  type="submit">确定</button>
        </p>
        </form>
    </div>
</div>
<script>
$(function(){
	$(".conf_item").each(function(i) {
        $(this).click(function(){
			var bg = "<div id='fullbg'></div>";
		    $("body").append(bg);	
		    $("#fullbg").css({ height:'100%', width:'100%', display:'block',position:'fixed', top:'0',left:'0',background:'url(images/opacity.png)'});
			$(".conf_item_box").hide().eq(i).show();
			})
    });
	$(".clo").click(function(){
		$(".conf_item_box").hide();
		$("#fullbg").hide();
		})
	var oms_notice = <?php echo $response['oms_notice'] ?>

	if(oms_notice == 1)
		$("#circul").attr("checked","checked");
	else
		$("#nocircul").attr("checked","checked");	
	})
	
function getQueryString(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if (r != null) return unescape(r[2]); return null;
}


var form =  new BUI.Form.HForm({
    srcNode : '#form1',
    submitType : 'ajax',
    callback : function(data){
			var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, function() {
            	if (data.status == 1) {
                	ui_closePopWindow(getQueryString('ES_frmId'));
                	
                }
            }, type);
	}
}).render();


var form =  new BUI.Form.HForm({
    srcNode : '#form2',
    submitType : 'ajax',
    callback : function(data){
			var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, function() {
            	if (data.status == 1) {
                	ui_closePopWindow(getQueryString('ES_frmId'));
                	
                }
            }, type);
	}
}).render();

</script>
</body>
</html>