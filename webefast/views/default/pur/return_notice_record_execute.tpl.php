<style type="text/css">
    .button-group .button {
        width: 250px;
        padding: 40px 0;
        margin:0 auto;
    }
    .auto-jump {
        text-align:center;
        margin-top:5px;
        margin-left:10px;
        margin-buttom:10px;
    }
</style>

<div class="button-group return-type" style="text-align:center;">
    <div class="button" id="create_return_by_unfinish">按未完成数生成</div>
    <div class="button" id="crete_return_by_null">生成空白单</div>
</div>
<div style="clear: both"></div>
<div class="auto-jump"><input type="checkbox" name="auto_jump" id="auto_jump" value="checked" checked="checked">自动跳转到采购退货单详情</div>
<input type="hidden" name="return_notice_record_id" id="return_notice_record_id" value="<?php echo $response['data']['return_notice_record_id']; ?>" >
<input type="hidden" name="record_code" id="record_code" value="<?php echo $response['data']['record_code']; ?>" >

<div style="">
    <strong>按未完成数生成：</strong><br>
    采购退货单的商品按照所有采购退货通知单中未完成的商品生成，商品SKU的数量等于未完成数<br>
    <strong>生成空白单：</strong><br>
    生成后的采购退货单仅是一个空白单，无商品明细（采购入库商品可以手工添加或EXCEL导入或扫描商品生成），仅绑定了采购退货通知单<br>
    <strong>自动跳转到采购退货单详情：</strong><br>
    此勾选项如勾选，则无论是“按未完成数生成”或“生成空白单”任一一种模式生成采购退货单，生成后，页面自动跳转到新生成的采购退货单详情页面<br>
</div>
<script type="text/javascript">
    $("#create_return_by_unfinish").click(function () {
        var params = {
            "return_notice_record_id": $("#return_notice_record_id").val(),
            "record_code": $("#record_code").val(),
            "create_type": 'create_return_unfinish',
        };
        create_return_record(params);
    });

    $("#crete_return_by_null").click(function () {
        var params = {
            "return_notice_record_id": $("#return_notice_record_id").val(),
            "record_code": $("#record_code").val(),
            "create_type": 'crete_return_by_null',
        };
        create_return_record(params);
    });

    function create_return_record(params) {
        $.post("?app_act=pur/return_notice_record/create_return_record", params, function (data) {
            var return_record_id = data.data;
            var type = (data.status == 1) ? 'success' : 'error';
            if (type != 'success') {
                BUI.Message.Alert(data.message, type);
            } else {

                if ($("[name='auto_jump']:checked").val() == 'checked') {
                    openPage('<?php echo base64_encode('?app_act=pur/return_record/view&return_record_id') ?>' + return_record_id, '?app_act=pur/return_record/view&return_record_id=' + return_record_id, '采购退货单');
                    // 	        this.close(); 
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                } else {
                    BUI.Message.Alert(data.message, 'info');
                    if (data.status == 1)
                        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                }
            }
        }, "json");
    }
</script>