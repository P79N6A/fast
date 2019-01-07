<table cellspacing="0" class="table table-bordered">
    <thead>
        <tr>
            <th>商品名称</th>
            <th>商品编码</th>
            <th>商品规格</th>
            <th>商品条形码</th>
            <th>数量(实物锁定)</th>
            <th>单价</th>
            <th>均摊金额</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($response['detail_list'] as $key => $detail) { ?>
            <tr class="detail_<?php echo $detail['sell_goods_id']; ?>">
                <td><?php echo $detail['goods_name']; ?></td>
                <td><?php echo $detail['goods_code']; ?></td>
                <td><?php echo $response['goods_spec1'] . '：' . $detail['spec1_name'] . ' | ' . $response['goods_spec2'] . '：' . $detail['spec2_name']; ?></td>
                <td><?php echo $detail['barcode']; ?></td>
                <td name="num">
                    <div>
                        <?php echo $detail['num']; ?>(<span class="num" param1="<?php echo $detail['sell_goods_id']; ?>" param2="<?php echo $detail['sku']; ?>"><?php echo $detail['lock_num']; ?></span>)
                    </div>
                    <input class="new_num" onblur="change_avg_money('<?php echo $detail['num']; ?>', '<?php echo sprintf("%.2f", $detail['avg_money']); ?>', this)" style="width: 30px; text-align: center; display: none;" type="text" value="<?php echo $detail['num']; ?>">
                </td>

                <td><?php echo sprintf("%.2f", $detail['goods_price']); ?></td>
                <td><?php echo sprintf("%.2f", $detail['avg_money']); ?></td>
                <td style="width: 12%">
                    <button class="button button-small change" title="等价换货" onclick="change_goods('<?php echo $detail['record_code']; ?>', '<?php echo $detail['goods_name']; ?>', '<?php echo $detail['goods_code']; ?>', '<?php echo $detail['sku']; ?>', '<?php echo $detail['barcode']; ?>', '<?php echo $response['record']['send_store_code']; ?>', '<?php echo $detail['spec1_name']; ?>', '<?php echo $detail['spec2_name']; ?>', '<?php echo $detail['avg_money']; ?>', '<?php echo $detail['sell_goods_id'] ?>', '<?php echo $detail['num']; ?>')" disabled="disabled"><i class="icon-pencil"></i></button>
                    <button class="button button-small edit" title="编辑" onclick="detail_edit(<?php echo $detail['sell_goods_id']; ?>)" disabled="disabled"><i class="icon-edit"></i></button>
                    <button class="button button-small delete" title="删除" onclick="detail_delete(<?php echo $detail['sell_goods_id']; ?>)" disabled="disabled"><i class="icon-trash"></i></button>
                    <button class="button button-small save hide" title="保存" onclick="detail_save(<?php echo $detail['sell_goods_id']; ?>)"><i class="icon-ok"></i></button>
                    <button class="button button-small cancel hide" title="取消" onclick="detail_cancel(<?php echo $detail['sell_goods_id']; ?>)"><i class="icon-ban-circle"></i></button>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php echo load_js('common.js') ?>
<script>
    function change_goods(record_code, goods_name, goods_code, sku, barcode, store_code, spec1_name, spec2_name, avg_money, sell_goods_id, num) {
        var url = "?app_act=oms_shop/oms_shop/change_goods_view&record_code=" + record_code + '&goods_name=' + goods_name + '&goods_code=' + goods_code + '&store_code=' + store_code + '&spec1_name=' + spec1_name + '&sku=' + sku + '&barcode' + barcode + '&spec2_name=' + spec2_name + '&sell_goods_id=' + sell_goods_id + '&num=' + num + '&avg_money=' + avg_money;
        new ESUI.PopWindow(url, {
            title: '等价换货',
            width: 750,
            height: 500,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                window.location.reload();
            }
        }).show();
    }

    $(function () {
        BUI.use('bui/tooltip', function (Tooltip) {
            var tips = new Tooltip.Tips({
                tip: {
                    trigger: '.api_refund_desc', //出现此样式的元素显示tip
                    alignType: 'top', //默认方向
                    elCls: 'panel',
                    width: 200,
                    zIndex: '1000000',
                    titleTpl: ' <div class="panel-body" style="background-color:#FFFF99">{title}</div>',
                    offset: 5
                }
            });
            tips.render();
        });
    });
</script>