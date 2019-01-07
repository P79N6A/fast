<style type="text/css">
    .well {
        min-height: 35px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '采购统计分析',
    'links' => array(
       // array('url' => 'pur/purchase_record/detail&app_scene=add', 'title' => '添加采购入库单', 'is_pop' => true, 'pop_size' => '500,550'),
       // array('url' => 'pur/purchase_record/import&app_scene=add', 'title' => '导出', 'is_pop' => true, 'pop_size' => '500,300'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$record_time_start = date("Y-m");
$record_time_start = $record_time_start.'-01';
render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(
        
   array(
        'label' => '查询',
        'id' => 'btn-search',
           'type'=>'submit'
    ),
    array(
         'label' => '导出',
         'id' => 'exprot_list',
    ),
         ) ,
    'fields' => array(
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '供应商',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'supplier_code',
            'data' => load_model('base/SupplierModel')->get_purview_supplier()
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
            <td style="text-align: right; width: 150px;">采购商品数量总计：</td>
            <td><span id="record_num_all"></span>件</td>

            <td style="text-align: right;">采购商品金额总计：</td>
            <td><span id="record_money_all"></span>元</td>

            <td style="text-align: right;">退回商品数量总计：</td>
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
                'title' => '供应商',
                'field' => 'supplier_name',
                'width' => '150',
                'align' => '',
            ),
            //array(
            //    'type' => 'text',
            //    'show' => 1,
            //    'title' => '仓库',
            //    'field' => 'store_name',
            //    'width' => '150',
            //    'align' => '',
            //),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购商品数量',
                'field' => 'record_num',
                'width' => '150',
                'align' => '',
            ),
            array(
            	'type' => 'text',
            	'show' => 1,
            	'title' => '采购商品总金额',
            	'field' => 'record_money',
            	'width' => '150',
            	'align' => ''
            ),
            array(
            	'type' => 'text',
            	'show' => 1,
            	'title' => '退回商品数量',
            	'field' => 'return_num',
            	'width' => '150',
            	'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货商品总金额',
                'field' => 'return_money',
                'width' => '150',
                'align' => ''
            ), 
        )
    ),
    'dataset' => 'pur/PurReportModel::get_report_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'supplier_code',
    'export'=> array('id'=>'exprot_list','conf'=>'purchase_analyse_report_list','name'=>'采购统计分析','export_type' => 'file'),//
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
    'init' => 'nodata',

));
?>

<script type="text/javascript">
    function do_view(_index, row) {
        var record_time_start = $("#record_time_start").val();
        var record_time_end = $("#record_time_end").val();
        var store_code=$("#store_code").val();
        var url =  "?app_act=pur/purchase_analyse/view&supplier_code=" + row.supplier_code+"&record_time_start="+record_time_start+"&record_time_end="+record_time_end+"&store_code="+store_code;
        openPage('<?php echo base64_encode('?app_act=pur/purchase_analyse/view') ?>',url,'采购统计分析商品明细');
    }


    //汇总
    $(document).ready(function () {
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });

        function count_all(obj) {
            $.post("?app_act=pur/purchase_analyse/report_count", obj, function (data) {
                $("#record_num_all").html(data.record_num_all);
                $("#record_money_all").html(data.record_money_all);
                $("#return_num_all").html(data.return_num_all);
                $("#return_money_all").html(data.return_money_all);
            }, "json");
        }
    });
</script>