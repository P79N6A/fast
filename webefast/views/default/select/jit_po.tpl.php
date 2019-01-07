<style type="text/css">
    .well .control-group {
        width: 45%;
    }
    #table_srcoll{
        display:none !important;
    }
</style>
<?php
require_lib('util/oms_util', true);
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'show_row' => 1,
    'col'=>1,
    'fields' => array(
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => oms_opts_by_tb('base_shop', 'shop_code', 'shop_name', array('sale_channel_code' => 'weipinhui')),
        ),
        array(
            'label' => '档期号',
            'type' => 'input',
            'id' => 'po_no'
        ),
        array(
            'label' => '待拣货',
            'type' => 'select',
            'id' => 'notice_record_num',
            'data'=>array(array('', '全部'), array('1', '否'), array('2', '是')),
            'value'=>'2'
        ),
        array(
            'label' => '批发通知单号',
            'type' => 'input',
            'id' => 'notice_record_no'
        ),
        array(
            'label' => '开始时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'st_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'st_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '结束时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'et_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'et_time_end', 'remark' => ''),
            )
        ),
    )
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '档期号',
                'field' => 'po_no',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '开始时间',
                'field' => 'sell_st_time',
                'width' => '140',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '结束时间',
                'field' => 'sell_et_time',
                'width' => '140',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'PO开始时间',
                'field' => 'po_start_time',
                'width' => '140',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '未拣货数',
                'field' => 'not_pick',
                'width' => '80',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitPoModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CheckSelection' => true
));
?>
<?php echo_selectwindow_js($request, 'table', array('id' => 'po_no', 'code' => 'po_no', 'name' => 'po_no')) ?>
