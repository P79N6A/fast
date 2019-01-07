<style type="text/css">
    .form-horizontal{
        margin-left:-200px; border:1px solid #ded6d9; width:400px; height:300px; position:absolute; left:50%; top:5%;
    }
    .tab-tr{
        line-height:20px; margin-left:30px; margin-top:10px;
    }
    .tab-td1{
        padding:15px 0 0 40px; margin-left:30px; width:100px;  color:#999999; font-size:14px;
    }
    .tab-td2{
        padding:15px 0 0 40px; font-size:14px;
    }
</style>

<form class="form-horizontal">

    <?php if (!empty($response['message'])): ?>
        <div><?php echo $response['message']; ?> </div>
    <?php else: ?>

        <table width="100%">
            <tr class='tab-tr'><td class='tab-td1' >买家昵称：</td><td class='tab-td2'><?php echo $response['buyer_nick'] ?></td></tr>
            <tr class='tab-tr'><td class='tab-td1'>退单编号：</td><td class='tab-td2'><?php echo $response['sell_return_code'] ?></td></tr>
            <tr class='tab-tr'><td class='tab-td1'>退单状态：</td><td class='tab-td2'><?php echo $response['return_status'] ?></td></tr>
            <tr class='tab-tr'><td class='tab-td1'>平台交易号：</td><td class='tab-td2'><?php echo $response['deal_code'] ?></td></tr>
            <tr class='tab-tr'><td class='tab-td1'>退货物流：</td><td class='tab-td2'><?php echo $response['return_express_name'] ?></td></tr>
            <tr class='tab-tr'><td class='tab-td1'>退款说明：</td><td class='tab-td2'><?php echo $response['return_buyer_memo'] ?></td></tr>
            <tr class='tab-tr'><td class='tab-td1'>买家支付宝：</td><td class='tab-td2'><?php echo $response['buyer_ali_pay_no'] ?></td></tr>
            <tr class='tab-tr'><td class='tab-td1'>退款金额：</td><td class='tab-td2'><?php echo $response['refund_total_fee'] ?></td></tr>
        </table>
    <?php endif; ?>

</form>

<script type="text/javascript">
</script>