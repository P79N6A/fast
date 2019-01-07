<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<object id="LODOP" classid="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" width="0" height="0">
	<param name="Caption" value=""/>
	<param name="Border" value="1"/>
	<param name="CompanyName" value="上海百胜软件"/>
	<param name="License" value="452547275711905623562384719084"/>
	<?php $project = $GLOBALS['context']->config['project']; ?>
	<embed id="LODOP_EM" TYPE="application/x-print-lodop" width="0" height="0"
	       PLUGINSPAGE="/<?php echo $project ?>webpub/js/print/lodop/install_lodop.exe">
</object>
<script type="text/javascript">
    function ocx_download(){
	     window.location = cutoverUrl("ocx_download");
    }	
</script>
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
<script type="text/javascript">

	var LODOP;

	var barcode_height = <?php echo $response['barcode_height']?>;
	var barcode_width = <?php echo $response['barcode_width']?>;

	var barcode_top_offset = <?php echo $response['barcode_top_offset']?>;

	var barcode_left_offset = <?php echo $response['barcode_left_offset']?>;

	var codeStyle = "<?php echo $response['codeStyle']?>";

	var code_cols = <?php echo $response['barcode_col']?>;

	var code_col_width = <?php echo $response['barcode_col_width']?>;


<!--	var dpi = "--><?php //echo $response['dpi']?><!--";-->

	//条码与内容间距
	var barcode_space_between = <?php echo $response['barcode_space_between']?>

	var print_pick = {

		print_prepare: function (print_index,col_index) {
			var col_left = col_index * code_col_width;
			var col_left_px = Math.round(col_left * 3.77953);	//mm -> px
			
			var strHtmlContent = $("#print_index_" + print_index).html();
			//赋到隐藏div内
			$("#tmp_print_content").html(strHtmlContent);

			//判断是否有条码区域
			var _check_exist_barcode_area = $("#tmp_print_content").find('#print_content_index_' + print_index).find('#div_table_barcode_area').html();
			if (!_check_exist_barcode_area) {

				var barcode_value = $("#tmp_print_content").find('#print_content_index_' + print_index).find('img').attr('custom_value');
				if (barcode_value) {

					var __barcode_width = barcode_width - barcode_left_offset;
					LODOP.ADD_PRINT_BARCODE(barcode_top_offset+'mm', (col_left+barcode_left_offset)+'mm', _width +"mm", barcode_height + "mm", codeStyle, barcode_value);
					LODOP.SET_PRINT_STYLEA(0, 'FontSize', 8);
				}
				return;
			}

			//先把临时html的头尾区域删除
			$("#tmp_print_content").find('#print_content_index_' + print_index).find('#div_print_page_top_area').remove();
			$("#tmp_print_content").find('#print_content_index_' + print_index).find('#div_print_page_bottom_area').remove();

			//初始值 ,检查顺序
			var data_index = -1;
			var barcode_index = -1;

			var first = $("#tmp_print_content").find('#print_content_index_' + print_index).find('#div_print_main_major_area').find('div').first();
			var first_id = $(first).attr("id");
			if ('div_table_main_area' == first_id) {

				data_index = 1;
				if ($("#" + first_id).is(':hidden')) {
					data_index = -1;
				}
			}

			if ('div_table_barcode_area' == first_id) {
				barcode_index = 1;
				if ($("#" + first_id).is(':hidden')) {
					barcode_index = -1;
				}
			}

			var last = $("#tmp_print_content").find('#print_content_index_' + print_index).find('#div_print_main_major_area').find('div').last();
			var last_id = $(last).attr("id");
			if ('div_table_main_area' == last_id) {

				data_index = 2;

				if (barcode_index == -1) {
					data_index = 1;
				}

				if ($("#" + last_id).is(':hidden')) {
					data_index = -1;
				}
			}

			if ('div_table_barcode_area' == last_id) {
				barcode_index = 2;

				if (data_index == -1) {
					barcode_index = 1;
				}

				if ($("#" + last_id).is(':hidden')) {
					barcode_index = -1;
				}
			}

			var barcode_value = $("#tmp_print_content").find('#print_content_index_' + print_index).find('img').attr('custom_value');

			if (barcode_index == -1) {
				if ($("#div_table_barcode_area").is(':hidden')) {
					barcode_index = -1;
				}
			}
			//判断顺序结束----

			//抹去条码所在的div
			$("#tmp_print_content").find('#print_content_index_' + print_index).find('#div_print_main_major_area').find("#div_table_barcode_area").remove();

			//数据排前面
			if (data_index == 1) {

				$("#tmp_print_content").show();

				var _height = $("#tmp_print_content").find("#print_content_index_" + print_index).find('#div_print_main_major_area').find('#div_table_main_area').height() + barcode_space_between; //加barcode_space_between是防止html高度不够

				//获取table的高度赋予外部，防止高度超出
				$("#tmp_print_content").find('#print_content_index_' + print_index).css('height', _height);

				$("#tmp_print_content").hide();

				var __strHtmlContent = $("#tmp_print_content").html();

				LODOP.ADD_PRINT_HTM(barcode_top_offset, col_left_px + barcode_left_offset, "<?php if(not_null($response['do_print']['config']['template_page_width'])) echo $response['do_print']['config']['template_page_width'] + 8; else echo '204';?>mm", _height + 'px', __strHtmlContent);

				if (barcode_value && barcode_index != -1) {

					var _top = _height + barcode_top_offset + barcode_space_between;

					var __barcode_width = barcode_width - barcode_left_offset;
					LODOP.ADD_PRINT_BARCODE(_top + 'px', (col_left+ barcode_left_offset)+'mm', __barcode_width+"mm", barcode_height + "mm", codeStyle, barcode_value);

					LODOP.SET_PRINT_STYLEA(0, 'FontSize', 4);
				}
			}

			//条码区域排前面
			if (barcode_index == 1) {

				//剩余html的高度
				var _height = <?php if(not_null($response['do_print']['config']['template_page_height'])) echo $response['do_print']['config']['template_page_height']; else echo '289';?> - barcode_height - barcode_top_offset;

				var __barcode_width = barcode_width - barcode_left_offset;
				LODOP.ADD_PRINT_BARCODE(barcode_top_offset+'mm', (col_left+barcode_left_offset)+'mm', __barcode_width+"mm", barcode_height + "mm", codeStyle, barcode_value);
				LODOP.SET_PRINT_STYLEA(0, 'FontSize', 4);

				if (data_index != -1) {

					//获取table的高度赋予外部，防止高度超出
					var __height = $("#tmp_print_content").find("#print_content_index_" + print_index).find('#div_print_main_major_area').find('#div_table_main_area').height();
					$("#tmp_print_content").find('#print_content_index_' + print_index).css('height', __height);
					var __strHtmlContent = $("#tmp_print_content").html();

					var _top = barcode_height + barcode_top_offset;

					if (__strHtmlContent) {
						LODOP.ADD_PRINT_HTM(_top +"mm", col_left_px + barcode_left_offset, "<?php if(not_null($response['do_print']['config']['template_page_width'])) echo $response['do_print']['config']['template_page_width'] + 8; else echo '204';?>mm", _height + "mm", __strHtmlContent);
					}
				}
			}
		},
		do_print: function () {

			LODOP = getLodop(document.getElementById('LODOP'), document.getElementById('LODOP_EM'));
			var printer_count = LODOP.GET_PRINTER_COUNT();
			if (printer_count < 1) {
				alert('该系统未安装打印设备,请添加相应的打印设备');
				return;
			}
			LODOP.PRINT_INIT('打印');

			var p = 0;

			var is_select_printer = false; //已经选择了打印机
			<?php if(isset($response['do_print']['config']['printer_name']) && !empty($response['do_print']['config']['printer_name'])){?>
			is_select_printer = true;
			LODOP.SET_PRINTER_INDEX("<?php echo $response['do_print']['config']['printer_name'];?>");
			<?php }?>

			if (false == is_select_printer) {
				var p = LODOP.SELECT_PRINTER();
				if (p < 0) {
					return;
				}
			}
			
			//循环打印
			var print_count = $('#print_count').html() ;
			page_count= Math.floor( print_count % code_cols >0 ?  print_count / code_cols +1 : print_count / code_cols );
			for (var i = 0; i < page_count; ++i) {
				LODOP.NEWPAGE();
				for(var j=0;j<code_cols;++j){
					this.print_prepare(i*code_cols+j,j);
				   if(i*code_cols+j >= print_count) break;
				}
			}

			LODOP.SET_PRINT_MODE('PRINT_END_PAGE',page_count);
			
		},
		print_preview: function () {
			this.do_print();
			LODOP.PREVIEW();
		},
		print_start: function () {
			this.do_print();
			LODOP.PRINT();
		}
	}
	
