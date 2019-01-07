<style type="text/css">
    .well {
        min-height: 40px;
    }
    #start_time,#end_time {
        width: 110px;
    }
</style>

<?php
render_control('PageHead', 'head1', array('title' => '活动列表',
    'links' => array(
        array('url' => 'crm/activity/view&app_scene=add', 'title' => '添加活动', 'is_pop' => false, 'pop_size' => '500,550'),
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
            'label' => '导出明细',
            'id' => 'exprot_detail',
        ),
    ),
    'fields' => array(
        array(
            'label' => '活动编码/名称',
            'title' => '',
            'type' => 'input',
            'id' => 'code_name'
        ),
        array(
            'label' => '活动时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'start_time', 'value' => $response['start_time'],),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'end_time', 'remark' => ''),
            )
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '商品',
            'title' => '编码/条形码',
            'type' => 'input',
            'id' => 'goods_code'
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
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '查看',
                        'act' => 'crm/activity/view&app_scene=edit',
                        'show_name' => '查看',
                    ),
                    array('id' => 'enable',
                        'title' => '启用',
                        'callback' => 'do_enable',
                        'show_cond' => 'obj.status != 1 && obj.is_first == 0'
                    ),
                    array('id' => 'disable',
                        'title' => '停用',
                        'callback' => 'do_disable',
                        'show_cond' => 'obj.status == 1'
                    ),
                    array('id' => 'send_again',
                        'title' => '库存同步',
                        'callback' => 'sync',
                        'show_cond' => 'obj.status == 1'
                    ),
                    array('id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.status != 1 && obj.is_del == 1'
                    ),
                    array('id' => 'copy',
                        'title' => '复制',
                        'callback' => 'do_copy',
                        'show_cond' => 'obj.status != 1 && obj.is_copy == 1'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动编码',
                'field' => 'activity_code',
                'width' => '130',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动名称',
                'field' => 'activity_name',
                'width' => '160',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动开始时间',
                'field' => 'start_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动结束时间',
                'field' => 'end_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动店铺',
                'field' => 'shop_name',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '同步库存时间',
                'field' => 'update_inv_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库存锁定单',
                'field' => 'stock_lock_record',
                'width' => '150',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({stock_lock_record_id})">{stock_lock_record}</a>',
                )
            ),
        )
    ),
    'dataset' => 'crm/ActivityModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'activity_id',
    'export' => array('id' => 'exprot_detail', 'conf' => 'activity_report_do_list', 'name' => '活动列表详情', 'export_type' => 'file'),
    'init' => 'nodata'
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    $('#code_name').parent().prev().css("width", "90px");

    function do_enable(_index, row) {
        BUI.Message.Confirm('活动启用后不允许修改，请确认，确认启用活动吗？', function () {
            _do_set_active(_index, row, 'enable');

        }, 'question');
    }
    function do_disable(_index, row) {
        BUI.Message.Confirm('确认停用活动吗？', function () {
            _do_set_active(_index, row, 'disable');

        }, 'question');
    }
    function do_delete(_index, row) {
        BUI.Message.Confirm('确认删除活动吗？', function () {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('crm/activity/delete_activity'); ?>',
                data: {code: row.activity_code, shop: row.shop_code},
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
        }, 'question');
    }
    function do_copy(_index, row) {
        BUI.Message.Confirm('确认复制活动吗？', function () {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('crm/activity/copy_activity'); ?>',
                data: {code: row.activity_code, shop: row.shop_code},
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
        }, 'question');
    }

    var fullMask;
    function loadTip(tip) {
        BUI.use(['bui/mask'], function (Mask) {
            fullMask = new Mask.LoadMask({
                el: 'body',
                msg: tip
            });
        });
    }

    function _do_set_active(_index, row, active) {
        loadTip('正在启用，请勿操作...');
        fullMask.show();
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('crm/activity/update_active'); ?>',
            data: {id: row.activity_id, type: active},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                var msg, btn_ok, set_type;
                fullMask.hide();
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    if (ret.status == -5) {
                        BUI.Message.Confirm(ret.message + '确认启用活动吗？', function () {
                            _do_set_active(_index, row, 'check');
                        }, 'question');
                    } else if (ret.status == -4) {
                        BUI.Message.Confirm(ret.message + '确认启用活动吗？', function () {
                            _do_set_active(_index, row, 'is_null');
                        }, 'question');
                    } else if (ret.status == -3) {
                        msg = '为保证店铺以库存锁定单数量来同步，请为店铺设置并启用库存同步策略';
                        btn_ok = '前往设置';
                        set_type = 1;
                    } else if (ret.status == -2) {
                        msg = '为保证店铺以库存锁定单数量来同步，请开启并设置库存同步策略';
                        btn_ok = '前往开启';
                        set_type = 2;
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
                if (ret.status == -3 || ret.status == -2) {
                    BUI.Message.Show({title: '友情提示', msg: msg, icon: 'warning', buttons: [
                            {text: btn_ok, elCls: 'button button-primary', handler: function () {
                                    open_inv_sync_page(set_type);
                                    this.close();
                                }
                            },
                            {text: '考虑一下', elCls: 'button', handler: function () {
                                    this.close();
                                }
                            }
                        ]
                    });
                }
            }
        });
    }
    function open_inv_sync_page(_type) {
        if (_type == 1) {
            openPage('<?php echo base64_encode('?app_act=op/inv_sync/do_list') ?>', '?app_act=op/inv_sync/do_list', '库存同步策略');
        } else {
            openPage('<?php echo base64_encode('?app_act=sys/params/do_list&page_no=op') ?>', '?app_act=sys/params/do_list&page_no=op', '系统参数设置');
        }
    }

    function sync(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('crm/activity/sync_inv'); ?>',
            data: {code: row.activity_code, shop: row.shop_code},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    //BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    function view(stock_adjust_record_id) {
        var url = '?app_act=stm/stock_lock_record/view&stock_lock_record_id=' + stock_adjust_record_id
        openPage(window.btoa(url), url, '锁定单详情');
    }
</script>




