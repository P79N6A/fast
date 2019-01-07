<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">支付方式：</td>
        <td width="70%">
            <select style="width: 155px;" name="pay_code" id="pay_code">
                <?php foreach ($response['pay_way'] as $val) { ?>
                    <option value="<?php echo $val['pay_type_code'] ?>" <?php echo $val['pay_type_code'] == 'cash' ? 'selected' : ''; ?>><?php echo $val['pay_type_name'] ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="30%" align="right">支付金额：</td>
        <td width="70%">
            <input style="width: 150px;" type="text" id="pay_money" value="<?php echo sprintf("%.2f", $response['record']['payable_amount'] - $response['record']['buyer_real_amount']) ?>">
        </td>
    </tr>
    <tr>
        <td width="30%" align="right">已付金额：</td>
        <td width="70%">
            <?php echo sprintf("%.2f", $response['record']['buyer_real_amount']) ?>
        </td>
    </tr>
    <tr>
        <td width="30%" align="right">应付金额：</td>
        <td width="70%"><?php echo sprintf("%.2f", $response['record']['payable_amount']); ?></td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>

<script>
    $(document).ready(function () {
        $("#btn_pay_ok").click(function () {
            var data = {pay_code: $("#pay_code").val(), pay_money: $("#pay_money").val()};
            var params = {type: 'pay', record_code: <?php echo $request['record_code'] ?>, data: data};
            $.post("?app_act=oms_shop/oms_shop/opt", params, function (ret) {
                if (ret.status != 1) {
                    BUI.Message.Alert(ret.message, 'error');
                } else {
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                }
            }, "json");
        });
    });
</script>