<?php
$links = array(array('url' => 'base/store_type/detail&app_scene=add', 'is_pop' => true, 'title' => '添加仓库类别'),);
if (load_model('sys/PrivilegeModel')->check_priv('base/store/detail#scene=add')) {
    render_control('PageHead', 'head1', array('title' => '仓库类别',
        'links' => $links,
        'ref_table' => 'table'
    ));
} else {
    render_control('PageHead', 'head1', array('title' => '仓库类别',
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
            'label' => '仓库类别',
            'title' => '仓库类别名称/代码',
            'type' => 'input',
            'id' => 'code_name',
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
                        'act' => 'base/store_type/detail&app_scene=edit', 'show_name' => '编辑','priv' => 'base/store_type/detail#scene=edit'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此信息吗？','priv' => 'base/store_type/delete'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '类别代码',
                'field' => 'type_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '类别名称',
                'field' => 'type_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '300',
            )
        )
    ),
    'dataset' => 'base/StoreTypeModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
        //'RowNumber'=>true,
        // 'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/store_type/do_delete'); ?>', data: {type_code: row.type_code},
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

</script>
