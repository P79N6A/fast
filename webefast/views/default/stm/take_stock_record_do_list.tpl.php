<?php echo load_js("baison.js", true); ?>
<script>
    function do_sure(_index, row) {
        ajax_post({
            url: "?app_act=stm/take_stock_record/do_sure",
            data: {id: row.take_stock_record_id},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                if (data.status == "1") {
                    tableStore.load();
                }
            }
        })
    }
    function do_stop(_index, row) {
        ajax_post({
            url: "?app_act=stm/take_stock_record/do_stop",
            data: {id: row.take_stock_record_id},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                if (data.status == "1") {
                    tableStore.load();
                }
            }
        })
    }
    function do_delete(_index, row) {
        ajax_post({
            url: "?app_act=stm/take_stock_record/do_delete",
            data: {id: row.take_stock_record_id},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                if (data.status == "1") {
                    tableStore.load();
                }
            }
        })
    }
</script>
<?php
render_control('PageHead', 'head1', array('title' => '盘点单列表',
    'links' => array(
        array('url' => 'stm/take_stock_record/add&app_scene=add', 'title' => '添加盘点单', 'is_pop' => true, 'pop_size' => '500,550'),
        array('url' => 'stm/take_stock_record/profit_and_loss&app_scene=add', 'title' => '一键盘点', 'is_pop' => true, 'pop_size' => '800,550'),
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
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => '单据编号',
            'title' => '',
            'type' => 'input',
            'id' => 'record_code'
        ),
        array(
            'label' => '盘点仓库',
            'title' => '',
            'type' => 'select',
            'id' => 'store_code',
            'data' =>load_model('base/StoreModel')->get_select(2)
        ),
        array(
            'label' => '商品',
            'title' => '商品编码/商品名称',
            'type' => 'input',
            'id' => 'goods_name'
        ),
        array(
            'label' => '商品条形码',
            'title' => '商品条形码',
            'type' => 'input',
            'id' => 'barcord'
        ),
        array(
            'label' => '盘点日期',
            'type' => 'group',
            'field' => 'bill_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'bill_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'bill_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '确认',
            'title' => '',
            'type' => 'select',
            'id' => 'is_sure',
            'data' => array(
                array('', '请选择'), array('0', '未确认'), array('1', '已确认')
            )),
        array(
            'label' => '盘点',
            'title' => '',
            'type' => 'select',
            'id' => 'is_pre_profit_and_loss',
            'data' => array(array('', '请选择'), array('0', '未盘点'), array('1', '已盘点')
            )),
        array(
            'label' => '创建人',
            'type' => 'input',
            'id' => 'add_person'
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
                'width' => '200',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view'
                    ),
                    /* array(
                      'id' => 'enable',
                      'title' => '编辑',
                      'callback' => 'do_enable',
                      'show_cond' => 'obj.is_check_and_accept != 1'
                      ), */
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.is_sure != 1',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                    /*
                      array(
                      'id' => 'pre_profit',
                      'title' => '预盈亏',
                      'show_name'=>'预盈亏',
                      'show_cond' => 'obj.is_sure == 1',
                      'act'=>'pop:stm/take_stock_record/pre_profit&app_scene=view',
                      'pop_size'=>'850,500',
                      'is_pop'=>true
                      ), */
                    array(
                        'id' => 'sure',
                        'title' => '确认',
                        'callback' => 'do_sure',
                        'show_cond' => 'obj.is_sure != 1',
                    ),
                    array(
                        'id' => 'stop',
                        'title' => '终止',
                        'callback' => 'do_stop',
                        'show_cond' => 'obj.is_stop != 1 && obj.is_sure == 1 && obj.is_pre_profit_and_loss == 0',
                    ),
                    array(
                        'id' => 'view_record',
                        'title' => '查看未验收单',
                        'show_name' => '查看未验收单',
                        'show_cond' => 'obj.is_sure == 0',
                        'act' => 'stm/take_stock_record/view_unconfirmed&app_scene=view',
                        'is_pop' => false
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '确认',
                'field' => 'is_sure',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '盘点',
                'field' => 'is_pre_profit_and_loss',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '终止',
                'field' => 'is_stop',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '200',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
//                    'value' => '<a href="' . get_app_url('stm/take_stock_record/view') . '&_id={take_stock_record_id}">{record_code}</a>',
                    'value' => '<a href="javascript:view({take_stock_record_id})">{record_code}</a>',
                ),
            ),
            /*
              array(
              'type' => 'text',
              'show' => 1,
              'title' => '关联盈亏单',
              'field' => 'relation_code',
              'width' => '100',
              'align' => ''
              ), */
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '盘点日期',
                'field' => 'take_stock_time',
                'width' => '120',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code',
                'width' => '100',
                'align' => '',
                'phpfun' => 'get_store_name_by_code'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '盘点数量',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建人',
                'field' => 'is_add_person',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'stm/TakeStockRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'take_stock_record_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'take_stock_record_list', 'name' => '盘点单','export_type' => 'file'),
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<script>
    /**
     * 查看盘点单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.take_stock_record_id);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.take_stock_record_id);
    }
    function view(take_stock_record_id) {
        var url = '?app_act=stm/take_stock_record/view&_id=' + take_stock_record_id
        openPage(window.btoa(url), url, '盘点单详情');
    }

</script>