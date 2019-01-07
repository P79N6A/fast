<style>

</style>
<?php
//$is_power = load_model('sys/PrivilegeModel')->check_priv('fx/account/add');
$links = '';
//if ($is_power == true) {
////    $links = array(array('url' => 'fx/account/detail&app_scene=add', 'title' => '新增', 'is_pop' => true, 'pop_size' => '400,500'));
//}
render_control('PageHead', 'head1', array('title' => '收款统计  ',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit',
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => '出库月份',
            'type' => 'group',
            'field' => 'store_out_time',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'store_out_time_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'store_out_time_end', 'remark' => '', 'class' => 'input-small'),
            ),
        ),
        array(
            'label' => '单据编号',
            'type' => 'input',
            'id' => 'record_code',
            'title' => '',
        ),
        array(
            'label' => '付款状态',
            'title' => '',
            'type' => 'select',
            'id' => 'pay_status',
            'data' => ds_get_select_by_field('pay_status'),
        ),
        array(
            'label' => '分销商',
            'type' => 'select_pop',
            'id' => 'custom_code',
            'select' => 'base/custom_multi'
        ),
    )
));
?>
<div style="padding: 3px;">
    <table style="width: 85%">
        <tr>
            <td style="text-align: right; width: 150px;">待付金额：</td>
            <td><span id="pending_money_all"></span>元</td>

            <td style="text-align: right;">订单金额：</td>
            <td><span id="money_all"></span>元</td>

            <td style="text-align: right;">货款金额：</td>
            <td><span id="goods_money_all"></span>元</td>

            <td style="text-align: right;">运费金额：</td>
            <td><span id="express_money_all"></span>元</td>

            <td style="text-align: right;">已付金额：</td>
            <td><span id="pay_money_all"></span>元</td>
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
                    array('id' => 'view', 'title' => '查看明细', 'callback' => 'showDetail'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库月份',
                'field' => 'store_out_months',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商名称',
                'field' => 'custom_code_name',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '货款金额',
                'field' => 'goods_money',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '运费',
                'field' => 'sum_express_money',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单总金额',
                'field' => 'sum_money',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '待付总金额',
                'field' => 'sum_pending_money',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付总金额',
                'field' => 'sum_pay_money',
                'width' => '120',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'fx/CollectionStatisticModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'store_out_record_id',
    'init' => 'nodata',
    'export' => array('id' => 'exprot_list', 'conf' => 'fx_collection_statistic', 'name' => '收款统计', 'export_type' => 'file'),//
//    'CheckSelection' => true,
//    'events' => array(
//        'rowdblclick' => 'showDetail',
//    ),
));
?>
<script type="text/javascript">
    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        var url = '?app_act=fx/collection_statistic/view&custom_code=' + row.custom_code + '&store_out_months=' + row.store_out_months;
        openPage(window.btoa(url), url, '收款统计明细');
    }

    //汇总
    $(document).ready(function () {
//        $("#end_time_start").val("<?php //echo $end_time_start . '-' . '01';?>//");
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });

        function count_all(obj) {
            $.post("?app_act=fx/collection_statistic/report_count", obj, function (data) {
                $("#pending_money_all").html(data.pending_money_all);
                $("#money_all").html(data.money_all);
                $("#goods_money_all").html(data.goods_money_all);
                $("#express_money_all").html(data.express_money_all);
                $("#pay_money_all").html(data.pay_money_all);
            }, "json");
        }
    });

    BUI.use('bui/calendar', function (Calendar) {
        var dt = new Date();
        m = dt.getMonth();
        y = dt.getFullYear();
        var inputEl = $('#store_out_time_start'), monthpicker = new BUI.Calendar.MonthPicker({
            trigger: inputEl,
            autoHide: true,
            align: {
                points: ['bl', 'tl']
            },
            year: y,
            month: m,
            success: function () {
                var month = String(this.get('month') + 1),
                    year = this.get('year');
                var date = year + '-' + month;
                if (month.length < 2) {
                    date = year + '-0' + month;
                }
                inputEl.val(date);
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
    BUI.use('bui/calendar', function (Calendar) {
        var dt = new Date();
        m = dt.getMonth();
        y = dt.getFullYear();
        var inputEl = $('#store_out_time_end'), monthpicker = new BUI.Calendar.MonthPicker({
            trigger: inputEl,
            autoHide: true,
            align: {
                points: ['bl', 'tl']
            },
            year: y,
            month: m,
            success: function () {
                var month = String(this.get('month') + 1),
                    year = this.get('year');
                var date = year + '-' + month;
                if (month.length < 2) {
                    date = year + '-0' + month;
                }
                inputEl.val(date);
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
</script>




