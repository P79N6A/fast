<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php echo_print_plugin() ?>
	<?php echo load_js('jquery-1.8.1.min.js');?>
	<?php

		$extend_attr = array();

		$page_zoom = 100;
		$page_top_offset = $page_left_offset = 0;

		if (not_null($response['do_print']['config']['extend_attr'])) {
			$extend_attr = object_to_array(json_decode($response['do_print']['config']['extend_attr']));
		}

		if (not_null($extend_attr['page_zoom'])) {
			$page_zoom = $extend_attr['page_zoom'];
		}

		if (not_null($extend_attr['page_top_offset'])) {
			$page_top_offset = $extend_attr['page_top_offset'];
		}

		if (not_null($extend_attr['page_left_offset'])) {
			$page_left_offset = $extend_attr['page_left_offset'];
		}

	?>

	<script type="text/javascript">

		var LODOP;
		var print_pick = {

			print_prepare:function() {

				var strHtmlContent = $("#print_content").html();
				LODOP.SET_PRINT_PAGESIZE(0,"<?php if(not_null($response['do_print']['config']['template_page_width'])) echo $response['do_print']['config']['template_page_width'] + 8; else echo '210';?>mm","<?php if(not_null($response['do_print']['config']['template_page_height'])) echo $response['do_print']['config']['template_page_height']; else echo '297';?>mm","<?php if(not_null($response['do_print']['config']['template_page_style'])) echo $response['do_print']['config']['template_page_style']; else echo 'A4';?>");

				LODOP.ADD_PRINT_HTM("<?php echo $page_top_offset ?>mm","<?php echo $page_left_offset ?>mm","<?php if(not_null($response['do_print']['config']['template_page_width'])) echo $response['do_print']['config']['template_page_width'] + 8; else echo '204';?>mm","<?php if(not_null($response['do_print']['config']['template_page_height'])) echo $response['do_print']['config']['template_page_height']; else echo '289';?>mm",strHtmlContent);
			},

			print_start:function() {

				LODOP = getLodop(document.getElementById('LODOP'),document.getElementById('LODOP_EM'));
				var printer_count = LODOP.GET_PRINTER_COUNT();
				if (printer_count<1) {
					alert('该系统未安装打印设备,请添加相应的打印设备');
					return;
				}
				LODOP.PRINT_INIT('打印');

				var is_select_printer = false; //已经选择了打印机
				<?php if(isset($response['do_print']['config']['printer_name']) && !empty($response['do_print']['config']['printer_name'])){?>
				is_select_printer = true;
				LODOP.SET_PRINTER_INDEX("<?php echo $response['do_print']['config']['printer_name'];?>");
				<?php }?>

				if (false == is_select_printer) {
					var p = LODOP.SELECT_PRINTER();
					if (p<0) {
						return;
					}
				}
				print_pick.print_prepare();
				//LODOP.PRINT_SETUP();
				//window.print();
				//LODOP.PREVIEW();
			//	LODOP.ADD_PRINT_TEXT(10, 350, 200, 22, "第#页/共&#页/共&amp;页");
				LODOP.PRINT();
			},

			print_preview:function() {

				LODOP = getLodop(document.getElementById('LODOP'),document.getElementById('LODOP_EM'));
				var printer_count = LODOP.GET_PRINTER_COUNT();
				if (printer_count<1) {
					alert('该系统未安装打印设备,请添加相应的打印设备');
					return;
				}
				LODOP.PRINT_INIT('打印');

				var is_select_printer = false; //已经选择了打印机
				<?php if(isset($response['do_print']['config']['printer_name']) && !empty($response['do_print']['config']['printer_name'])){?>
				is_select_printer = true;
				LODOP.SET_PRINTER_INDEX("<?php echo $response['do_print']['config']['printer_name'];?>");
				<?php }?>

				if (false == is_select_printer) {
					var p = LODOP.SELECT_PRINTER();
					if (p<0) {
						return;
					}
				}
				print_pick.print_prepare();
				//LODOP.PRINT_SETUP();
				//window.print();
				//LODOP.PREVIEW();
				//	LODOP.ADD_PRINT_TEXT(10, 350, 200, 22, "第#页/共&#页/共&amp;页");
				LODOP.PREVIEW();
			}
		}
		function ocx_download(){
			window.location = cutoverUrl("ocx_download");
		}
	</script>
	<style type="text/css">
		body{text-align:center;}
		div{margin:0 auto;text-align:left;}
	</style>
	<title>打印预览</title>
		<?php echo load_js('jquery-1.8.1.min.js');?>
<!--		--><?php //echo get_js('print/lodop/LodopFuncs.js') ?><!--	-->
</head>
<body>
    <?php include get_tpl_path('web_page_top'); ?>
	<input name="" type="button" value="打 印" class="button_main" onclick="print();" />
	<input name="" type="button" value="打 印 预 览" class="button_main" onclick="print_pick.print_preview();" />
