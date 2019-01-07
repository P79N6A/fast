<?php
render_control('PageHead', 'head1', array('title' => "供应商",
    'links' => array(
        array('url' => 'base/supplier/detail&app_scene=add', 'title' => '添加供应商', 'is_pop' => false, 'pop_size' => '600,600'),
    ),
    'ref_table' => 'table'
));
?>


<?php
$buttons = array(
    array(
    'label' => '查询',
     'id' => 'btn-search',
     'type' => 'submit',
     ),
);
if (load_model('sys/PrivilegeModel')->check_priv('base/supplier/exprot')) {
    $buttons[] = array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
}
render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'fields' => array(
        array(
            'label' => '名称/代码',
            'type' => 'input',
            'id' => 'code_name'
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
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'base/supplier/detail&app_scene=edit', 'show_name' => '编辑供应商',
                        'show_cond' => 'obj.is_buildin != 1'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除 [<b>{supplier_name}</b>] 的信息吗？'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商代码',
                'field' => 'supplier_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商名称',
                'field' => 'supplier_name',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '折扣',
                'field' => 'rebate',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '联系人',
                'field' => 'contact_person',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机号',
                'field' => 'mobile',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '联系电话',
                'field' => 'tel',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '联系地址',
                'field' => 'addr',
                'width' => '400',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'base/SupplierModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'supplier_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'supplier_do_list', 'name' => '供应商列表','export_type'=>'file'),
));
?>
<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/supplier/do_delete'); ?>', data: {supplier_id: row.supplier_id},
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



