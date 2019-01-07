<?php render_control('PageHead', 'head1',
    array('title' => '销售平台',
        'links' => array(
            //array('url' => 'base/sale_channel/detail&app_scene=add', 'title' => '新增', 'is_pop' => true, 'pop_size' => '500,400'),
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
            'title' => '销售平台代码',
            'type' => 'input',
            'id' => 'sale_channel_code',
        ),
        array('label' => '名称',
            'title' => '销售平台名称',
            'type' => 'input',
            'id' => 'sale_channel_name',
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
            array('id' => 'enable', 'title' => '启用',
                'callback' => 'do_enable', 'show_cond' => 'obj.is_active != 1 && obj.is_system == 0'),
            array('id' => 'disable', 'title' => '停用',
                'callback' => 'do_disable', 'show_cond' => 'obj.is_active == 1 && obj.is_system == 0',
                'confirm' => '确认要停用此销售平台吗？'),

            array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:base/sale_channel/detail&app_scene=edit', 'show_name' => '编辑', 'show_cond' => 'obj.is_system == 0'),
            array('id' => 'delete', 'title' => '删除',
                'callback' => 'do_delete',
                'confirm' => '确认要删除此销售平台吗？', 'show_cond' => 'obj.is_system == 0'),
        )
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => '代码',
        'field' => 'sale_channel_code',
        'width' => '100',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '名称',
        'field' => 'sale_channel_name',
        'width' => '100',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '类型',
        'field' => 'is_system_txt',
        'width' => '120',
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
    'dataset' => 'base/SaleChannelModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sale_channel_id',
));

?>

<script type="text/javascript">
<!--

function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('base/sale_channel/update_active');?>',
    data: {sale_channel_id: row.sale_channel_id, type: active},
    success: function(ret) {
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

function do_delete(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('base/sale_channel/do_delete');
?>', data: {sale_channel_id: row.sale_channel_id},
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
//-->
</script>