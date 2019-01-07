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
    <div class="row" style=" margin-top: 50px;margin-bottom: 15px;">
        <div class="control-group span11">
            <input type="radio" name="radio_record"  value="1" checked="checked"/><span>普通导入（需按照系统模板导入）</span>&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="radio_record"  value="2"/><span>分销导入（需按照系统模板导入）</span><span id ="fx_hint"></span>
<!--                <div id ="fx_hint">
                </div>-->
        </div>
    </div>
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
            <!--a class="button" target="_blank" href="?app_act=sys/excel_import/tplDownload_by_code&tpl=oms_sell_record&name=<?php echo urlencode('订单导入模板')?>">模版下载</a-->
           
            <a class="button" target="_blank" onclick="download_record();">模版下载</a>
            
        </div>
    </div>
</div>
</form>
<?php if ($response['xiachufang'] == 1){?>
<div>
<form action="?app_act=oms/sell_record/import_xcf_trade_action" enctype="multipart/form-data" method="post" onsubmit="return xcf_check();">
<div class="upload1">
    <div class="row">
        <div class="control-group span11">
            <label class="control-label span3">下厨房模版上传：</label>
            <div class="span8 controls">
				<input type="file" id="xcf_file" name="fileData"/>
            </div>
        </div>

    </div>
    <input type="hidden" id="url" name="url">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button id="submit" class="button button-primary" type="submit">导入下厨房订单</button>
           <span style="color: red"> 直接从下厨房后台导出订单即可导入</span>
        </div>
    </div>
</div>
</form>
</div>
<?php }?>
<script type="text/javascript">
    $(function() {
        $('[name=radio_record]').click(function(){
            var type = $('[name=radio_record]:checked').val();
            if(type == 1) {
                $('#fx_hint').html('');
            } else {
                $('#fx_hint').html('<span style="color:red;">支持分销单导入，确保店铺建议无误，若为网络代销，店铺性质需为分销必须绑定分销商。  </span>');
            }
            
        })
    })
    function download_record() {
        var type = $('[name=radio_record]:checked').val();
        var url;
        if(type == 1) {
            url = "<?php echo get_excel_url("order_import.xlsx",1,'订单导入模板') ?>";
        } else {
            url = "<?php echo get_excel_url("fx_order_import.xlsx",1,"分销订单导入模板") ?>";
        }
        window.open(url);
    }
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