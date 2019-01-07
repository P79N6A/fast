<?php
render_control('PageHead', 'head1', array(
    'title' => '拣货单模板列表',
    'links' => array(
        array(
            'url' => 'base/picking/detail&app_scene=add',
            'title' => '新增模板',
            'is_pop' => true,
            'pop_size' => '800,600'
        )
    ),
    'ref_table' => 'table'
));
?>

<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '模板名称',
            'title' => '模板名称/简介',
            'type' => 'input',
            'id' => 'keyword'
        )
    )
)
);
?>
<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '模板名称',
                'field' => 'picking_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '概述',
                'field' => 'picking_desc',
                'width' => '400',
                'align' => ''
            ),
            
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array(
                    
            
                    array(
                        'id' => 'enable',
                        'title' => '启用',
                        'callback' => 'do_enable',
                        'show_cond' => 'obj.ispublic != 1'
                    ),
                    array(
                        'id' => 'disable',
                        'title' => '停用',
                        'callback' => 'do_disable',
                        'show_cond' => 'obj.ispublic == 1',
                        'confirm' => '确认要停用此模板吗？'
                    ),
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'act' => 'pop:base/picking/detail&app_scene=view',
                        'show_name' => '查看模板',
                        'pop_size' => '800,600'
                    ),
                    array(
                        'id' => 'edit',
                        'title' => '编辑',
                        'act' => 'pop:base/picking/detail&app_scene=edit',
                        'show_name' => '编辑模板',
                        'pop_size' => '800,600'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'confirm' => '确认要删除此模板吗？'
                    )
                )
            )
            
        )
    )
    ,
    'dataset' => 'base/PickingModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id'
));
?>

<script type="text/javascript">

function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}

function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}

function do_delete(_index,row) {
	_do_delete(_index, row);
}

function _do_delete(_index, row){
	$.ajax({ type: 'POST', dataType: 'json',  
	    url: '<?php echo get_app_url('base/picking/do_delete');?>',
	    data: {id: row.id}, 
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
function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',  
    url: '<?php echo get_app_url('base/picking/update_active');?>',
    data: {id: row.id, type: active}, 
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
