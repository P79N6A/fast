
<?php
render_control('PageHead', 'head1', array('title' => '商品成本月结单列表',
    'links' => array(
        array('url' => 'acc/cost_month/detail&app_scene=add', 'title' => '新建成本月结单', 'is_pop' => true, 'pop_size' => '450,450'),
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
            'label' => '月结月份',
            'title' => '',
            'type' => 'text',
            'id' => 'ymonth',
        ),
        array(
            'label' => '审核状态',
            'title' => '',
            'type' => 'select',
            'id' => 'is_check',
            'data' => ds_get_select_by_field('cost_month_check', 1)
        ),
        array(
            'label' => '月结单号',
            'title' => '',
            'type' => 'text',
            'id' => 'record_code'
        ),
    )
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '140',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'enable',
                        'title' => '确认',
                        'callback' => 'do_sure',
                        'show_cond' => 'obj.is_sure != 1'
                    ),
                    array(
                        'id' => 'cancel',
                        'title' => '取消确认',
                        'callback' => 'do_cancel_sure',
                        'show_cond' => 'obj.is_sure == 1 && obj.is_check!=1'
                    ),
                    array(
                        'id' => 'check',
                        'title' => '审核',
                        'callback' => 'do_check',
                        'show_cond' => 'obj.is_sure ==1 && obj.is_check != 1',
                        'confirm' => '审核成功后不可删除，请确认！'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.is_sure != 1 && obj.is_check !=1',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '确认',
                'field' => 'is_sure',
                'width' => '60',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '审核',
                'field' => 'is_check',
                'width' => '60',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '月结单号',
                'field' => 'record_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '月结月份',
                'field' => 'ymonth',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '汇总仓库',
                'field' => 'store_name',
                'width' => '300',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'record_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '120',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'acc/CostMonthModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'cost_month_id',
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    //删除月结单
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('acc/cost_month/do_delete'); ?>',
            data: {cost_month_id: row.cost_month_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    //月结单确认
    function  do_sure(_index, row) {
        url = '?app_act=acc/cost_month/update_sure';
        data = {id: row.cost_month_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }
    //月结单取消
    function  do_cancel_sure(_index, row) {
        url = '?app_act=acc/cost_month/update_sure';
        data = {id: row.cost_month_id, type: 'disable'};
        _do_operate(url, data, 'flush');
    }
    //月结单审核
    function  do_check(_index, row) {
        url = '?app_act=acc/cost_month/update_check';
        data = {id: row.cost_month_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }

    //查看月结单详情
    function do_view(_index, row) {
        view(row.record_code);
    }

    function view(record_code) {
        var url = '?app_act=acc/cost_month/view&record_code=' + record_code;
        openPage(window.btoa(url), url, '成本月结单详情');
    }

    BUI.use('bui/calendar', function (Calendar) {
        var dt = new Date();
        m = dt.getMonth();
        y = dt.getFullYear();
        var inputEl = $('#ymonth'), monthpicker = new BUI.Calendar.MonthPicker({
            trigger: inputEl,
            // month:1, //月份从0开始，11结束
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
                inputEl.val(date);//月份从0开始，11结束
                this.hide();
            }
        });
        monthpicker.render();
        monthpicker.on('show', function (ev) {
            var val = inputEl.val(),
                    arr, month, year;
            if (val) {
                arr = val.split('-'); //分割年月
                year = parseInt(arr[0]);
                month = parseInt(arr[1]);
                monthpicker.set('year', year);
                monthpicker.set('month', month - 1);
            }
        });
    });
</script>