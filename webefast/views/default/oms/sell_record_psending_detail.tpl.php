<tr id='<?php echo "tr".$response['sell_record_code']; ?>' class="bui-grid-row bui-grid-row-odd">
    <td colspan='3'></td>
    <td colspan='9'>
        <?php $result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2)); ?>
        <table cellspacing="0" class="table table-bordered">
            <tbody>
                <tr>
                    <th>商品名称</th>
                    <th>商品编码</th>
                    <th>商品条形码</th>
                    <th><?php echo $result['data'][0]['name']; ?></th>
                    <th><?php echo $result['data'][1]['name']; ?></th>
                    <th>平台规格</th>
                    <th>数量(实物锁定数)</th>
                    <th>标准价</th>
                    <th>单价</th>
                    <th>均摊金额</th>
                    <th>预售</th>
                    <th>礼品</th>
                    <th>计划发货时间</th>
                </tr>
                <?php foreach ($response['detail'] as $data): ?>
                    <tr>
                        <td style="width:20%"><?php echo $data['goods_name']; ?></td>
                        <td><?php echo $data['goods_code']; ?></td>
                        <td><?php echo $data['spec1_name']; ?></td>
                        <td><?php echo $data['spec2_name']; ?></td>
                        <td><?php echo $data['sku_name']; ?></td>
                        <td><?php echo $data['platform_spec']; ?></td>
                        <td><?php echo $data['num']; ?>(<?php echo $data['lock_num'];?>)</td>
                        <td><?php echo $data['goods_price']; ?></td>
                        <td><?php echo $data['goods_price']; ?></td>
                        <td></td>
                        <td><?php echo $data['avg_money']; ?></td>
                        <td><?php echo $data['is_gift']=='0'?'否':'是'; ?></td>
                        <td><?php echo $data['shipping_time']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </td>
</tr>

