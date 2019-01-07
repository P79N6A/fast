<style type="text/css">
    .well {
        min-height: 100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '商品进销存报表',
    'ref_table' => 'table'
));
?>

<?php
$price_type_arr = array();
$price_type_arr[] = array('sell_price', '吊牌价');
if ($response['price_status']['cost'] == 1) {
    $price_type_arr[] = array('cost_price', '成本价');
}
if ($response['price_status']['purchase'] == 1) {
    $price_type_arr[] = array('purchase_price', '进货价');
}
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_barcode'] = '商品条形码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type = array_from_dict($keyword_type);

$order_date_start = date('Y-m') . '-01';
$order_date_end = date('Y-m-d');

$spec_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));

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
    'fields' =>
    array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select(),
        ),
        array(
            'label' => '年份',
            'type' => 'select_multi',
            'id' => 'year_code',
            'data' => ds_get_select('year'),
        ),
        array(
            'label' => '分类',
            'type' => 'select_multi',
            'id' => 'category_code',
            'data' => load_model('util/FormSelectSourceModel')->get_catagory(),
        ),
        array(
            'label' => '品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            'data' => load_model('prm/BrandModel')->get_purview_brand(),
        ),
        array(
            'label' => '季节',
            'type' => 'select_multi',
            'id' => 'season_code',
            'data' => load_model('util/FormSelectSourceModel')->get_season(),
        ),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' =>
            array(
                array(
                    'title' => 'start',
                    'type' => 'date',
                    'field' => 'order_date_start',
                ),
                array(
                    'pre_title' => '~',
                    'type' => 'date',
                    'field' => 'order_date_end',
                    'remark' => '',
                ),
            ),
            'help' => '业务开始日期未选择，默认为本月第一天；
业务结束日期未选择，默认为当前时间',
        ),
        array(
            'label' => '价格',
            'type' => 'select',
            'id' => 'price_type',
            'data' => $price_type_arr,
        ),
        array(
            'label' => '仓库类别',
            'type' => 'select_multi',
            'id' => 'store_type_code',
            'data' => load_model('base/StoreTypeModel')->get_select(),
        ),

    array (
      'label' => '库存变化',
      'type' => 'select',
      'id' => 'is_have_change',
      'data' => array(array('0','全部'),array('1','有库存变化'),array('2','无库存变化')),
    ),
    ),
));
?>
<div style="padding: 3px;">
    <table style="width: 100%">
        <tr style="">
            <td style="text-align: right;">期初库存总数：</td>
            <td><span id="qc_all"></span></td>

            <td style="text-align: right;">采购入库总数：</td>
            <td><span id="purchase_all"></span></td>

            <td style="text-align: right;">采购退货总数：</td>
            <td><span id="pur_return_all"></span></td>

            <td style="text-align: right;">批发销货总数：</td>
            <td><span id="wbm_store_out_all"></span></td>

            <td style="text-align: right;">批发退货总数：</td>
            <td><span id="wbm_return_all"></span></td>

            <td style="text-align: right;">调整总数：</td>
            <td><span id="adjust_all"></span></td>

            <td style="text-align: right;">移入总数：</td>
            <td><span id="shift_in_all"></span></td>

            <td style="text-align: right;">移出总数：</td>
            <td><span id="shift_out_all"></span></td>

        </tr>
        <tr>
            <td style="text-align: right;">零售发货总数：</td>
            <td><span id="sell_record_all"></span></td>

            <td style="text-align: right;">零售退货总数：</td>
            <td><span id="sell_return_all"></span></td>

            <td style="text-align: right;">入库总数：</td>
            <td><span id="storage_in_num_all"></span></td>

            <td style="text-align: right;">出库总数：</td>
            <td><span id="storage_out_num_all"></span></td>

            <td style="text-align: right;">期末库存总数：</td>
            <td><span id="qm_all"></span></td>

            <td style="text-align: right;">期初总金额：</td>
            <td><span id="qc_je_all"></span></td>

            <td style="text-align: right;">期末总金额：</td>
            <td><span id="qm_je_all"></span></td>
        </tr>
    </table>
