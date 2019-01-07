<?php
$arry = array('goods_spec1','goods_spec2');
$arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arry);
$goods_spec1_rename = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
$goods_spec2_rename = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
?>
<table cellspacing="0" class="table table-bordered">
    <tbody>
        <tr>
            <th>商品名称</th>
            <th>商品编码</th>
            <th><?php echo $goods_spec1_rename;?></th>
            <th><?php echo $goods_spec2_rename;?></th>
            <th>商品条形码</th>
            <th>批次</th>
            <th>生产日期</th>
            <th>实物锁定</th>
            <th>实物库存</th>
        </tr>
        <?php foreach($response['data'] as $data): ?>
        <tr>
            <td style="width:25%"><?php echo $data['goods_name']; ?></td>
            <td><?php echo $data['goods_code']; ?></td>
            <td><?php echo $data['spec1_name']; ?></td>
            <td><?php echo $data['spec2_name']; ?></td>
            <td><?php echo get_barcode_by_sku($data['sku']); ?></td>
            <td><?php echo $data['lof_no']; ?></td>
            <td><?php echo $data['production_date']; ?></td>
            <td style="width:10%"><?php echo $data['num']; ?></td>
            <td style="width:10%"><?php echo $data['lof_inv_num']; ?></td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>

