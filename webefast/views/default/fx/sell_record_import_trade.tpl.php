<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    .span11 {
        width: 970px;
    }
</style>
<?php render_control('PageHead', 'head1',
    array('title' => '订单导入',

        'links' => array(

        ),
        'ref_table' => 'table'
    ));?>

<form action="?app_act=oms/sell_record/import_trade_action" enctype="multipart/form-data" method="post" onsubmit="return check();">
<div class="upload1">
    <div class="row">
        <div class="control-group span11">
            <label class="control-label span3">文件上传：</label>
            <div class="span8 controls">
				<input type="file" name="fileData"/>
            </div>
        </div>

    </div>
    <input type="hidden" id="url" name="url">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button id="submit" class="button button-primary" type="submit">导入订单</button>
            <!--<a class="button" target="_blank"  href="?app_act=sys/file/get_file&type=1&name=fx_order_import.xlsx&down_name=<?php // echo urlencode('分销订单导入模板')?>">模版下载</a><br>-->
            <?php if($response['login_type'] != 2) {?>
                <a class="button" target="_blank"  href="<?php echo get_excel_url("fx_order_import.xlsx",1,'分销订单导入模板')?>">模版下载</a><br>
            <?php } else { ?>
                <a class="button" target="_blank"  href="<?php echo get_excel_url("fx_login_order_import.xlsx",1,'分销订单导入模板')?>">模版下载</a><br>
            <?php } ?>
        </div>
    </div>
    <div class="row">
        <div class="control-group span11">
            <span style="color:red;">支持分销单导入，确保店铺建议无误，若为网络代销，店铺性质需为分销必须绑定分销商。  </span><br>
        </div>

    </div>
</div>
</form>
<script type="text/javascript">
<!--
	function check(){
		if ($(":file").val() == ''){
			alert('请选择要上传的文件');
			return false;
		}
		return true;
	}
	function xcf_check(){
		if ($("#xcf_file").val() == ''){
			alert('请选择要上传的文件');
			return false;
		}
		return true;
	}
//-->
</script>