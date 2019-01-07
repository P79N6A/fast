<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">订单已付金额：</td>
        <td width="70%">
            <input type="text" id="paid_money" value="<?php echo sprintf("%.2f", $response['record']['payable_money'])?>">
        </td>
    </tr>
    <tr>
        <td align="right">订单应付金额：</td>
        <td><?php echo sprintf("%.2f", $response['record']['payable_money']);?></td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_pay_ok").click(function(){
            var params = {sell_record_code: <?php echo $request['sell_record_code']?>,paid_money:$("#paid_money").val()};
            $.post("?app_act=oms/sell_record/opt_pay", params, function(data){
                if(data.status != "1"){
                    alert(data.message)
                } else {
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                }
            }, "json")
        })
    })
</script>