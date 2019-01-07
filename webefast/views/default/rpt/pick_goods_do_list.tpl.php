<style type="text/css">
    #delivery_time_start, #delivery_time_end {width: 100px;}
</style>
<?php
render_control('PageHead', 'head1',
    array(
        'title' => '拣货绩效统计',
        'ref_table' => 'table'
    )
);
render_control('SearchForm', 'searchForm', array(
    'buttons' =>
        array(
            array(
                'label' => '查询',
                'id' => 'btn-search',
                'type' => 'submit'
            ),
            array(
                'label' => '导出',
                'id' => 'exprot_list',
            ),
        ),
    // 'show_row' => 2,
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
        ),
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' =>
            array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '员工代码',
                    'field' => 'staff_code',
                    'width' => '140',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '员工名称',
                    'field' => 'staff_name',
                    'width' => '150',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '拣货订单笔数',
                    'field' => 'pick_record_num',
                    'width' => '120',
                    'align' => '',
                    'format_js' => array(
                        'type' => 'html',
                        'value' => '<a href="javascript:view({staff_id})">{pick_record_num}</a>',
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '拣货商品数',
                    'field' => 'pick_goods_num',
                    'width' => '120',
                    'align' => '',
                ),
            ),
    ),
    'dataset' => 'rpt/WavesRecordReportModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'pick_goods_list', 'name' => '拣货绩效分析', 'export_type' => 'file'), //
    'idField' => 'waves_record_id',
    'init' => 'nodata',
));
?>

<script type="text/javascript">
    $('#delivery_time_start').val('<?php echo date('Y-m') . '-01 00:00:00'; ?>');

    function view(staff_id) {
        var url = '?app_act=oms/waves_record/do_list&staff_id=' + staff_id;
        openPage(window.btoa(url), url, '订单波次打印');
    }



</script>






