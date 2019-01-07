<?php
render_control('PageHead', 'head1', array('title' => '网络订单应收统计',
    'ref_table' => 'table'
));
$cur_ym = date('Y-m', time());
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
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel',
            'data' => load_model('base/SaleChannelModel')->get_select()
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '账期',
            'type' => 'input',
            'id' => 'zq_month',
            'value' => $cur_ym
        )
    )
        )
);
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'view', 'title' => '查看', 'callback' => 'do_view'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sale_channel_name',
                'width' => '150',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '300',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '应收总金额',
                'field' => 'total_money',
                'width' => '180',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单总金额（邮费汇总）',
                'field' => 'order_money',
                'width' => '180',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单总金额（补差汇总）',
                'field' => 'return_money',
                'width' => '180',
                'align' => 'center'
            ),
        ),
    ),
    'dataset' => 'oms/RetailSettlementModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'params' => array(
        'filter' => array("zq_month" => $cur_ym)
    ),
));
?>

<span style="color:#F00">
    	友情提示：应收总金额=订单总金额-退单总金额
</span>&nbsp;&nbsp;
<script type="text/javascript">

    BUI.use('bui/calendar', function (Calendar) {
        var d = new Date();
        var year = d.getFullYear();
        var month = d.getMonth();
        var inputEl = $('#zq_month'),
                monthpicker = new BUI.Calendar.MonthPicker({
                    trigger: inputEl,
                    autoHide: true,
                    month: month,
                    align: {
                        points: ['bl', 'tl']
                    },
                    year: year,
                    success: function () {
                        var month = this.get('month'),
                                year = this.get('year');
                        inputEl.val(year + '-' + (month + 1));
                        this.hide();
                    }
                });
        monthpicker.render();
        monthpicker.on('show', function (ev) {
            var val = inputEl.val(),
                    arr, month, year;
            if (val) {
                arr = val.split('-');
                year = parseInt(arr[0]);
                month = parseInt(arr[1]);
                monthpicker.set('year', year);
                monthpicker.set('month', month - 1);
            }
        });
    });
    function do_view(_index, row) {
        view(row.sale_channel_code, row.shop_code);
    }
    function view(sale_channel_code, shop_code) {
        var month = $("#zq_month").val();
        var url = '?app_act=acc/retail_settlement_detail/do_list&source=' + sale_channel_code + '&shop_code=' + shop_code + '&month=' + month + "&type=view";
        openPage("do_list", url, "网络订单应收明细");
    }
</script>
