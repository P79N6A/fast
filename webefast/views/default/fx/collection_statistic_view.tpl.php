<style>
</style>
<?php
//$is_power = load_model('sys/PrivilegeModel')->check_priv('fx/account/add');
$links = '';
//if ($is_power == true) {
////    $links = array(array('url' => 'fx/account/detail&app_scene=add', 'title' => '新增', 'is_pop' => true, 'pop_size' => '400,500'));
//}
render_control('PageHead', 'head1', array('title' => '收款统计明细  ',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit',
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => '单据编号',
            'type' => 'input',
            'id' => 'record_code',
            'title' => '',
        ),
        array(
            'label' => '付款状态',
            'title' => '',
            'type' => 'select',
            'id' => 'pay_status',
            'data' => ds_get_select_by_field('pay_status'),
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '添加收款记录', 'callback' => 'do_add','show_cond' => 'obj.pay_status == 1'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库时间',
                'field' => 'is_store_out_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商名称',
                'field' => 'custom_code_name',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '货款金额',
                'field' => 'goods_money',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '运费',
                'field' => 'express_money',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单金额',
                'field' => 'money',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '待付金额',
                'field' => 'pending_money',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付金额',
                'field' => 'pay_money',
                'width' => '120',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'fx/CollectionStatisticModel::get_detail_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'store_out_record_id',
    'params' => array('filter' => array('custom_code' => $response['custom_code'], 'store_out_months' => $response['store_out_months'])),
//    'init' => 'nodata',
    'export' => array('id' => 'exprot_list', 'conf' => 'fx_collection_statistic_view', 'name' => '收款统计明细', 'export_type' => 'file'),//
//    'CheckSelection' => true,
//    'events' => array(
//        'rowdblclick' => 'showDetail',
//    ),
));
?>
<script type="text/javascript">
    //数据行双击打开新页面显示详情
    function do_add(_index, row) {
        openPage('<?php echo base64_encode('?app_act=fx/pending_payment/add&record_code=') ?>' + row.record_code, '?app_act=fx/pending_payment/add&record_code=' + row.record_code, '添加收款记录');
    }

</script>




