<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<object id="LODOP" classid="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" width=0  height=0>
		<param  name="Caption"  value="" />
		<param  name="Border"   value="1" />
		<param name="CompanyName" value="上海百胜软件"/>
		<param name="License" value="452547275711905623562384719084"/>
		<?php $project = $GLOBALS['context']->config['project'];?>
		<embed id="LODOP_EM" TYPE="application/x-print-lodop" width="0" height="0" PLUGINSPAGE="/<?php echo $project?>webpub/js/print/lodop/install_lodop.exe">
	</object>

	<?php

		$extend_attr = array();

		$page_zoom = 100;

		if (not_null($response['do_print']['config']['extend_attr'])) {
			$extend_attr = object_to_array(json_decode($response['do_print']['config']['extend_attr']));
		}

		if(not_null($extend_attr['page_zoom'])) {
			$page_zoom = $extend_attr['page_zoom'];
		}
	?>

	<script>

		var LODOP;
		var print_pick = {

			print_prepare:function() {

				var strHtmlContent = $("#wwwww").html();
				LODOP.SET_PRINT_PAGESIZE(0,"<?php if(not_null($response['do_print']['config']['template_page_width'])) echo $response['do_print']['config']['template_page_width'] + 8; else echo '210';?>mm","<?php if(not_null($response['do_print']['config']['template_page_height'])) echo $response['do_print']['config']['template_page_height']; else echo '297';?>mm","<?php if(not_null($response['do_print']['config']['template_page_style'])) echo $response['do_print']['config']['template_page_style']; else echo 'A4';?>");

				LODOP.ADD_PRINT_HTM("0mm","0mm","<?php if(not_null($response['do_print']['config']['template_page_width'])) echo $response['do_print']['config']['template_page_width'] + 8; else echo '204';?>mm","<?php if(not_null($response['do_print']['config']['template_page_height'])) echo $response['do_print']['config']['template_page_height']; else echo '289';?>mm",strHtmlContent);
				//LODOP.ADD_PRINT_TABLE("0mm","0mm","<?php if(not_null($response['do_print']['config']['template_page_width'])) echo $response['do_print']['config']['template_page_width'] + 8; else echo '204';?>mm","<?php if(not_null($response['do_print']['config']['template_page_height'])) echo $response['do_print']['config']['template_page_height']; else echo '289';?>mm",strHtmlContent);

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
			}
		}
	</script>
	<style type="text/css">
		body{text-align:center;}
		div{margin:0 auto;text-align:left;}
	</style>
	<title>打印预览</title>
</head>
<body>
    <?php include get_tpl_path('web_page_top'); ?>
	<input name="" type="button" value="打 印" class="button_main" onclick="print_pick.print_start();" /><br/>
	<div id="print_content">

		<?php echo get_js("jquery-1.7.min.js,baison.js")?>
		<?php echo get_js('print/colResizable-1.3.min.js,print/lodop/LodopFuncs.js') ?>
		<?php echo get_webpub('style/css/print.css');?>

		<style type="text/css">
				.danjuContent table{ border-collapse:collapse; border-spacing:0;}
				.danjuContent table,
				.danjuContent table th,
				.danjuContent table td{ font-size:12px;}
				.danjuContent div {margin-top: 2px;margin-bottom: 2px;}
		</style>

		<?php if (!empty($response['do_print']['data'])) {?>

			<?php foreach($response['do_print']['data'] as $print_key=>$print_data) {?>
				<?php $print_print = $print_data['danju_print_content'];?>
				<div class="danjuContent" >
					<div style="width:<?php echo ($print_data['template_page_width']-10)*$page_zoom/100;?>mm;">
						<?php echo $print_print;?>
					</div>
				</div>

				<div style="PAGE-BREAK-AFTER:always">&nbsp;</div>
			<?php } ?>
		<?php } else {?>
			<div style="text-align: center">
					无打印数据
			</div>
		<?php }?>
	</div>


	<div id="wwwww">
		<table style="border-color: black; border-width: 1px; border-style: solid;" cellpadding="0" cellspacing="0" width="100%">
			<!--			<thead><tr>-->
			<!--				<th style="width: 133px; border-color: black; border-width: 1px; border-style: dashed;" id="table_detail_area_th_money" rel_field="money">金额</th>-->
			<!--				<th style="width: 133px; border-color: black; border-width: 1px; border-style: dashed;" id="table_detail_area_th_price" rel_field="price">单价</th>-->
			<!--				<th style="width: 133px; border-color: black; border-width: 1px; border-style: dashed;" id="table_detail_area_th_rebate" rel_field="rebate">折扣</th>-->
			<!--				<th style="width: 133px; border-color: black; border-width: 1px; border-style: dashed;" id="table_detail_area_th_refer_price" rel_field="refer_price">参考价</th>-->
			<!--				<th style="width: 133px; border-color: black; border-width: 1px; border-style: dashed;" id="table_detail_area_th_num" rel_field="num">数量</th>-->
			<!--				<th style="width: 133px; border-color: black; border-width: 1px; border-style: dashed;" id="table_detail_area_th_size_name" rel_field="size_name">尺码名称</th>-->
			<!--			</tr></thead>-->
			<tbody>
			<tr>
				<td style="border: 1px solid black;">1800.00</td>
				<td style="border: 1px solid black;">600.00</td>
				<td style="border: 1px solid black;">1.000</td>
				<td style="border: 1px solid black;">600.00</td>
				<td style="border: 1px solid black;">3</td>
				<td style="border: 1px solid black;">165</td>
			</tr>
			<tr>
				<td style="border: 1px solid black;">1800.00</td>
				<td style="border: 1px solid black;">600.00</td>
				<td style="border: 1px solid black;">1.000</td>
				<td style="border: 1px solid black;">600.00</td>
				<td style="border: 1px solid black;">3</td>
				<td style="border: 1px solid black;">165</td>
			</tr>
			</tbody>
		</table>
	</div>

	<script type="application/javascript">
		$(document).ready(function(){
			<?php foreach($response['do_print']['danju_print_conf']['main_conf'] as $_key => $_area){?>
				<?php if ('list' == $_area['type']) {?>
					reset_list_style('<?php echo 'table_'.$_key ?>');
				<?php }?>
			<?php }?>
		});

        /**
         * 重置list下的td的样式
         * @param table_id
         */
		function reset_list_style(table_id) {
			if ($('#'+table_id).length > 0) {
				$('#'+table_id).find('tbody').find('tr').each(function(tr_k,tr_v){
					$(tr_v).find('td').each(function(td_k, td_v){

						$_th = $('#'+table_id).find('thead').find('tr').eq(0).find('th').eq(td_k);
						var font_size = $_th.css('font-size');
						var text_align = $_th.css('text-align');
						var font_weight = $_th.css('font-weight');
						$(td_v).css('font-size', font_size);
						$(td_v).css('text-align', text_align);
						$(td_v).css('font-weight', font_weight);
					})
				});

			}
		}

	</script>

</body>
</html>