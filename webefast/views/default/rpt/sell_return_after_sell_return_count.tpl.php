<style>
    .wh{margin-right: 5px;}
</style>
<div style="padding: 10px;">
    <table style="width: 95%">
        <tr>
            <td style="text-align: right; width: 100px;">退货总数量：</td>
            <td class='wh'><?php echo $response['return_num'] ?>件</td>

            <td style="text-align: right;">退款总金额：</td>
            <td class='wh'><?php echo $response['return_money'] ?>元</td>

            <td style="text-align: right;">赔付总金额：</td>
            <td class='wh'><?php echo $response['compensate_money'] ?>元</td>

            <td style="text-align: right;">卖家承担运费总金额：</td>
            <td class='wh'><?php echo $response['seller_express_money'] ?>元</td>

            <td style="text-align: right;">手工调整总金额：</td>
            <td class='wh'><?php echo $response['adjust_money'] ?>元</td>

            <td style="text-align: right;">商品退货总金额：</td>
            <td class='wh'><?php echo $response['avg_money'] ?>元</td>
        </tr>
    </table>
</div>