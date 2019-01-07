<?php
render_control('PageHead', 'head1',
		array('title'=>"供应商类型",
				'links'=>array(
						array('url'=>'base/supplier_type/detail&app_scene=add', 'title'=>'添加供应商类型', 'is_pop'=>true, 'pop_size'=>'500,400'),
				),
				'ref_table'=>'table'
));?>


<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array (

    		array (
    				'label' => '名称/代码',
    				'type' => 'input',
    				'id' => 'code_name'
    		),
    )
) );
?>

<?php

render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '类型代码',
                'field' => 'supplier_type_code',
                'width' => '200',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '类型名称',
                'field' => 'supplier_type_name',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array (

                	array('id'=>'edit', 'title' => '编辑',
                		'act'=>'pop:base/supplier_type/detail&app_scene=edit', 'show_name'=>'编辑',
                		'show_cond'=>'obj.is_buildin != 1'),

                   array('id'=>'delete', 'title' => '删除', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),

                ),
            )
        )
    ),
    'dataset' => 'base/SupplierTypeModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'supplier_type_id',
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('base/supplier_type/do_delete');?>', data: {supplier_type_id: row.supplier_type_id},
    success: function(ret) {
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


