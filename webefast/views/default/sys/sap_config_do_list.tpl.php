<?php
render_control('PageHead', 'head1', array('title' => 'SAP配置列表',
    'links' => array(
        array('url' => 'sys/sap_config/detail&app_scene=add', 'title' => '新增SAP配置'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array('label' => 'SAP配置名称',
            'title' => '',
            'type' => 'input',
            'id' => 'sap_config_name',
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '编辑', 'act' => 'sys/sap_config/detail&app_scene=edit', 'show_name' => '编辑'),
                    array('id' => 'delete', 'title' => '删除',
                        'callback' => 'do_delete',
                        'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
                ),
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '配置名称',
                'field' => 'sap_config_name',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'sys/SapConfigModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sap_config_id',
));
?>

<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/sap_config/do_delete');
?>', data: {id: row.sap_config_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>