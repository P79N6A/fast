<div style="color: red">绑定库存锁定：列表中仅展示已锁定的库存锁定单</div>
<br />
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库存锁定编号',
                'field' => 'record_code',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '95',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'is_add_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '50',
                'align' => '',
                'buttons' => array(
                    array('id' => 'relation', 'title' => '绑定', 'callback' => 'relation'),
                ),
            ),
        )
    ),
    'dataset' => 'stm/StockLockRecordModel::get_by_page',
    'idField' => 'stock_lock_record_id',
    'params' => array('filter' => array('status' => 1, 'lock_obj' => 0)),
    //'RowNumber'=>true,
    'CellEditing' => true,
));
?>

<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    function relation(index, row) {
        var d = {"lock_record_id": row.stock_lock_record_id, 'po_id':<?php echo $response['data']['po_id']; ?>, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_po/do_relation_lock'); ?>', d, function (data) {

            var type = data.status == 1 ? 'success' : 'error';
            if (type == 'error') {
                BUI.Message.Alert(data.message, function () {
                }, type);
            } else {
                BUI.Message.Alert(data.message, function () {
                    ui_closePopWindow(<?php echo $response['data']['ES_frmId']; ?>);
                }, type);
            }
        }, "json");
    }

</script>

