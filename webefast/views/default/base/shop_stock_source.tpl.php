<?php render_control('PageHead', 'head1',
	array('title' => '库存来源仓库',
		'ref_table' => 'table',
	));
?>
<style>
	.ntbl table {border-collapse: collapse;border-spacing: 0;}
	.ntbl table,
	.ntbl table th,
	.ntbl table td {font-size: 8px;}
</style>


<table class="ntbl">
<?php
foreach($response['store'] as $sub_store){
	if (in_array($sub_store['store_code'],$response['stock_source_store_code'],true)){
		$_chk = 'checked';
	}else{
		$_chk = '';		
	}
	echo "<tr><td><input type='checkbox' name='store_code' value='{$sub_store['store_code']}' {$_chk}/></td><td>{$sub_store['store_name']}</td></tr>";
}
?>
</table>
<div class="row form-actions actions-bar">
	<div class="span13 offset3 ">
		<button id="submit" class="button button-primary" type="submit">提交</button>
		<button id="reset" class="button " type="reset">重置</button>
	</div>
</div>


<script type="text/javascript">
$("#submit").click(function(){
	var chked_arr = new Array();
	$(":checked").each(function(){
		chked_arr.push($(this).val());
	});
	if (chked_arr.length == 0){
		alert('请选择仓库');
		return;
	}
	var store_code_list = chked_arr.join(',');
	var ajax_url = "?app_fmt=json&app_act=base/shop/save_stock_source&store_code_list="+store_code_list+'&_id='+<?php echo $request['_id']; ?>;
	$.get(ajax_url, function(data){
	    var data = eval('('+data+')');
	    if (data.status == 1) {
	        ui_closePopWindow('<?php echo CTX()->request['ES_frmId']?>'); 
	        window.location.reload();
	    } else {
	        BUI.Message.Alert(data.message, function() { }, type);
	    }
	});
});
</script>