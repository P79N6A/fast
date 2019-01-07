<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    .span11 {
        width: 970px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '已发货单导入',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>

<form action="?app_act=oms/sell_record/history_import_trade_action" enctype="multipart/form-data" method="post" onsubmit="return check();">
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
                <button id="submit" class="button button-primary" type="submit">导入</button>
                <!--a class="button" target="_blank" href="?app_act=sys/excel_import/tplDownload_by_code&tpl=oms_sell_record&name=<?php echo urlencode('订单导入模板') ?>">模版下载</a-->

                <a class="button" onclick="download_record();" target="_blank" >模版下载</a>
            </div>
        </div>
        <div class="row">
            <div class="control-group span11">
                <label style="color:red;font-size:12px" >注:单次最大支持10000条数据导入.</label>
                <div class="span8 controls">

                </div>
            </div>

        </div>
    </div>
</form>

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
            url = "<?php echo get_excel_url("oms_order_send_import.xlsx",1,'已发货单导入模板') ?>";
        } else {
            url = "<?php echo get_excel_url("oms_fx_order_send_import.xlsx",1,'分销已发货单导入模板')?>";
        }
        window.open(url);
    }
<!--
    function check() {
        if ($(":file").val() == '') {
            alert('请选择要上传的文件');
            return false;
        }
        return true;
    }
    function xcf_check() {
        if ($("#xcf_file").val() == '') {
            alert('请选择要上传的文件');
            return false;
        }
        return true;
    }
//-->
</script>