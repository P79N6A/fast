<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">订单备注：</td>
        <td width="70%">
            <textarea id="remark" style="width: 80%; height: 39px;" name="remark"></textarea>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_pay_ok").click(function(){
            var params = {
                "purchaser_record_code_list": <?php echo json_encode(explode(',', $request['purchaser_record_code_list']))?>,
                "remark": $("#remark").val(),
            };
            $.post("?app_act=pur/accounts_payable/edit_remark_action", params, function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert('添加成功！', function () {
                        ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                    }, 'success');
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json")
        })
    })
</script>