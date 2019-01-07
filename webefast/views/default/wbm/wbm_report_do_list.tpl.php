<?php
render_control('PageHead', 'head1', array('title' => '批发统计分析',
    'links' => array(
        // array('url' => 'pur/purchase_record/detail&app_scene=add', 'title' => '添加采购入库单', 'is_pop' => true, 'pop_size' => '500,550'),
        // array('url' => 'pur/purchase_record/import&app_scene=add', 'title' => '导出', 'is_pop' => true, 'pop_size' => '500,300'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$fenxiao = load_model('base/CustomModel')->get_purview_custom_select('pt_fx',4);


render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
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
    'fields' => array(
        array(
            'label' => '业务时间',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '分销商',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'distributor_code',
            'data' => $fenxiao
        ),
        array(
            'label' => '仓库',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
    )
));
?>
<div style="padding: 3px;">
    <table style="width: 85%">
        <tr>
            <td style="text-align: right; width: 150px;">批发商品数量总计：</td>
            <td><span id="record_num_all"></span>件</td>

            <td style="text-align: right;">批发商品金额总计：</td>
            <td><span id="record_money_all"></span>元</td>

            <td style="text-align: right;">退货商品数量总计：</td>
            <td><span id="return_num_all"></span>件</td>

            <td style="text-align: right;">退货商品金额总计：</td>
            <td><span id="return_money_all"></span>元</td>
        </tr>
    </table>
</div>
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
                    array(
                        'id' => 'view',
                        'title' => '查看商品明细',
                        'callback' => 'do_view'
                    ),
                ),
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '分销商',
            		'field' => 'custom_name',
            		'width' => '120',
            		'align' => '',
            ),
//            array(
//            		'type' => 'text',
//            		'show' => 1,
//            		'title' => '仓库',
//            		'field' => 'store_name',
//            		'width' => '120',
//            		'align' => '',
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库数量',
                'field' => 'out_num',
                'width' => '120',
                'align' => '',

            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '出库商品总金额',
            		'field' => 'out_money',
            		'width' => '120',
            		'align' => '',
  
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货数量',
                'field' => 'in_num',
                'width' => '120',
                'align' => '',

            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '退货商品总金额',
            		'field' => 'in_money',
            		'width' => '120',
            		'align' => ''
            ),
          
            
        )
    ),
    'dataset' => 'wbm/WbmReportModel::get_report_by_page',
    'queryBy' => 'searchForm',
    'export'=> array('id'=>'exprot_list','conf'=>'wbm_report_do_list','name'=>'批发统计分析','export_type' => 'file'),//
    'idField' => 'custom_code',
            'init'=>'nodata',
));
?>

<script type="text/javascript">


    /**
     * 
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        var store_code = $('#store_code').val();
        var url = "?app_act=wbm/wbm_report/do_detail_list&distributor_code=" + row.custom_code + '&record_time_start=' + $('#record_time_start').val() + "&record_time_end=" + $('#record_time_end').val() + "&store_code=" + store_code;
        openPage('<?php echo base64_encode('?app_act=wbm/wbm_report/do_detail_list&do_detail_list') ?>', url, '批发统计分析商品明细');

    }
    //汇总
    $(function () {
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });
    });
    function count_all(obj) {
        $.post("?app_act=wbm/wbm_report/record_count", obj, function (data) {
            $("#record_num_all").html(data.out_num);
            $("#record_money_all").html(data.out_money);
            $("#return_num_all").html(data.in_num);
            $("#return_money_all").html(data.in_money);
        }, "json");
    }
    

</script>

