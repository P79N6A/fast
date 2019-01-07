<?php
$links = array();
$add_priv = load_model('sys/PrivilegeModel')->check_priv('stm/stock_adjust_record/detail&_type=entity');
if ($add_priv == TRUE) {
    $links[] = array('url' => 'stm/stock_adjust_record/detail&app_scene=add&shop_type=entity_shop', 'title' => '添加调整单', 'is_pop' => true, 'pop_size' => '500,550');
}
render_control('PageHead', 'head1', array('title' => '门店库存调整单',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['code_name'] = '商品编码';
$keyword_type['code_sku'] = '商品条形码';
$keyword_type['relation_code'] = '盘点单号';
$keyword_type = array_from_dict($keyword_type);
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
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '仓库',
            'type' => $response['login_type'] > 0 ? 'select' : 'select_multi',
            'id' => 'store_code',
            'data' => $response['store'],
        ),
        array(
            'label' => '类型',
            'type' => 'select_multi',
            'id' => 'adjust_type',
            'data' => $response['adjust_type'],
        ),
        array(
            'label' => '验收',
            'type' => 'select_multi',
            'id' => 'is_check_and_accept',
            'data' => array(
                array('1', '已验收'), array('0', '未验收')
            )
        ),
        array(
            'label' => '调整原因',
            'type' => 'input',
            'id' => 'remark'
        ),
        array(
            'label' => '下单日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'is_add_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'is_add_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
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
                'width' => '120',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view',
//                        'priv' => 'stm/stock_adjust_record/entity_view'
                    ),
                    array(
                        'id' => 'enable',
                        'title' => '验收',
                        'callback' => 'do_enable',
                        'show_cond' => 'obj.is_check_and_accept != 1',
//                        'priv' => 'stm/stock_adjust_record/do_entity_checkin&_type=entity'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.is_check_and_accept != 1',
                        'confirm' => '确认要删除此信息吗？',
//                        'priv' => 'stm/stock_adjust_record/do_delete&_type=entity'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '验收',
                'field' => 'is_check_and_accept',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '150',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({stock_adjust_record_id})">{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务时间',
                'field' => 'record_time',
                'width' => '100',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'num',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '调整类型',
                'field' => 'adjust_type_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '调整原因',
                'field' => 'remark',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'stm/StockAdjustRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'stock_adjust_record_id',
    'params' => array('filter' => array('is_entity_shop' => 'entity_shop')),
    'export' => array('id' => 'exprot_list', 'conf' => 'stock_adjust_record_entity_shop', 'name' => '门店库存调整单'),
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('stm/stock_adjust_record/do_delete'); ?>',
            data: {stock_adjust_record_id: row.stock_adjust_record_id},
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

    function do_enable(_index, row) {
        _do_set_check(_index, row, 'enable');
    }

    /**
     * 验收调整单
     * @param _index
     * @param row
     * @param active
     * @private
     */
    function _do_set_check(_index, row, active) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('stm/stock_adjust_record/do_entity_checkin'); ?>',
            data: {id: row.stock_adjust_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }


    /**
     * 查看调整单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.stock_adjust_record_id);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.stock_adjust_record_id);
    }


    function view(stock_adjust_record_id) {
        var url = '?app_act=stm/stock_adjust_record/entity_view&stock_adjust_record_id=' + stock_adjust_record_id;
        openPage(window.btoa(url), url, '调整单详情');
    }
</script>