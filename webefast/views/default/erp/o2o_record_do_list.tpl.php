<style>
    #process_batch_task_tips div { height: 300px; overflow-y: scroll; }
    .control-label #keyword_type {
        margin-top: 2px;
        width: 90px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '单据列表',
        'links' => array(),
        'ref_table' => 'table'
    )
);
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = 'eFAST订单号';
$keyword_type['new_record_code'] = '新订单号';
$keyword_type['api_record_code'] = 'O2O单据号';
$keyword_type = array_from_dict($keyword_type);

$time_type = array();
$time_type['upload_request_time'] = '上传时间';
$time_type['api_order_time'] = '发货时间';
$time_type['process_time'] = '处理时间';
$time_type['cancel_request_time'] = '取消时间';
$time_type = array_from_dict($time_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'show_row' => 3,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '单据类型',
            'type' => 'select',
            'id' => 'record_type',
            'data' =>ds_get_select_by_field('o2o_record_type'),
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'sys_store_code',
            'data' => load_model('erp/O2oRecordReportModel')->get_o2o_store_all(),
        ),
        array(
            'label' => array('id' => 'time_type', 'type' => 'select', 'data' => $time_type),
            'type' => 'group',
            'field' => 'daterange1',
            'data' => $time_type,
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'start_time',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'end_time', 'remark' => ''),
            )
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            'data' => load_model('base/SaleChannelModel')->get_all_select()
        ),
        array(
            'label' => '取消状态',
            'type' => 'select',
            'id' => 'cancel_request_flag',
            'data' => load_model('wms/WmsTradeModel')->getWmsFlag('cancel_request_flag'),
        )
    ),
));
?>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => false, 'id' => 'tabs_all'),
        array('title' => '待上传', 'active' => true, 'id' => 'tabs_wait_upload'),
        array('title' => '待发货/待收货', 'active' => false, 'id' => 'tabs_wait_order'),
        array('title' => '待处理', 'active' => false, 'id' => 'tabs_wait_process'),
        array('title' => '已发货/已收货', 'active' => false, 'id' => 'tabs_ordered'),
        array('title' => '已取消', 'active' => false, 'id' => 'tabs_cancel'),
        array('title' => '操作失败', 'active' => false, 'id' => 'tabs_fail'),
    ),
    'for' => 'TabPage1Contents'
));
?>
<?php
$buttons = array(
    array('id' => 'upload', 'title' => '上传', 'callback' => 'upload', 'confirm' => '确认要上传吗？', 'priv' => '', 'show_cond' => '(obj.upload_request_flag == 0  && obj.upload_response_flag == 0 && obj.api_order_flow_end_flag == 0)||(obj.upload_request_flag == 0  && (obj.upload_response_flag == 0 || obj.upload_response_flag == 20) && obj.api_order_flow_end_flag == 0)||(obj.upload_response_flag == 20 && obj.cancel_response_flag!=10)'),
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
        'field' => 'record_type_name',
        'width' => '80',
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
        'title' => '订单号',
        'field' => 'record_code',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '新订单号',
        'field' => 'new_record_code',
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
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '销售平台',
        'field' => 'sale_channel_name',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺',
        'field' => 'shop_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '交易号',
        'field' => 'deal_code',
        'width' => '120',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '买家昵称',
        'field' => 'buyer_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => 'O2O单据号',
        'field' => 'api_record_code',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '上传时间',
        'field' => 'upload_request_time',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '取消时间',
        'field' => 'cancel_request_time',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '收发货时间',
        'field' => 'api_order_time',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '处理时间',
        'field' => 'process_time',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '日志',
        'field' => 'log_err_msg',
        'width' => '100',
        'align' => ''
    ),
);

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'erp/O2oRecordReportModel::do_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
  //  'params' => array('filter' => array('wmsId' => $response['wmsId'])),
    'customFieldTable' => 'erp/o2o_oms_trade',
    //'CheckSelection' => true
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
        var params = {"id": row.id, "app_fmt": 'json'};
        $.post('<?php echo get_app_url('erp/o2o_record/record_upload'); ?>', params, function (data) {
            var type = data.status === 1 ? 'success' : 'error';
            var msg = data.status === 1 ? '上传成功' : data.message;
            BUI.Message.Alert(msg, type);
            tableStore.load();
        }, "json");
    }

</script>