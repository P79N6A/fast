<?php
render_control('PageHead', 'head1', array('title' => '库存同步策略',
    'links' => array(
//        array('url' => 'op/inv_sync/warn_goods', 'title' => '预警商品查询', 'is_pop' => false),
        array('url' => 'op/inv_sync/detail&app_scene=add', 'title' => '新增策略', 'is_pop' => false),
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
            'label' => '策略名称',
            'type' => 'input',
            'id' => 'sync_name'
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '状态',
            'type' => 'select',
            'id' => 'status',
            'data' => ds_get_select_by_field('clerkstatus', 1)
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
                        'act' => 'op/inv_sync/detail&app_scene=view&sync_code={sync_code}',
                        'show_name' => '查看策略',
                    ),
                    array(
                        'id' => 'edit',
                        'title' => '编辑',
                        'act' => 'op/inv_sync/detail&app_scene=edit&sync_code={sync_code}',
                        'show_name' => '编辑策略',
                        'show_cond' => 'obj.status != 1'
                    ),
                    array(
                        'id' => 'enable',
                        'title' => '启用',
                        'callback' => 'do_enable',
                        'show_cond' => 'obj.status != 1',
                        'confirm' => '确认要启用吗？'
                    ),
                    array(
                        'id' => 'disable',
                        'title' => '停用',
                        'callback' => 'do_disable',
                        'show_cond' => 'obj.status == 1',
                        'confirm' => '确认要停用吗？'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.status != 1 && obj.able_auth ==1',
                        'confirm' => '确认要删除吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '启用',
                'field' => 'status',
                'width' => '50',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '策略代码',
                'field' => 'sync_code',
                'width' => '100',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '策略名称',
                'field' => 'sync_name',
                'width' => '160',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '适用店铺',
                'field' => 'shop_name',
                'width' => '310',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供货仓',
                'field' => 'store_name',
                'width' => '310',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '启用在途数',
                'field' => 'is_road',
                'width' => '100',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '启用安全库存',
                'field' => 'is_safe',
                'width' => '100',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
        )
    ),
    'dataset' => 'op/InvSyncModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sync_id',
));
?>
    <div style="color: #ff3300;margin-left: 20px;position: relative;top: 50px;">温馨提示：使用库存同步策略，请配置所有店铺库存同步比例，否则未设置的店铺商品不会同步库存。<br/>
        必须保证策略有一个启用，否则以默认比例进行同步（系统店铺同步比例*店铺库存来源仓）！</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }

    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_active(_index, row, active) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('op/inv_sync/update_active'); ?>',
            data: {id: row.sync_id, active: active},
            success: function (ret) {
                if (ret.status == 1) {
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    }
    function do_ceshi(_index, row, active) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('op/inv_sync/ceshi'); ?>',
            data: {},
            success: function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Alert(ret.message, 'success');
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    }
    function do_delete(_index, row){
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('op/inv_sync/delete'); ?>',
            data: {sync_code:row.sync_code},
            success: function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Alert(ret.message, 'success');
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    }
    function do_edit(_index, row) {
        openPage('<?php echo base64_encode('?app_act=op/inv_sync/detail&app_scene=edit&sync_code=') ?>' + row.sync_code, '?app_act=op/inv_sync/detail&app_scene=edit&sync_code=' + row.sync_code, '编辑策略');
    }
</script>