
<div>
    <table class='detail_main'>
        <tr>
            <td>收货人：</td><td class='left'><?php echo $response['data']['receiver_name']; ?></td>
            <td>地址：</td><td class='left'><?php echo $response['data']['receiver_address']; ?></td>
        </tr>
        <tr>
            <td>手机：</td><td class='left'><?php echo $response['data']['receiver_mobile']; ?></td>
            <td>电话：</td><td class='left'><?php echo $response['data']['receiver_phone']; ?></td>
        </tr>
        <tr>
            <td>客户留言：</td><td class='left'><?php echo $response['data']['buyer_remark']; ?></td>
            <td>订单客服：</td><td class='left'><?php echo $response['data']['seller_remark']; ?></td>
        </tr>
    </table>
    <table cellspacing="0" class="table table-bordered">
        <tbody>
            <tr>
                <th>商品名称</th>
                <th>商品编码</th>
                <th><?php echo $response['goods_spec1_rename']; ?></th>
                <th><?php echo $response['goods_spec2_rename']; ?></th>
                <th>商品条形码</th>
                <th>数量</th>
            </tr>
            <?php foreach ($response['detail'] as $data): ?>
                <tr>
                    <td><?php echo $data['goods_name']; ?></td>
                    <td><?php echo $data['goods_code']; ?></td>
                    <td><?php echo $data['spec1_name']; ?></td>
                    <td><?php echo $data['spec2_name']; ?></td>
                    <td><?php echo $data['barcode']; ?></td>
                    <td><?php echo $data['num']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

