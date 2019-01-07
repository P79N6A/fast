<style>
    #delivery_time_start, #delivery_time_end, #check_time_start, #check_time_end {width: 100px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '客服绩效分析',
    'ref_table' => 'table'
));
?>

<?php
$status = array(
    'all' => '全部',
    '0' => '已确认未发货',
    '4' => '已确认已发货'
);
$status = array_from_dict($status);
$check_time_start = date('Y-m') . '-01 00:00:00';
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
            'label' => '确认时间',
            'type' => 'group',
            'field' => 'check_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'check_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'check_time_end', 'remark' => ''),
            )
        ),
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
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ),
        array(
            'label' => '订单状态',
            'type' => 'select',
            'id' => 'status',
            'data' => $status,
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
                'type' => 'text',
                'show' => 1,
                'title' => '客服编号',
                'field' => 'user_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客服名称',
                'field' => 'confirm_person',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单笔数',
                'field' => 'count_sell_record',
                'width' => '100',
                'align' => '',
                'sortable' => true,
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '退单笔数',
//                'field' => 'count_sell_return',
//                'width' => '100',
//                'align' => '',
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '退单率',
//                'field' => 'return_rate',
//                'width' => '100',
//                'align' => '',
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '总笔数',
//                'field' => 'count_record',
//                'width' => '100',
//                'align' => '',
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品数量',
                'field' => 'sum_record_num',
                'width' => '100',
                'align' => '',
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '退单数量',
//                'field' => 'sum_return_num',
//                'width' => '100',
//                'align' => '',
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '总数量',
//                'field' => 'sum_number',
//                'width' => '100',
//                'align' => '',
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单金额',
                'field' => 'sum_record_money',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '退单金额',
//                'field' => 'sum_return_money',
//                'width' => '100',
//                'align' => '',
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '总金额',
//                'field' => 'sum_money',
//                'width' => '100',
//                'align' => '',
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '业绩金额',
//                'field' => 'sum_money',
//                'width' => '100',
//                'align' => '',
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客单价<img height="23" width="23" src="assets/images/tip.png" title="客单价=订单金额/订单笔数" />',
                'field' => 'unit_price',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '连带率<img height="23" width="23" src="assets/images/tip.png" title="连带率=商品数量/订单笔数" />',
                'field' => 'related',
                'width' => '100',
                'align' => '',
            ),
        ),
    ),
    'dataset' => 'rpt/CustomServiceModel::get_by_list_page', //get_page_data
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'custom_service_list', 'name' => '客服绩效分析','export_type' => 'file'),
    'idField' => 'sell_record_id',
    'init' => 'nodata',	
//    'customFieldTable' => 'rpt/report_jxc_do_list',
//    'CheckSelection' => false,
));
?>

<script type="text/javascript">
    $(function(){
        $('#check_time_start').val("<?php echo $check_time_start?>");
    })
//    $(function () {
//        searchFormFormListeners['beforesubmit'].push(function(ev) {
//            var obj = searchFormForm.serializeToObject();
//            load_count(obj);
//         
//        });
//    });
//    function load_count(obj) {
//        $.post("?app_act=rpt/custom_service/custom_service_count",obj, function(data) {
//            $("#data_count").html(data);
//        });
//    }
</script>




