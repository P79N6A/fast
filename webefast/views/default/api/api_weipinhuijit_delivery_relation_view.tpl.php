<div>未确认出库的出库单</div>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库单号',
                'field' => 'delivery_no',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '送货仓库',
                'field' => 'warehouse_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'insert_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'relation', 'title' => '绑定', 'callback' => 'relation'),
                ),
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitDeliveryModel::get_by_page',
    'idField' => 'delivery_id',
    'params' => array('filter' => array('is_delivery' => 0, 'warehouse' => $response['data']['warehouse'])),
    'CellEditing' => true,
));
?>

<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    function relation(index, row) {
        var d = {"delivery_no": row.delivery_no, 'store_out_record_no':<?php echo $response['data']['store_out_record_no']; ?>, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_delivery/do_relation'); ?>', d, function (data) {

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, function () {
                ui_closePopWindow(<?php echo $response['data']['ES_frmId']; ?>);
            }, type);


        }, "json");



    }

</script>

