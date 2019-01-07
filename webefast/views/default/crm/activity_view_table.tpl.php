<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'do_delete',
                        'title' => '删除',
                        'callback' => 'delete_goods',
                        'confirm' => '确认要删除吗？',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['type'] == 0 ? '商品规格' : '子商品信息',
                'field' => 'spec',
                'width' => '240',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '可用库存',
                'field' => 'inv_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售数量',
                'field' => 'sell_num',
                'width' => '65',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动上报库存',
                'field' => 'update_num',
                'width' => '100',
                'align' => '',
                'editor' => (0 == $response['status'] && 0 == $response['is_first']) ? "{xtype:'number'}" : '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '剩余库存',
                'field' => 'result_inv',
                'width' => '65',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'crm/ActivityGoodsModel::get_crm_goods_by_page',
    'idField' => 'activity_id',
    'params' => array(
        'filter' => array('activity_code' => $response['code'], 'type' => $response['type'], 'shop_code' => $response['shop_code'], 'start_time' => $response['start_time'], 'end_time' => $response['end_time']),
    ),
    'CellEditing' => true,
));
?>
<script type="text/javascript">
    if (typeof tableCellEditing != "undefined") {
        //数量、价格修改回调操作

        tableCellEditing.on('accept', function (record, editor) {

            if (record.record.update_num < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                tableStore.load();
                return;
            }
            var _record = record.record;
            $.post('?app_act=crm/activity/do_edit_num',
                    {shop_code: "<?php echo $response['shop_code']; ?>", activity_code: "<?php echo $response['code']; ?>", sku: _record.sku, barcode: _record.barcode, inv_num: _record.inv_num, update_num: _record.update_num, start_time: "<?php echo $response['start_time']; ?>", end_time: "<?php echo $response['end_time']; ?>"},
                    function (result) {
                        BUI.Message.Alert('修改成功');
                        tableStore.load();
                    }, 'json');
        });
    }
</script>