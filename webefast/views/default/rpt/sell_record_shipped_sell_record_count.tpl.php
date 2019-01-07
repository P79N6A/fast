<div style="padding: 3px;">
    <table style="width: 70%">
        <tr>
            <td style="text-align: right; width: 100px;">销售总金额：</td>
            <td><?php echo $response['pay_money']?>元</td>

            <td style="text-align: right;">邮费总计：</td>
            <td><?php echo $response['express_money']?>元</td>

            <td style="text-align: right;">发货数量总计：</td>
            <td><?php echo $response['goods_count']?>件</td>

            <td style="text-align: right;">订单总计：</td>
            <td><?php echo $response['record_count']?>单</td>
        </tr>
    </table>
</div>