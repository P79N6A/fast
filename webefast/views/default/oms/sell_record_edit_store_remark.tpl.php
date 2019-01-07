<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">仓库留言：</td>
        <td width="70%">
            <textarea id="store_remark" style="width: 80%; height: 39px;" name="store_remark"></textarea>
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
                "sell_record_code_list": <?php echo json_encode(explode(',', $request['sell_record_code_list']))?>,
                "store_remark": $("#store_remark").val()
            };

            $.post("?app_act=oms/sell_record/edit_store_remark_action", params, function(data){
                BUI.Message.Alert(data.message, 'info')
                ui_closePopWindow("<?php echo $request['ES_frmId']?>")
            }, "json")
        })
    })
</script>