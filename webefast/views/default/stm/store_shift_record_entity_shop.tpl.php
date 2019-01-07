<style type="text/css">
    #is_add_time_start{width:100px;}
    #is_add_time_end{width:100px;}
</style>
<?php
$links = array();
$add_priv = load_model('sys/PrivilegeModel')->check_priv('stm/store_shift_record/entity_shop_detail&_type=entity');
if ($add_priv == TRUE) {
    $links[] = array('url' => 'stm/store_shift_record/entity_shop_detail&app_scene=add', 'title' => '添加调拨单', 'is_pop' => true, 'pop_size' => '500,550');
}
render_control('PageHead', 'head1', array('title' => '门店库存调拨单',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_barcode'] = '商品条形码';
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
            'label' => '移出仓',
            'type' => 'select_multi',
            'id' => 'shift_out_store_code',
            'data' => load_model('base/StoreModel')->get_entity_store_select(),
        ),
        array(
            'label' => '移入仓',
            'type' => 'select_multi',
            'id' => 'shift_in_store_code',
            'data' => load_model('base/StoreModel')->get_entity_store_select(),
        ),
        array(
            'label' => '移出状态',
            'type' => 'select_multi',
            'id' => 'is_shift_out',
            'data' => array(
                array('0', '未出库'),
                array('1', '已出库')
            )
        ),
        array(
            'label' => '移入状态',
            'type' => 'select_multi',
            'id' => 'is_shift_in',
            'data' => array(
                array('0', '未入库'),
                array('1', '已入库')
            )
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'is_add_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'is_add_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '移出日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'is_shift_out_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'is_shift_out_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '移入日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'is_shift_in_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'is_shift_in_time_end', 'remark' => ''),
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
                'width' => '110',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view',
                    ),
                    array(
                        'id' => 'check1',
                        'title' => '确认',
                        'callback' => 'do_sure',
                        'show_cond' => 'obj.is_sure == 0',
                        'priv' => 'stm/store_shift_record/do_sure&_type=entity'
                    ),
                    array(
                        'id' => 'check2',
                        'title' => '取消确认',
                        'callback' => 'do_re_sure',
                        'show_cond' => 'obj.is_shift_out != 1 && obj.is_sure == 1 ',
                        'priv' => 'stm/store_shift_record/do_sure&_type=entity'
                    ),
                    array(
                        'id' => 'enable',
                        'title' => '出库',
                        'callback' => 'do_shift_out',
                        'show_cond' => 'obj.is_shift_out != 1 && obj.is_sure == 1',
                        'priv' => 'stm/store_shift_record/do_shift_out&_type=entity'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.is_sure != 1',
                        'confirm' => '确认要删除此信息吗？',
                        'priv' => 'stm/store_shift_record/do_delete&_type=entity'
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
                'title' => '出库',
                'field' => 'is_shift_out',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '入库',
                'field' => 'is_shift_in',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({shift_record_id})">{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'is_add_time',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移出时间',
                'field' => 'is_shift_out_time',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移入时间',
                'field' => 'is_shift_in_time',
                'width' => '100',
                'align' => '',
//                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移出仓库',
                'field' => 'shift_out_store_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移入仓库',
                'field' => 'shift_in_store_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '移出数量',
                'field' => 'out_num',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '差异',
                'field' => 'diff_num',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总金额',
                'field' => 'out_money',
                'width' => '70',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'stm/StoreShiftRecordModel::get_entity_shop_list',
    'queryBy' => 'searchForm',
    'idField' => 'shift_record_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'stock_shift_record_entity_shop', 'name' => '门店库存调拨单'),
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    //删除
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('stm/store_shift_record/do_delete'); ?>',
            data: {shift_record_id: row.shift_record_id},
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
    //确认
    function  do_sure(_index, row) {
        url = '?app_act=stm/store_shift_record/do_sure';
        data = {id: row.shift_record_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
    //取消确认
    function  do_re_sure(_index, row) {
        url = '?app_act=stm/store_shift_record/do_sure';
        data = {id: row.shift_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    //出库
    function do_shift_out(_index, row) {
        url = '?app_act=stm/store_shift_record/do_shift_out';
        data = {id: row.shift_record_id};
        _do_operate(url, data, 'table');
    }

    /**
     * 查看调拨单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.shift_record_id);
    }
    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.shift_record_id);
    }
    function view(shift_record_id) {
        var url = '?app_act=stm/store_shift_record/entity_shop_view&is_entity=1&shift_record_id=' + shift_record_id;
        openPage(window.btoa(url), url, '调拨单详情');
    }

    function alert_msg(_status) {
        if (_status == 1) {
            BUI.Message.Show({
                msg: '操作成功',
                icon: 'success',
                buttons: [],
                autoHide: true,
                autoHideDelay: 1000
            });
        } else {
            BUI.Message.Show({
                msg: '操作失败 ',
                icon: 'error',
                buttons: [],
                autoHide: true,
                autoHideDelay: 1000
            });
        }
    }
</script>