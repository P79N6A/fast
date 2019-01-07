<style type="text/css">
#tabs{
	height: 28px;
	overflow: hidden;
}
#tabs li{float:left;list-style-type:none;padding:6px;border:1px #C2C2C2 solid;margin-left:8px;background:#E5E5E5;cursor:pointer;}
#tabs .current{background:#F8F8F8;}

.total_info td{text-align:center;}
.total_info,.tbl_list{width:500px;border-collapse:collapse;margin:6px;}
.total_info td,.tbl_list td{border:1px #ccc solid;}
.total_info caption,.tbl_list caption{background:#CCC4FF;}
.tbl_list .td_label{width:200px;}
.tbl_list .td_cont{width:200px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '支付宝流水核销统计',
    'links' => array(
        
    ),
    'ref_table' => 'table'
));
?>
<?php
$db_shop = load_model('base/ShopModel')->get_purview_shop('shop_code,shop_name,sale_channel_code');
$shop = array();
foreach($db_shop as $sub_db){
	if ($sub_db['sale_channel_code']!=='taobao'){
		continue;
	}
	$shop[] = $sub_db;
}

$ym = array();
$firstday = date("Y-m-01");
for($i=0;$i<=6;$i++){
	if ($i == 0){
		$ym[] = date('Y-m');
	}else{
		//$ym[] = date('Y-m',strtotime("-{$i} month"));
                $ym[] = date("Y-m",strtotime("$firstday -{$i} month"));
	}
}
?>

<div style="margin-bottom:6px;">
	<ul id="tabs">
		<?php
			foreach($shop as $row){
				echo "<li _code='{$row['shop_code']}'>{$row['shop_name']}</li>";
			}
		?>
	</ul>
</div>

<form>
	<input type="hidden" id="shop_code" value=""/>
	收款账期月份: <select id="ym" onchange="op_submit()">
		<?php
			foreach($ym as $_ym_v){
				echo "<option value='{$_ym_v}'>{$_ym_v}</option>";
			}
		?>
	</select> 
数据更新时间：<span id="lastchanged"> ---- </span>
</form>

<table>
	<tr><td id="html1" valign="top"></td><td id="html2" valign="top"></td></tr>
	<tr><td id="html3" valign="top"></td><td></td></tr>
</table>	
<script>
$("#tabs li").click(function(){
	$("#tabs li").removeClass("current");
	$(this).toggleClass("current");
	$("#shop_code").val($(this).attr('_code'));
	op_submit();
});

$("#tabs li").eq(0).click();

function op_submit(){
	var params = {};
	params['shop_code'] = $("#shop_code").val();
	params['ym'] = $("#ym").val();
	params['app_fmt'] = 'json';
	//console.log(params);
	var url = "?app_act=acc/report_alipay/search";
	$.getJSON(url,params,function(result){
		$("#lastchanged").html(result.lastchanged);
		$("#html1").html(result.html1);
		$("#html2").html(result.html2);
		$("#html3").html(result.html3);		
	});
}
</script>