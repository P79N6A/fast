<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
</style>
<?php render_control('PageHead', 'head1',
    array('title' => '分销订单导入',

        'links' => array(

        ),
        'ref_table' => 'table'
    ));?>

<form action="?app_act=oms/sell_record/import_fenxiao_trade_action" enctype="multipart/form-data" method="post" onsubmit="return check();">
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
//-->
</script>