<!--	&nbsp;&nbsp;<a target=_blank onclick="ocx_download();" title="插件如安装有问题，请以管理员身份运行">插件下载</a><br/>-->
	<div id="print_content">

		<?php echo load_css('reset.css');?>
		<?php echo load_css('print.css');?>

		<style type="text/css">
				.danjuContent table{ border-collapse:collapse; border-spacing:0;}
				.danjuContent table,
				.danjuContent table th,
				.danjuContent table td{ font-size:14px;}
				.danjuContent div {margin-top: 1px;margin-bottom: 1px;}
		</style>

		<?php if (!empty($response['do_print']['data'])) {?>

			<?php foreach($response['do_print']['data'] as $print_key=>$print_data) {?>
				<?php $print_print = not_null($print_data['print_html']) ? $print_data['print_html'] : $print_data['danju_print_content'];?>
				<?php //$print_print = $print_data['danju_print_content'];?>
				<div id="print_index_<?php echo $print_key ?>" class="danjuContent" >
					<div style="width:<?php echo ($print_data['template_page_width']-10)*$page_zoom/100;?>mm;">
						<?php echo $print_print;?>
					</div>
				</div>

				<div style="PAGE-BREAK-AFTER:always"> &nbsp;</div>
			<?php } ?>
		<?php } else {?>
			<div style="text-align: center">
					无打印数据
			</div>
		<?php }?>
	</div>

	<script type="text/javascript">

		var print_html_style = null;

		$(function () {
			//style 2014-03-31
			<?php if (not_null($response['do_print']['data'][0]['print_html_style'])) {?>
			print_html_style = <?php echo $response['do_print']['data'][0]['print_html_style']?>;
			<?php }?>
			<?php foreach($response['do_print']['danju_print_conf']['main_conf'] as $_key => $_area){?>
				<?php if ('list' == $_area['type']) {?>
					reset_list_style('<?php echo $_key ?>');
				<?php }?>
			<?php }?>

			$("#print_content").find('.CRC').remove();
			$("#print_content").find('.CRL').remove();
			$("#print_content").find('.CRG').remove();

			$("#print_content").find('.ui-droppable').removeClass('ui-droppable');
			$("#print_content").find('.ui-draggable').removeClass('ui-draggable');
			$("#print_content").find('.CRZ').removeClass('CRZ');
		});

        /**
         * 重置list下的td的样式
         * @param table_id
         */
		function reset_list_style(table_id) {

			if ($('#table_'+table_id).length > 0) {

				$('#table_'+table_id).find('tbody').find('td').each(function(){

					var rel_field = $(this).attr('rel_field');

					var __th_style = $('#table_'+table_id).find("th[rel_field='"+rel_field+"']").attr('style');

					//没找到style
					if (!__th_style) {
						var _index = $(this).index();
						__th_style = $('#table_'+table_id).find('thead').find('th').eq(_index).attr('style');
					}

					//获取th的style
					$(this).attr('style', __th_style);
				});

				if (!print_html_style) {
					return;
				}
				//自定义设置，保存th高度，td高度
				if (typeof print_html_style[table_id] == 'undefined') {
					return;
				}
				if (typeof print_html_style[table_id]['table']['custom_table_setting'] == 'undefined') {
					return;
				}

				var custom_table_setting = print_html_style[table_id]['table']['custom_table_setting'];
				var table_setting_arr = custom_table_setting.split(';');

				var table_line_style = table_setting_arr[0].split(':')[1];
				var table_td_height = table_setting_arr[1].split(':')[1];
				var table_th_height = table_setting_arr[2].split(':')[1];

				//设置th的style
				$('#table_'+table_id).find('th').each(function(){
					$(this).css('height', table_th_height);
				});

				//设置td的style
				$('#table_'+table_id).find('td').each(function(){
					$(this).css('height', table_td_height);
//					$(this).css('overflow', 'hidden');
				});

//				$("#table_"+table_id).css('table-layout','fixed');
			}
		}

	</script>

</body>
</html>
<script type="text/javascript">
	var print_data_type = "<?php echo $response['print_data_type'];?>";
	var record_ids = "<?php echo $response['record_ids'];?>";
	function print(){
		if(print_data_type == 'oms_waves_record'){
			var data = {
		   	         'print_data_type':print_data_type,
			   	     'record_ids':record_ids,   
		   	     };
			$.post('<?php echo get_app_url('oms/waves_record/do_print_log');?>', data, function(data){
	          var type = data.status == 1 ? 'success' : 'error';
	  		if (data.status == 1) {
	  			
	  		} 
	      }, "json");
		}
		print_pick.print_start();
	}
</script>