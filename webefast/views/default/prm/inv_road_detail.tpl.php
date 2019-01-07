<?php
    render_control('PageHead', 'head1', array('title' => '在途库存详情查询', 'ref_table' => 'table'));
?>

<?php
$options = array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select(),
        ),
        array(
            'label' => '商品编号',
            'type' => 'input',
            'id' => 'goods_code'
        ),
        array(
            'label' => '商品条形码',
            'type' => 'input',
            'id' => 'barcode',
            'title' => '商品条形码',
        ),
    )
);
render_control('SearchForm', 'searchForm', $options);
?>

<?php
$options_conf_list = array(
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '单据类型',
            'field' => 'order_type',
            'width' => '100',
            'align' => ''
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '单据编号',
            'field' => 'record_code',
            'width' => '150',
            'align' => '',
            'format_js' => array(
                'type' => 'html',
                'value' => '<a href=\\\'javascript:planned_record_detail_view({planned_record_id})\\\'>{record_code}</a>',
            ),
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '商品条形码',
            'field' => 'barcode',
            'width' => '150',
            'align' => ''
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '计划日期',
            'field' => 'planned_time',
            'width' => '120',
            'align' => '',
            'format' => array('type' => 'date')
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '入库期限',
            'field' => 'in_time',
            'width' => '120',
            'align' => '',
            'format' => array('type' => 'date')
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '供应商',
            'field' => 'supplier_name',
            'width' => '100',
            'align' => ''
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '采购数量',
            'field' => 'num',
            'width' => '100',
            'align' => ''
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '完成数量',
            'field' => 'finish_num',
            'width' => '100',
            'align' => ''
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '在途库存',
            'field' => 'road_num',
            'width' => '100',
            'align' => ''
        ),
    );
$options = array(
    'conf' => array('list' => $options_conf_list),
    'dataset' => 'prm/InvRecordModel::inv_road_detail',
    'queryBy' => 'searchForm',
    'idField' => 'goods_inv_id',
//    'init' => 'nodata',
//    'customFieldTable' => 'pur/planned_record_do_list',
//    'export' => array('id' => 'exprot_list', 'conf' => 'planned_record_list', 'name' => '采购计划单','export_type'=>'file'),
    'params' => array('filter' => $response['filter']),
//    'RowNumber'=>true,
//    'CheckSelection'=>true,
//    'events' => array('rowdblclick' => 'showDetail',),
);
render_control('DataTable', 'table', $options);
?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#searchForm #store_code").val('<?php echo isset($response['filter']['store_code']) ? $response['filter']['store_code'] : ""; ?>');
        $("#searchForm #barcode").val('<?php echo isset($response['filter']['barcode']) ? $response['filter']['barcode'] : ""; ?>');
        $("#searchForm #goods_code").val('<?php echo isset($response['filter']['goods_code']) ? $response['filter']['goods_code'] : ""; ?>');
        $("#searchForm #store_code_select_multi .bui-select-input").click();
        $("div[class='bui-list-picker bui-picker bui-overlay bui-ext-position x-align-bl-tl']").css("visibility","hidden");
        $("#searchForm #barcode").blur();
        $("#btn-search").hide();
    });
    function planned_record_detail_view(planned_record_id) {
        openPage('<?php echo base64_encode('?app_act=pur/planned_record/view&planned_record_id') ?>' + planned_record_id, '?app_act=pur/planned_record/view&planned_record_id=' + planned_record_id, '采购订单详情');
    }
</script>

