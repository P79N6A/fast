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

	<script>

		var LODOP;
		var print_pick = {

			print_prepare:function() {

				var strHtmlContent = $("#print_content").html();
				LODOP.SET_PRINT_PAGESIZE(0,"<?php if(not_null($response['do_print']['config']['template_page_width'])) echo $response['do_print']['config']['template_page_width'] + 8; else echo '210';?>mm","<?php if(not_null($response['do_print']['config']['template_page_height'])) echo $response['do_print']['config']['template_page_height']; else echo '297';?>mm","<?php if(not_null($response['do_print']['config']['template_page_style'])) echo $response['do_print']['config']['template_page_style']; else echo 'A4';?>");

				LODOP.ADD_PRINT_HTM("0mm","0mm","<?php if(not_null($response['do_print']['config']['template_page_width'])) echo $response['do_print']['config']['template_page_width'] + 8; else echo '204';?>mm","<?php if(not_null($response['do_print']['config']['template_page_height'])) echo $response['do_print']['config']['template_page_height']; else echo '289';?>mm",strHtmlContent);

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
			//	LODOP.PREVIEW();
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
		<?php echo get_webpub('style/css/reset.css');?>

		<style type="text/css">
			.danjuContent table{ border-collapse:collapse; border-spacing:0;}
			.danjuContent table,
			.danjuContent table th,
			.danjuContent table td{ font-size:14px;}
			.danjuContent div {margin-top: 1px;margin-bottom: 2px;}
		</style>

		<?php if (!empty($response['do_print']['data'])) {?>

			<?php foreach($response['do_print']['data'] as $print_key=>$print_data) {?>
				<?php $print_print = $print_data['danju_print_content'];?>
				<div class="danjuContent" >
					<div style="width:<?php echo $print_data['template_page_width']-10;?>mm;">
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
</body>

<script type="text/javascript">

	function calculate_ext_col_width () {

		if ($('.inner_table_ext_print_col').length <= 0) {
			return false;
		}

		$(".inner_table_ext_print_col").css('border', 0);

		var td_count = $(".inner_table_ext_print_col").find("tr").eq(0).find("td").length;
		var tr_count = $(".inner_table_ext_print_col").find("tr").length;

		$(".inner_table_ext_print_col").find('tr').each(function() {
			$(this).find('td').each(function(){
				$(this).css('width','38px');
				$(this).css('word-wrap', 'break-word');
				$(this).css('word-break', 'break-all');
			})
		});

		$(".inner_table_ext_print_col").find('tr').first().find('td').css('border-top', 0);
		$(".inner_table_ext_print_col").find('tr').last().find('td').css('border-bottom', 0);

		$(".inner_table_ext_print_col").find('tr').find('td:first').css('border-left', 0);
		$(".inner_table_ext_print_col").find('tr').find('td:last').css('border-right', 0);

		for(var i = 0; i < tr_count; ++i) {
			for(var j = 0; j < td_count; ++j) {

				$(".td_print_ext_col_"+j).css('word-wrap', 'break-word');
				$(".td_print_ext_col_"+j).css('word-break', 'break-all');

				var td_width = $(".td_print_ext_col_"+j).css('width');
				$(".inner_table_ext_print_col").find("tr").eq(i).find("td").eq(j).css('width', td_width);
			}
		}
	}

//	calculate_ext_col_width();
</script>

</html>
