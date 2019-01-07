<?php render_control('PageHead', 'head1',
    array('title' => '短信供应商列表',
        'links' => array(
            array('url' => 'sys/sms_supplier/detail&app_scene=add', 'title' => '添加供应商', 'is_pop' => true, 'pop_size' => '600,450'),
        ),
        'ref_table' => 'table'
    ));

?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array('label' => '供应商代码',
            'title' => '供应商代码',
            'type' => 'input',
            'id' => 'supplier_code',
        ),
        array('label' => '供应商名称',
            'title' => '供应商名称',
            'type' => 'input',
            'id' => 'supplier_name',
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
                'callback' => 'do_enable', 'show_cond' => 'obj.is_active != 1'),
            array('id' => 'disable', 'title' => '停用',
                'callback' => 'do_disable', 'show_cond' => 'obj.is_active == 1'),
            array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:sys/sms_supplier/detail&app_scene=edit', 'show_name' => '编辑'),
            array('id' => 'delete', 'title' => '删除',
                'callback' => 'do_delete',
                'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
        ),
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => '供应商代码',
        'field' => 'supplier_code',
        'width' => '150',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '供应商名称',
        'field' => 'supplier_name',
        'width' => '150',
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
    'dataset' => 'sys/SmsSupplierModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'supplier_id',
));

?>

<script type="text/javascript">

function do_delete(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/sms_supplier/do_delete');

?>', data: {supplier_id: row.supplier_id},
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

function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/sms_supplier/update_active');

?>',
    data: {supplier_id: row.supplier_id, type: active},
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

</script>