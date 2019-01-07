<?php
render_control('PageHead', 'head1', array('title' => '奇门WMS配置',
    'links' => array(
        array('url' => 'servicenter/wms_qimen_config/detail&app_scene=add', 'title' => '新增配置', 'is_pop' => true, 'pop_size' => '500,400'),
        //array('type' => 'js', 'js' => 'sync_client_environment()', 'title' => '同步客户环境'),
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
            'label' => '客户名称',
            'type' => 'input',
            'id' => 'customer',
            'data' => array()
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '110',
                'align' => '',
                'buttons' => array(
                    //array('id' => 'enable', 'title' => '启用', 'callback' => 'do_enable', 'show_cond' => 'obj.is_active != 1'),
                    //array('id' => 'disable', 'title' => '停用', 'callback' => 'do_disable', 'show_cond' => 'obj.is_active != 0', 'confirm' => '确定要停用仓库吗？'),
                    //array('id' => 'disable', 'title' => '编辑', 'callback' => 'do_disable', 'show_cond' => 'obj.is_active != 0', 'confirm' => '确定要停用仓库吗？'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'show_cond' => '', 'confirm' => '确定要删除该配置吗？'),
                ),
            ),
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
                'title' => '客户名称',
                'field' => 'kh_id_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户ID',
                'field' => 'kh_id',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '奇门ID',
                'field' => 'qimen_id',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '添加人',
                'field' => 'add_person',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '添加时间',
                'field' => 'add_time',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'servicenter/WmsQimenModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'wms_config_id',
   // 'CellEditing' => true,
));
?>
<script type="text/javascript">

    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('servicenter/wms_qimen_config/do_delete'); ?>',
            data: {wms_config_id: row.wms_config_id},
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
</script>
