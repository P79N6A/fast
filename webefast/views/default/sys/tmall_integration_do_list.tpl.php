<?php
render_control('PageHead', 'head1', array('title' => '天猫积分抵扣',
    'ref_table' => 'table'
));
?>
<?php
$end_time_start = date('Y-m',strtotime("-1 month"));
$end_time_end = date('Y-m-t', strtotime('-1 month'));
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
    'show_row' => 3,
    'fields' => array(
        array(
            'label' => '交易结束时间',
            'type' => 'group',
            'field' => 'end_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'end_time_start'),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'end_time_end', 'remark' => ''),
            ),
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop_tianmao(),//淘宝平台，天猫店铺
        ),
        array(
            'label' => '会员昵称',
            'type' => 'input',
            'id' => 'buyer_nick',
            'title' => '',
        ),
    ),
));
?>
<div style="padding: 3px;">
    <table style="width: 85%">
        <tr>
            <td style="text-align: right; width: 150px;">应收金额总计：</td>
            <td><span id="payment_all"></span>元</td>

            <td style="text-align: right;">邮费总计：</td>
            <td><span id="post_fee_all"></span>元</td>

            <td style="text-align: right;">积分抵扣金额：</td>
            <td><span id="real_point_fee_money_all"></span>元</td>
        </tr>
    </table>
</div>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'tid',
                'width' => '160',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '180',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '会员昵称',
                'field' => 'buyer_nick',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易结束时间',
                'field' => 'end_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单应收款',
                'field' => 'payment',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '邮费',
                'field' => 'post_fee',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '积分抵扣金额(元)',
                'field' => 'real_point_fee_money',
                'width' => '120',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'rpt/TmallIntegrationModel::get_by_list_page',
    'queryBy' => 'searchForm',
    'init' => 'nodata',
    'export' => array('id' => 'exprot_list', 'conf' => 'rpt_tmall_integration_list', 'name' => '天猫积分抵扣', 'export_type' => 'file'), //
//    'params' => array('filter' => array('current_time' => $current_time)),
));
?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#end_time_start").val("<?php echo $end_time_start . '-' . '01';?>");
        $("#end_time_end").val("<?php echo $end_time_end;?>");
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });

        function count_all(obj) {
            $.post("?app_act=sys/tmall_integration/report_count", obj, function (data) {
                $("#payment_all").html(data.payment_all);
                $("#post_fee_all").html(data.post_fee_all);
                $("#real_point_fee_money_all").html(data.real_point_fee_money_all);
            }, "json");
        }
    });
</script>




