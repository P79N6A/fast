<?php render_control('PageHead', 'head1',
    array('title' => '订单标签列表',
     'links' => array(
      array('url' => 'base/order_label/detail&app_scene=add', 'title' => '新增订单标签', 'is_pop' => true, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
    ));

?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array('label' => '标签代码',
            'title' => '标签代码',
            'type' => 'input',
            'id' => 'order_label_code',
        ),
        array('label' => '标签名称',
            'title' => '标签名称',
            'type' => 'input',
            'id' => 'order_label_name',
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
            array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:base/order_label/detail&app_scene=edit', 'show_name' => '编辑'),
			array(
                  'id' => 'delete',
                  'title' => '删除',
                  'callback' => 'do_delete',
                  'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？',
                  'show_cond' => 'obj.is_sys != 1'
                  ),        	
        )
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '标签代码',
        'field' => 'order_label_code',
        'width' => '100',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '标签名称',
        'field' => 'order_label_name',
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
    array('type' => 'text',
        'show' => 1,
        'title' => '系统内置',
        'field' => 'is_sys_html',
        'width' => '200',
        'align' => '',
    ),

)
),
    'dataset' => 'base/OrderLabelModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'order_label_id',
));

?>

<script type="text/javascript">

function do_delete(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('base/order_label/do_delete');
?>', data: {order_label_id: row.order_label_id},
    success: function(ret) {
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