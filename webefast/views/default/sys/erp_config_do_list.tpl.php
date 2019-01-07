<?php render_control('PageHead', 'head1',
    array('title' => 'ERP配置列表',
        'links' => array(
            array('url' => 'sys/erp_config/detail&app_scene=add', 'title' => '新增ERP配置'),
        ),
        'ref_table' => 'table'
    ));

?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
       array('label' => 'ERP配置名称',
            'title' => '',
            'type' => 'input',
            'id' => 'erp_config_name',
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
            array('id' => 'edit', 'title' => '编辑', 'act' => 'sys/erp_config/detail&app_scene=edit', 'show_name' => '编辑'),
            array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
            array('id' => 'delete_cache', 'title' => '一键清理缓存', 'callback' => 'do_delete_cache', 'confirm' => '确定要删除缓存数据吗？',),
        ),
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => 'ERP配置名称',
        'field' => 'erp_config_name',
        'width' => '200',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '对接方式',
        'field' => 'erp_type_name',
        'width' => '200',
        'align' => '',
    ),

)
),
    'dataset' => 'sys/ErpConfigModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'erp_config_id',
));

?>

<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/erp_config/do_delete');
?>', data: {id: row.erp_config_id},
    success: function(ret) {
    	var type = ret.status == 1 ? 'success' : 'error';
    	if (type == 'success') {
        BUI.Message.Alert('删除成功！', type);
        tableStore.load();
    	} else {
        BUI.Message.Alert(ret.message, type);
    	}
    }
	});
}

//清理缓存表
function do_delete_cache(_index, row) {
    $.ajax({
        type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('sys/erp_config/do_delete_cache');?>', data: {id: row.erp_config_id},
        success: function (ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert('删除成功！', type);
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
    url: '<?php echo get_app_url('sys/erp_config/update_active');

?>',
    data: {id: row.erp_config_id, type: active},
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

    parent.reload_parent_page = function(){
        tableStore.load();
    }

$(function(){
	$(".control-label").css("width","110px");
})

</script>