<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">快递运费：</td>
        <td width="70%">
            <input type="text" id="express_money" value="<?php echo sprintf("%.2f", $response['weigh_express_money']) ?>">
            <span style="color:red">快递公司提供数据</span>
        </td>
    <tr>
        <td align="right">称重运费：</td>
        <td><?php echo sprintf("%.2f", $response['weigh_express_money']) ?></td>
    </tr>
</tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>

<script>
    $(document).ready(function () {
        $("#btn_pay_ok").click(function () {
            var params = {express_no: <?php echo $response['express_no'] ?>, detail_dz_id:<?php echo $response['detail_dz_id'] ?>, express_money:$('#express_money').val()};
            $.post("?app_act=acc/order_express_detail/opt_hx", params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(data.message, type);
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                } else {
                    BUI.Message.Alert(data.message, type);
                }
            }, "json")
        })
    })
</script>