<style>
    #dz_month{width: 100px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '订单运费核销',
    'links' => array(
        array('url' => 'acc/order_express_detail/add&app_scene=add', 'title' => '新增快递对账单', 'is_pop' => TRUE, 'pop_size' => '500,530'),
    ),
    'ref_table' => 'table'
));


render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
//        array(
//            'label' => '导出',
//            'id' => 'exprot_list',
//        ),
    ),
    'fields' => array(
        array(
            'label' => '账期',
            'type' => 'input',
            'field' => 'month',
            'id' => 'dz_month'
        ),
    ),
));

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'view', 'title' => '查看', 'callback' => 'do_view'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '对账编号',
                'field' => 'dz_code',
                'width' => '200',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '220',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '账期',
                'field' => 'dz_month',
                'width' => '250',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统运费合计',
                'field' => 'express_cost',
                'width' => '120',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'create_time',
                'width' => '250',
                'align' => 'center'
            )
        ),
    ),
    'dataset' => 'acc/OrderExpressDzModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id'
));
?>
<script type="text/javascript">
   
    function do_view(_index, row) {
        view(row.dz_code);
    }

    function view(dz_code) {
        var url = '?app_act=acc/order_express_detail/view&dz_code=' + dz_code;
        openPage(window.btoa(url), url, '订单运费核销明细');
    }
   
   BUI.use('bui/calendar', function (Calendar) {
        var dt = new Date();
        m = dt.getMonth();
        y = dt.getFullYear();
        var inputEl = $('#dz_month'), monthpicker = new BUI.Calendar.MonthPicker({
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