<?php
render_control('PageHead', 'head1', array('title' => '唯品会JIT仓库管理',
    'links' => array(
        array('url' => 'servicenter/weipinhuijit_warehouse/detail&app_scene=add', 'title' => '添加唯品会仓库', 'is_pop' => true, 'pop_size' => '500,400'),
        array('type' => 'js', 'js' => 'sync_client_environment()', 'title' => '同步客户环境'),
    ),
    'ref_table' => 'table'
));
?>

<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '仓库名称',
            'title' => '仓库名称',
            'type' => 'input',
            'id' => 'warehouse_name',
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            //array('type' => 'button',
            //    'show' => 1,
            //    'title' => '操作',
            //    'field' => '_operate',
            //    'width' => '110',
            //    'align' => '',
            //    'buttons' => array(
            //        array('id' => 'enable', 'title' => '启用',
            //            'callback' => 'do_enable', 'show_cond' => 'obj.is_active != 1'),
            //        array('id' => 'disable', 'title' => '停用',
            //            'callback' => 'do_disable', 'show_cond' => 'obj.is_active != 0', 'confirm' => '确定要停用仓库吗？'),
            //    ),
            //),
            //array(
            //    'type' => 'text',
            //    'show' => 1,
            //    'title' => '启用状态',
            //    'field' => 'status',
            //    'width' => '70',
            //    'align' => '',
            //    'format' => array('type' => 'map_checked'),
            //),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '序号',
                'field' => 'warehouse_no',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库编码',
                'field' => 'warehouse_code',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库名称',
                'field' => 'warehouse_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'desc',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'servicenter/WeipinhuijitWarehouseModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'warehouse_id',
    'CellEditing' => true,
));
?>
<input type="hidden" id="sel_shop_code"/>
<script type="text/javascript">

    function sync_client_environment() {
        var d = {'app_fmt': 'json'};
        $("#get").attr('disabled', true);
        $.post('<?php echo get_app_url('pubdata/pubdata_sync/sync_weipinhuijit_warehouse'); ?>', d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            $("#get").attr('disabled', false);
            tableStore.load();//刷新
        }, "json");
    }

</script>
