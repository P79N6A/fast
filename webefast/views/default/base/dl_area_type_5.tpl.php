<?php

//render_control('Form', 'form1', array(
//	'conf'=>array(
//		'fields'=>array(
//			array('title'=>'店铺', 'type'=>'select', 'field'=>'shop','data' => $response['shop']),
//		//	array('title'=>'下载类型', 'type'=>'select', 'field'=>'shop','data' => $response['shop']),
//		),
////		'hidden_fields'=>array(array('field'=>'print_id')),
//	),
//	'buttons'=>array(
//		array('label'=>'下载', 'type'=>'submit'),
//		//array('label'=>'重置', 'type'=>'reset'),
//	),
//	'act_add'=>'api/base_item/do_dl_taobao_items', //edit,add,view
//
//	'data'=>$response['data'],
//
//	'rules'=>array(
////		array('order_time', 'require'),
////		array('reason','require'),
//	),
//));
?>

<div>
	<div class="row form-actions actions-bar">
		<div class="span13 offset3 ">
			<button type="button" class="button button-primary" id="download" onclick="dl_area_type_5()">下载</button>
		</div>
	</div>

</div>

<div style='text-align:right;' id="_msg"></div>

<script type="text/javascript">

	var fullMask = null;//提示层
	BUI.use(['bui/mask'],function(Mask){

		fullMask = new Mask.LoadMask({
			el : '#_msg',
			msg : '下载中。。'
		});
	});

	//下载
	function dl_area_type_5() {
		var url = '?app_act=base/taobao_area/do_dl_area_type_5';

		fullMask.show();

		$.ajax({
			type: "POST",
			url: url,
			data: {},
			dataType: "json",
			async : true,
			success: function(data){

				if (data.status == 1) {
					BUI.Message.Alert(data.message);
					fullMask.hide();
				}
			}
		});
	}

</script>