</script>


<style type="text/css">
	body {
		text-align: center;
	}

	div {
		margin: 0 auto;
		text-align: left;
	}
</style>
<title>打印预览<?php if(not_null($request['page_no']) && $request['page_no']) echo '  第'.$request['page_no'].'页' ?></title>
				
				<?php echo get_js("jquery-1.7.min.js,baison.js") ?>
				<?php echo get_js('print/lodop/LodopFuncs.js') ?>
				
</head>
<body>
    <?php include get_tpl_path('web_page_top'); ?>
<input name="" type="button" value="打 印" class="button_main" onclick="print_pick.print_start();"/>
<input name="" type="button" value="打 印 预 览" class="button_main" onclick="print_pick.print_preview();"/>
&nbsp;&nbsp; <a target=_blank onclick="ocx_download();" title="插件如安装有问题，请以管理员身份运行"> 插件下载 </a> <br/>
<div id="print_content">

	<?php if (!empty($response['do_print']['data'])) { ?>

		<span id="print_count" style="display: none;"><?php echo count($response['do_print']['data']) ?></span>
		<?php foreach ($response['do_print']['data'] as $print_key => $print_data) { ?>

			<?php $print_print = not_null($print_data['print_html']) ? $print_data['print_html'] : $print_data['danju_print_content'];?>

			<div id="print_index_<?php echo $print_key ?>" class="danjuContent">

				<?php echo get_webpub('style/css/reset.css');?>
				<?php echo get_webpub('style/css/print.css'); ?>
				<style type="text/css">
					.danjuContent table {border-collapse: collapse;border-spacing: 0;}
					.danjuContent table,
					.danjuContent table th,
					.danjuContent table td {font-size: 8px;}
					.danjuContent div {margin-top: 1px;margin-bottom: 1px;}
					/*.printTable {margin-top: 1px;}*/
					/*.printTable td {padding: 1px;}*/
				</style>

				<div id="print_content_index_<?php echo $print_key ?>"
				     style="width:<?php echo ($print_data['template_page_width'] + 2)*$page_zoom/100; ?>mm;height: <?php echo $print_data['template_page_height'] - 4 ?>mm;overflow:hidden;">
					<?php echo $print_print; ?>
				</div>
			</div>
		<?php } ?>
	<?php } else { ?>
		<div style="text-align: center">
			无打印数据
		</div>
	<?php } ?>
</div>

<script type="text/javascript">

	$(function () {

		$("#print_content").find('.CRC').remove();
		$("#print_content").find('.CRL').remove();
		$("#print_content").find('.CRG').remove();

		$("#print_content").find('.ui-droppable').removeClass('ui-droppable');
		$("#print_content").find('.ui-draggable').removeClass('ui-draggable');
		$("#print_content").find('.CRZ').removeClass('CRZ');
	});
</script>

<div id="tmp_print_content" style="display: none;">

</div>

</body>
</html>