</div>
<?php
render_control('DataTable', 'table', array(
    'conf' =>
    array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $spec_arr['goods_spec1'],
                'field' => 'spec1_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $spec_arr['goods_spec2'],
                'field' => 'spec2_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '期初库存',
                'field' => 'qc',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '期初金额',
                'field' => 'qc_je',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '入库总数',
                'field' => 'storage_in_num',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购入库数',
                'field' => 'purchase',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购退货数',
                'field' => 'pur_return',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '批发销货数',
                'field' => 'wbm_store_out',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '批发退货数',
                'field' => 'wbm_return',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '调整数',
                'field' => 'adjust',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库总数',
                'field' => 'storage_out_num',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移入数',
                'field' => 'shift_in',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移出数',
                'field' => 'shift_out',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '零售发货数',
                'field' => 'sell_record',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '零售退货数',
                'field' => 'sell_return',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '期末库存<img height="23" width="23" src="assets/images/tip.png" title="期末=期初+入库-出库+调整" />',
                'field' => 'qm',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '价格',
                'field' => 'price',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '期末金额',
                'field' => 'qm_je',
                'width' => '100',
                'align' => '',
            ),
        ),
    ),
    'dataset' => 'prm/JxcReportModel::get_list_page', //get_page_data
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'report_jxc_list', 'name' => '商品进销存', 'export_type' => 'file'),
    'idField' => 'id',
    'customFieldTable'=>'rpt/report_jxc_do_list',
    'CheckSelection' => false,
));
?>

<script type="text/javascript">
//    $(function () {
//        $("#order_date_start").val('<?php //echo $order_date_start; ?>//');
//        $("#order_date_end").val('<?php //echo $order_date_end; ?>//');
//    });
    /*
     $(function(){
     $('#exprot_list').click(function(){
     var params;
     var url = tableStore.get('url');
     params = tableStore.get('params');
     params.ctl_type = 'export';
     if( show_mode == 'normal_mode'){
     params.ctl_export_conf = 'inv_list';
     }else{
     params.ctl_export_conf = 'inv_lof_list';
     }
     params.ctl_export_name =  '商品库存';
     var obj = searchFormForm.serializeToObject();
     for(var key in obj){
     params[key] =  obj[key];
     }
     for(var key in params){
     url +="&"+key+"="+params[key];
     }
     window.location.href = url;
     });*/


    $(document).ready(function () {
        $("#order_date_start").val('<?php echo $order_date_start; ?>');
        $("#order_date_end").val('<?php echo $order_date_end; ?>');
        rpt_count_all();//默认加载
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            rpt_count_all();
        });

        function rpt_count_all() {
            var obj = searchFormForm.serializeToObject();
            //汇总统计
            count_all(obj);
        }

        function count_all(obj) {
            $.post("?app_act=rpt/report_jxc/report_count", obj, function (data) {
                $("#qc_all").html(data.qc_all);
                $("#purchase_all").html(data.purchase_all);
                $("#pur_return_all").html(data.pur_return_all);
                $("#wbm_store_out_all").html(data.wbm_store_out_all);
                $("#wbm_return_all").html(data.wbm_return_all);

                $("#adjust_all").html(data.adjust_all);
                $("#shift_in_all").html(data.shift_in_all);
                $("#shift_out_all").html(data.shift_out_all);
                $("#sell_record_all").html(data.sell_record_all);
                $("#sell_return_all").html(data.sell_return_all);

                $("#storage_in_num_all").html(data.storage_in_num_all);
                $("#storage_out_num_all").html(data.storage_out_num_all);
                $("#qm_all").html(data.qm_all);
                $("#qc_je_all").html(data.qc_je_all);
                $("#qm_je_all").html(data.qm_je_all);
            }, "json");
        }
    });
</script>




