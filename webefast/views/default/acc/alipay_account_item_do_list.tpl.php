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
render_control('PageHead', 'head1', array('title' => '对账科目',
    'links' => array(
        
    ),
    'ref_table' => 'table'
));
?>
<?php

?>

<div style="margin-bottom:6px;">
	<ul id="tabs">
		<li onclick="op_submit(1)">收入科目</li>
		<li onclick="op_submit(2)">支出科目</li>
	</ul>
</div>


<div id="html">

</div>
<script>
$("#tabs li").click(function(){
	$("#tabs li").removeClass("current");
	$(this).toggleClass("current");
});

$("#tabs li").eq(0).click();

function op_submit(in_out_flag){
	var params = {};
	params['in_out_flag'] = in_out_flag;
	//console.log(params);
	var url = "?app_act=acc/alipay_account_item/search";
	$.get(url,params,function(result){
		$("#html").html(result);	
	});
}
</script>