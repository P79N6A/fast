<div>绑定批发通知单：列表中仅展示已确认、未终止、未绑定的批发通知单</div>


<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '批发通知单编号',
                'field' => 'record_code',
                'width' => '300',
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
    'dataset' => 'wbm/NoticeRecordModel::get_by_page',
    'idField' => 'notice_record_id',
    'params' => array('filter' => array('is_relation' => 0, 'is_fenye' => '0')),
    //'RowNumber'=>true,
    'CellEditing' => true,
));
?>

<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    function relation(index, row) {
        var d = {"notice_record_id": row.notice_record_id, 'po_id':<?php echo $response['data']['po_id']; ?>, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_po/do_relation'); ?>', d, function (data) {

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, function () {
                ui_closePopWindow(<?php echo $response['data']['ES_frmId']; ?>);
            }, type);


        }, "json");



    }

</script>

