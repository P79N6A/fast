<?php echo get_js('ui-1.8.18.min.js, tab.js,common.js,baison.js'); ?>
<?php echo get_webpub('style/css/print.css');?>
<?php echo get_webpub('style/css/blitzer/ui-1.8.18.css');?>
<div class="table_01" style="margin-top:10px;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<th>单据模板名称</th>
		<th>纸张类型</th>
		<th>纸张宽度</th>
		<th>纸张高度</th>
		<th>打印机</th>
		<th width="200">操作</th>
	</tr>
	<?php
		if ($response['list']):
		foreach ($response['list'] as $val):
	?>
	<tr>
		<td align="center"><?php echo $val['danju_print_name']; ?></td>
		<td align="center"><?php if ('custom_pager' ==$val['template_page_style']) echo '自定义纸张'; else  echo $val['template_page_style'];?></td>
        <td align="center"><?php echo $val['template_page_width'];?></td>
        <td align="center"><?php echo $val['template_page_height'];?></td>
        <td align="center" id="td_printer_name_<?php echo $val['print_id']; ?>"><?php echo $val['printer_name'];?></td>
		<td align="center">
			<a href="javascript:void(0);" onclick="addTab('<?php echo $val['danju_print_name'].'打印设置'?>','?app_act=common/danju_print/edit_print&print_id=<?php echo $val['print_id']; ?>&danju_print_code=<?php echo $val['danju_print_code']; ?>')">编辑</a>
			<a href="javascript:void(0);" onclick="addTab('<?php echo $val['danju_print_name'].'打印预览'?>','?app_act=common/danju_print/view_print&print_id=<?php echo $val['print_id']; ?>&danju_print_code=<?php echo $val['danju_print_code']; ?>')">预览</a>
            <a href="javascript:void(0);" onclick="danju_template.setDefaultPaper(<?php echo $val['print_id'];?>);">设置纸张</a>
			<?php if (!$val['is_default']) {?>
				<a href="javascript:void(0);" onclick="danju_template.set_default_print_data_type('<?php echo $val['print_data_type']; ?>',<?php echo $val['print_id']; ?>);">设为默认</a>
			<?php } ?>
            <a href="javascript:void(0);" onclick="modSettingPrinter.selectPrinter(<?php echo $val['print_id']; ?>);">修改打印机</a>
		</td>
	</tr>
	<?php endforeach;
		else:
	?>
	<tr>
		<td colspan="2">没有记录</td>
	</tr>
	<?php endif;?>
</table>
</div>

<div id="load_dialog"></div>

<script type="text/javascript">

	var danju_template = {
        setDefaultPaper:function(print_id){

            $('#load_dialog').dialog({
                modal:true,
                resizable:false,width:300,height:210,autoOpen:false,
                title:"设置纸张",buttons:{
                    "取消":function(){
                        $("#load_dialog").dialog("close");
                    },
                    "保存":function(){
	                    var url = '?app_act=common/danju_print/do_set_page_style&app_page=null&app_fmt=json&'+$('#updatePaper').serialize();
                        $.post(url,function(data){
	                        var ret = $.parseJSON(data);
                            if(1 != ret.status) {
                                alert(ret.message);
	                            return false;
                            }
                            alert("设置成功!");
                            refresh();
//	                        $("#load_dialog").dialog("close");
                        });
                    }
                }
            });
            var url = "?app_act=common/danju_print/set_page_style&app_page=null&print_id=" + print_id;
            $('#load_dialog').load(url).dialog("open");
        },
		set_default_print_data_type:function(print_data_type, print_id) {

			var params = {};
			params.print_data_type = print_data_type;
			params.print_id = print_id;
			var url = '?app_act=common/danju_print/set_default&app_page=null&app_fmt=json';
			$.post(url, params, function(data){
				var ret = $.parseJSON(data);
				alert(ret.message);
				refresh();
			})
		}
	}
</script>

<OBJECT ID="LODOP" CLASSID="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" WIDTH=0  HEIGHT=0>
    <param name="Caption"  value="">
    <param name="Border"   value="1">
    <param name="CompanyName" value="上海百胜软件">
    <param name="License" value="452547275711905623562384719084">
	<?php $project = $GLOBALS['context']->config['project'];?>
    <embed id="LODOP_EM" TYPE="application/x-print-lodop" width="0" height="0" PLUGINSPAGE="/<?php echo $project?>webpub/js/print/lodop/install_lodop.exe">
</OBJECT>

<?php echo get_js('print/lodop/LodopFuncs.js'); ?>
<script type="text/javascript">
    var LODOP;
    var modSettingPrinter = {
        selectPrinter:function(print_id) {

            LODOP = getLodop(document.getElementById('LODOP'),document.getElementById('LODOP_EM'));

            var printer_count = LODOP.GET_PRINTER_COUNT();
            if (printer_count<1) {
                alert('该系统未安装打印设备,请添加相应的打印设备');
                return;
            }
            //选择打印机
            var p = LODOP.SELECT_PRINTER();
            if (p<0) {
                return;
            }
            //获取打印机名称
            var printer_name = LODOP.GET_PRINTER_NAME(p);
            var params = {print_id:print_id,printer_name:printer_name};
          //  loadingDialog('提示', '提交中...');
            $.post('?app_act=common/danju_print/select_printer&app_fmt=json', params, function(data) {
           //     hideLoadingdialog();
                var ret=$.parseJSON(data);
                if (ret.status == 1) {
                    $("#td_printer_name_"+print_id).html(printer_name);
                } else {
                    alert(ret.message);
                }
	            refresh();
            });
        }
    }
</script>