<?php
$links = array(array('url' => 'base/store/detail&app_scene=add', 'is_pop' => true, 'pop_size' => '700,550', 'title' => '添加仓库'),);
if (load_model('sys/PrivilegeModel')->check_priv('base/store/detail#scene=add')) {
    render_control('PageHead', 'head1', array('title' => '仓库列表',
        'links' => $links,
        'ref_table' => 'table'
    ));
} else {
    render_control('PageHead', 'head1', array('title' => '仓库列表',
        'ref_table' => 'table'
    ));
}
?>


<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '仓库',
            'title' => '仓库名称/代码',
            'type' => 'input',
            'id' => 'code_name',
        ),
        array(
            'label' => '仓库类别',
            'type' => 'select_multi',
            'id' => 'store_type_code',
            'data' => load_model('base/StoreTypeModel')->get_list(1),
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
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'base/store/detail&app_scene=edit', 'show_name' => '编辑',
                        'show_cond' => 'obj.is_buildin != 1 && obj.store_property!=1', 'priv' => 'base/store/detail#scene=edit'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此信息吗？', 'priv' => 'base/store/delete', 'show_cond' => 'obj.store_property!=1  && obj.status!=1'),
                    array('id' => 'enable', 'title' => '启用',
                        'callback' => 'do_enable', 'show_cond' => 'obj.status != 1 && obj.store_property==0'),
                    array('id' => 'disable', 'title' => '停用',
                        'callback' => 'do_disable', 'show_cond' => 'obj.status == 1 && obj.store_property==0',
                        'confirm' => '<div style="text-align:center">确认要停用吗？<br><p style="color:red">仓库已存在订单数据，停用后将无法查询到对应仓库的数据！</p></div>'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库代码',
                'field' => 'store_code',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库名称',
                'field' => 'store_name',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库类别',
                'field' => 'store_type_code_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '缺货商品允许发货',
                'field' => 'allow_negative_inv',
                'width' => '120',
                'align' => '',
                'format_js' => array('type' => 'map_checked'),
            )
        )
    ),
    'dataset' => 'base/StoreModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'store_id',
        //'RowNumber'=>true,
        // 'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/store/do_delete'); ?>', data: {store_id: row.store_id},
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
        _do_set_active(_index, row, 'enable');
    }
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_active(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/store/update_active'); ?>',
            data: {id: row.store_id, type: active},
            success: function (ret) {
                var _type = ret.status == 1 ? 'success' : 'error';
                BUI.Message.Show({
                    msg: ret.message,
                    width:400,
                    icon: _type,
                    buttons: [],
                    autoHide: true
                });
                if (_type == 'success') {
                    tableStore.load();
                }
            }
        });
    }
</script>
