<style type="text/css">
    #record_time_start,#record_time_end,#delivery_time_start,#delivery_time_end{width:100px;}
</style>
<?php
render_control(
    'PageHead',
    'head1',
    array(
        'title'     => '验货员绩效',
        'ref_table' => 'table'
    )
);
$store_arr = load_model('base/StoreModel')->get_purview_store();
array_unshift($store_arr, array('store_code' => '', 'store_name' => '请选择'));
render_control('SearchForm', 'searchForm', array(
    'buttons' =>
    array(
        0 =>
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        1 =>
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' =>
    array(
        array(
            'label' => '发货时间',
            'type' => 'group',
            'field' => 'delivery_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'delivery_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'delivery_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '仓库',
            'type'  => 'select',
            'id'    => 'store_code',
            'data'  => $store_arr
        ),
    ),
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' =>
    array(
        'list' =>
        array(
            array(
                'type'  => 'text',
                'show'  => 1,
                'title' => '客服编号',
                'field' => 'user_id',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type'  => 'text',
                'show'  => 1,
                'title' => '客服名称',
                'field' => 'delivery_person',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type'     => 'text',
                'show'     => 1,
                'title'    => '验货订单笔数',
                'field'    => 'order_num',
                'width'    => '100',
                'align'    => '',
                'sortable' => true,
            ),
            array(
                'type'  => 'text',
                'show'  => 1,
                'title' => '验货商品数',
                'field' => 'product_num',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type'  => 'text',
                'show'  => 1,
                'title' => '一单一品订单数',
                'field' => 'sin_record_num',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type'  => 'text',
                'show'  => 1,
                'title' => '一单多品订单数',
                'field' => 'mul_record_num',
                'width' => '100',
                'align' => '',
            ),
        ),
    ),
    'dataset' => 'rpt/GoodsPerformanceModel::get_by_list_page', //get_page_data
    'queryBy' => 'searchForm',
    'export'  => array('id' => 'exprot_list', 'conf' => 'goods_performance_list', 'name' => '验货绩效分析'), // ,'export_type' => 'file'
    'idField' => 'sell_record_id',
    'init'    => 'nodata',
));
?>

<script type="text/javascript">
    $('#btn-search').on('click', function () {
        if ('' === $('#store_code').val()) {
            BUI.Message.Alert('请选择仓库', 'warning');
            return false;
        }
    });
    $('#delivery_time_start').val('<?php echo date('Y-m').'-01 00:00:00'; ?>');
</script>






