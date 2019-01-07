<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作者',
                'field' => 'user_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作名称',
                'field' => 'action_name',
                'width' => '95',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作时间',
                'field' => 'action_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据状态',
                'field' => 'record_status',
                'width' => '95',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'action_note',
                'width' => '350',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/TaobaoAgLogModel::get_by_page',
    'idField' => 'id',
    'params' => array('filter' => array('refund_id' => $response['refund_id'], 'page_size' => 10)),
    //'RowNumber'=>true,
    //'CheckSelection' => true,
));
?>

<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">


</script>

