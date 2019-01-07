<?php
render_control('PageHead', 'head1', array('title' => '退货原因',
    'links' => array(
        array('url' => 'base/return_reason/detail&app_scene=add', 'title' => '新增退货原因', 'is_pop' => true, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array('label' => '代码',
            'title' => '退货原因代码',
            'type' => 'input',
            'id' => 'return_reason_code',
        ),
        array('label' => '名称',
            'title' => '退货原因名称',
            'type' => 'input',
            'id' => 'return_reason_name',
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
                'width' => '150',
                'align' => '',
                'buttons' => array(
//                    array('id' => 'enable', 'title' => '启用', 'callback' => 'do_enable', 'show_cond' => 'obj.is_active == 0'),
//                    array('id' => 'disable', 'title' => '停用', 'callback' => 'do_disable', 'show_cond' => 'obj.is_active == 1'),
                    array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:base/return_reason/detail&app_scene=edit', 'show_name' => '编辑','show_cond' => 'obj.is_sys == 1'),
                    array('id' => 'delete', 'title' => '删除',
                        'callback' => 'do_delete',
                        'confirm' => '确认要删除此退货原因吗？',
                        'show_cond' => 'obj.is_sys == 1'),
                )
            ),
			array('type' => 'text',
                'show' => 1,
                'title' => '退货原因代码',
                'field' => 'return_reason_code',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '退货原因名称',
                'field' => 'return_reason_name',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '退货原因类型',
                'field' => 'return_reason_type_txt',
                'width' => '100',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'remark',
                'width' => '200',
                'align' => '',
                'format' => array('type' => 'truncate',
                    'value' => 20,
                )
            ),
            
        )
    ),
    'dataset' => 'base/ReturnReasonModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'return_reason_id',
));
?>

<script type="text/javascript">
<!--
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/return_reason/do_delete'); ?>', data: {return_reason_id: row.return_reason_id},
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
//-->
    function do_enable(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/return_reason/active_switch'); ?>', data: {return_reason_id: row.return_reason_id, is_active: 1},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('启用成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
    function do_disable(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/return_reason/active_switch'); ?>', data: {return_reason_id: row.return_reason_id, is_active: 0},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('停用成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>