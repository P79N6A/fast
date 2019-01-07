<style>
    #upload_time_start,#upload_time_end{
        width: 100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '零售日报',
    'links' => array(
        array('url' => 'erp/bserp/create_daily_report', 'title' => '生成日报', 'is_pop' => true, 'pop_size' => '490,510'),
    ),
    'ref_table' => 'table'
));
?>
<?php
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
            'label' => '单据编号',
            'type' => 'input',
            'id' => 'record_code'
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop']
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => $response['store']
        ),
        array(
            'label' => '单据类型',
            'type' => 'select',
            'id' => 'record_type',
            'data' => $response['record_type']
        ),
        array(
            'label' => '分销订单',
            'type' => 'select',
            'id' => 'is_fenxiao',
            'data' => ds_get_select_by_field('boolstatus', 1),
        ),
         array(
                'label' => '业务日期',
                'type' => 'group',
                'field' => 'daterange1',
                'child' => array(
                        array('title' => 'start', 'type' => 'date', 'field' => 'record_date_start'),
                        array('pre_title' => '~', 'type' => 'date', 'field' => 'record_date_end', 'remark' => ''),
                ),
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
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '140',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({id})">{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'title' => '单据类型',
                'field' => 'record_type_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '分销订单',
                'field' => 'is_fenxiao_order',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '仓库',
                'field' => 'store_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '总数量',
                'field' => 'quantity',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '总金额',
                'field' => 'amount',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '总运费<img height="23" width="23" src="assets/images/tip.png" class="tip" data-align="bottom-left" title="销售退单的总运费包含赔付金额以及手工调整金额" />',
                'field' => 'express_amount',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '备注',
                'field' => 'remark',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '业务日期',
                'field' => 'record_date',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '生成时间',
                'field' => 'create_time',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'erp/BserpModel::get_erp_do_list',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>

<script>
    //数据行双击打开详情页
    function showDetail(_index, row) {
        view(row.id);
    }
    //单击单据编号打开详情页
    function view(_id) {
        var url = '?app_act=erp/bserp/daily_report_detail&id=' + _id;
        openPage(window.btoa(url), url, '零售日报详情');
    }
</script>



