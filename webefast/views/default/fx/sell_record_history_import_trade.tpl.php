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

                <!--<a class="button" target="_blank" href="?app_act=sys/file/get_file&type=1&name=oms_fx_order_send_import.xlsx&down_name=<?php // echo urlencode('分销已发货订单导入模板')?>" >模版下载</a>-->
                
                <a class="button" target="_blank"  href="<?php echo get_excel_url("oms_fx_order_send_import.xlsx",1,'分销已发货订单导入模板')?>">模版下载</a><br>
            </div>
        </div>
        <div class="row">
            <div class="control-group span11">
                <span style="color:red;">支持分销单导入，确保店铺建议无误，若为网络代销，店铺性质需为分销必须绑定分销商。  </span><br>
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