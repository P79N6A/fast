<style>
    .page_container {padding: 0 1% 0px;}
</style>
<div style="color: red">强制将该单设为已处理，单据状态推送值修改为： </div>
<br />
<?php if ($response['ag_record_type'] == 2) { ?>
    <div style="text-align: center;">
        <input type="radio" style="width:40px;" class="" name="push_val" value="SUCCESS" checked="checked"/>取消成功&nbsp;&nbsp;
        <input type="radio" style="width:40px;" class="" name="push_val" value="FAIL"/>取消失败
    </div>
<?php } else { ?>
    <div style="text-align: center;">
        <input type="radio" style="width:40px;" class="" name="push_val" value="SUCCESS" checked="checked"  />已入库
    </div>

<!--    <input type="radio" style="width:40px;" class="" name="push_val" value="FAIL" />拒绝入库-->
<?php } ?>

<br/><br/>
<div class=" actions-bar">
    <div class="" style="text-align: center;">
        <button type="button" id="ok" class="button button-primary">确认</button>&nbsp;&nbsp;&nbsp;
        <button type="button" id="cancel" class="button button-primary" onclick="do_cancel()">取消</button>
    </div>
</div>

<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    var ES_frmId = '<?php echo $request['ES_frmId']?>';
    var ag_record_type = '<?php echo $response['ag_record_type']?>';
    var refund_id = '<?php echo $response['refund_id']?>';

    $("#ok").click(function () {
        var params = {
            'refund_id': refund_id,
            'ag_record_type': ag_record_type,
            'push_val': $("input[name='push_val']:checked").val(),
        };
        $.post("?app_act=oms/taobao_ag/do_set_process", params, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (type == 'error') {
                BUI.Message.Alert(data.message, 'error');
            } else {
                BUI.Message.Alert(data.message, function () {
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                }, type);
            }
        }, "json");
    });

    function do_cancel() {
        ui_closePopWindow(ES_frmId);
    }
</script>

