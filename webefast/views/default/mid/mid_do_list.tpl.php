<style>

    .control-label #keyword_type {
        margin-top: 2px;
        width: 90px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '单据管理',
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '我方单据号';
$keyword_type['deal_code'] = '平台交易号';
$keyword_type['api_record_code'] = '对方交易号';
$keyword_type = array_from_dict($keyword_type);

$time_type = array();
$time_type['upload_request_time'] = '上传时间';
$time_type['order_time'] = '发货时间';
$time_type['process_time'] = '处理时间';
$time_type['cancel_response_time'] = '取消时间';
$time_type = array_from_dict($time_type);

$order_supplier = array();
$order_supplier['']= '全部';
$order_supplier['sell_record'] = '销售订单';
$order_supplier['sell_return'] = '销售退单';
$order_supplier['wbm_store_out'] = '批发销货单';
$order_supplier['wbm_return'] = '批发退货单';
$order_supplier['sell_record_rb'] = '销售订单日报';
$order_supplier['sell_return_rb'] = '销售退单日报';
$order_supplier = array_from_dict($order_supplier);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type','type' => 'select','data' => $keyword_type),
            'type' => 'input',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '单据类型',
            'title' => '',
            'type' => 'select',
            'id' => 'supplier_code',
            'data' => $order_supplier,
        ),
        array(
            'label' => 'eFAST仓库',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('mid/MidOptModel')->get_mid_store(),
        ),
        array(
            'label' => array('id' => 'time_type', 'type' => 'select', 'data' => $time_type),
            'type' => 'group',
            'field' => 'daterange1',
            'data' => $time_type,
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'time_end', 'remark' => ''),
            )
        ),
    )
));
?>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => true, 'id' => 'tabs_all'),
        array('title' => '待上传', 'active' => false, 'id' => 'tabs_wait_upload'),
        array('title' => '已上传', 'active' => false, 'id' => 'tabs_have_uploaded'),
        array('title' => '待发货/待收货', 'active' => false, 'id' => 'tabs_wait_order'),
        array('title' => '待处理', 'active' => false, 'id' => 'tabs_wait_process'),
        array('title' => '已发货/已收货', 'active' => false, 'id' => 'tabs_ordered'),
        array('title' => '已取消', 'active' => false, 'id' => 'tabs_cancel'),
        array('title' => '上传/处理失败', 'active' => false, 'id' => 'tabs_fail'),
    ),
    'for' => 'TabPage1Contents'
));
?>


<?php

$buttons = array(
//    array(
//        'id' => 'view',
//        'title' => '详情',
//        'act' => "pop:wms/wms_trade/view&task_id={id}&type={$response['wmsId']}",
//        'show_name' => '详情',
//        'show_cond' => '',
//        'priv' => 'wms/wms_trade/view',
//        'pop_size' => '920,600'
//    ),
    array(
        'id' => 'upload',
        'title' => '上传',
        'callback' => 'upload',
        'confirm' => '确认要上传吗？',
        'priv' => 'mid/mid/upload',
        'show_cond' => '(obj.upload_request_flag == 0  && obj.upload_response_flag == 0  && obj.order_flow_end_flag == 0)||( (obj.upload_response_flag == 0 || obj.upload_response_flag == 20)   && obj.order_flow_end_flag == 0)'
    ),
//    array('id' => 'cancel',
//        'title' => '取消',
//        'callback' => 'cancel',
//        'confirm' => '确认要取消吗？',
//        'priv' => 'wms/wms_mgr/cancel',
//        'show_cond' => '(obj.cancel_request_flag == 0 || obj.cancel_response_flag == 20) && obj.upload_response_flag == 10 && obj.wms_order_flow_end_flag == 0'
//    ),
    array(
        'id' => 'order_shipping',
        'title' => '处理',
        'callback' => 'order_shipping',
        'confirm' => '确认要处理吗？',
        'priv' => 'mid/mid/order_shipping',
        'show_cond' => 'obj.order_flow_end_flag == 1 && obj.process_flag < 30'
    ),
);


$list = array(
    array(
        'type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '80',
        'align' => '',
        'buttons' => $buttons,
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '单据类型',
        'field' => 'record_order_type',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '状态',
        'field' => 'status',
        'width' => '80',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '我方单据号',
        'field' => 'record_code',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '平台交易号',
        'field' => 'deal_code',
        'width' => '120',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '仓库',
        'field' => 'store_name',
        'width' => '80',
        'align' => ''
    ),
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '对方单据编号',
    'field' => 'api_record_code',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '上传时间',
    'field' => 'upload_response_time',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '取消时间',
    'field' => 'cancel_response_time',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '收发货时间',
    'field' => 'order_time',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '处理时间',
    'field' => 'process_time',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '日志',
    'field' => 'log_err_msg',
    'width' => '100',
    'align' => ''
);

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'mid/MidOptModel::do_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CheckSelection' => true
));
?>


<script type="text/javascript">
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
    });
    tableStore.on('beforeload', function (e) {
        e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });

    function upload(index, row) {
        var d = {"task_id": row.id, "app_fmt": 'json'};
        $.post('<?php echo get_app_url('mid/mid/upload'); ?>', d, function (data) {
            var type = data.status === 1 ? 'success' : 'error';
            var msg = data.status === 1 ? '上传成功' : data.message;
            BUI.Message.Alert(msg, type);
            tableStore.load();
        }, "json");
    }



    function order_shipping(index, row) {
        var d = {"task_id": row.id,  "app_fmt": 'json'};
        $.post('<?php echo get_app_url('mid/mid/order_shipping'); ?>', d, function (data) {
            var type = data.status === 1 ? 'success' : 'error';
            var msg = data.status === 1 ? '处理成功' : data.message;
            BUI.Message.Alert(msg, type);
            tableStore.load();
        }, "json");
    }
    </script>