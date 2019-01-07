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

<div id="container">
	<div class="row">
		<div class="control-group span11">
			<label class="control-label span3">店铺：</label>

			<div class="span8 controls">
				<select name="shop" id="shop" class="input-normal">
					<?php foreach($response['shop'] as $_shop) {?>
						<option value="<?php echo $_shop[0] ?>"><?php echo $_shop[1]?></option>
					<?php }?>
				</select></div>
		</div>
	</div>
	<div class="row form-actions actions-bar">
		<div class="span13 offset3 ">
			<button type="button" class="button button-primary" id="download" onclick="do_download()">下载</button>
		</div>
	</div>

	<!--进度条-->
	<div class="progress progress-striped" id="dl_progress" style="display: none;"></div>
</div>

<script type="text/javascript">
//	var form = new BUI.Form.HForm({
//		srcNode: '#form1',
//		submitType: 'ajax',
//		callback: function (data) {
//			var type = data.status == 1 ? 'success' : 'error';
//
//			BUI.Message.Alert(data.message, function () {
//				if (data.status == 1) {
//					ui_closePopWindow('1415325669612');
//				}
//			}, type);
//		}
//	}).render();

	var fullMask = null;//提示层
	BUI.use(['bui/mask'],function(Mask){

		fullMask = new Mask.LoadMask({
			el : 'body',
			msg : '下载中。。。'
		});
	});

	//下载
	function do_download() {
		var url = '?app_act=api/base_item/do_dl_taobao_items';

		fullMask.show();
		var shop = $('#shop').val();

		$.ajax({
			type: "POST",
			url: url,
			data: {'shop':shop},
			dataType: "json",
			async : true,
			success: function(data){

				if (data.status == 1) {
					BUI.Message.Alert(data.message);
					fullMask.hide();
					$('#dl_progress').hide();
				}
			}
		});

		//监测进度
		BUI.use('bui/progressbar',function(ProgressBar){

			$('#dl_progress').empty();//重置

			var Progressbar = ProgressBar.Base;
			var progressbar = new Progressbar({
				elCls : 'progress progress-striped active',
				render : '#dl_progress',
				tpl : '<div class="bar"></div>'
			//	percent:10
			});
			progressbar.render();
			progressbar.set('percent',0);

			setTimeout(check_dl_status(progressbar), 1000);
		});
	}

	//监测下载进度
	function check_dl_status(progressbar) {
		$('#dl_progress').show();

		var check_url = '?app_act=api/base_item/check_dl_progress';
		$.ajax({
			type: "POST",
			url: check_url,
			data: '',
			dataType: "json",
			async : true,
			success: function(data){

				if (data.status == 1) {
					progressbar.set('percent',data.data.dl_progress);
				}
				if (data.data.dl_over == 0) {
					setTimeout(check_dl_status(progressbar), 1000);
				}
				if (data.data.dl_over == 1){
					progressbar.set('percent',100);
				}
			}
		});
	}
</script>