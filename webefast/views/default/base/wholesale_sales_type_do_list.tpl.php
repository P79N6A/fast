<?php
render_control('PageHead', 'head1', array('title' => '库存调整类型',
    'links' => array(
        array('url' => 'base/wholesale_sales_type/detail&app_scene=add', 'title' => '添加批发销货类型', 'is_pop' => true, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '类型',
            'title' => '类型名称/代码',
            'type' => 'input',
            'id' => 'record_type_code',
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'edit',
                        'title' => '编辑',
                        'act' => 'pop:base/wholesale_sales_type/detail&app_scene=edit',
                        'show_name' => '编辑',
                        'show_cond' => 'obj.sys == 0',
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'confirm' => '确认要删除此批发销货类型吗？',
                        'show_cond' => 'obj.sys == 0',
                    ),
                )
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '类型代码',
                'field' => 'record_type_code',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '类型名称',
                'field' => 'record_type_name',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '200',
                'align' => '',
                'format' => array('type' => 'truncate',
                    'value' => 20,
                )
            ),
        )
    ),
    'dataset' => 'base/WholesaleSalesTypeModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'record_type_id',
));
?>

<script type="text/javascript">
<!--
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST', 
            dataType: 'json',
            url: '<?php echo get_app_url('base/wholesale_sales_type/do_delete');?>', 
            data: {record_type_id: row.record_type_id},
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
</script>