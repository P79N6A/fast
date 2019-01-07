<?php render_control('PageHead', 'head1',
    array('title' => '短信模板列表',
        'links' => array(
            array('url' => 'sys/sms_tpl/detail&app_scene=add', 'title' => '新增短信模板', 'is_pop' => true, 'pop_size' => '600,450'),
        ),
        'ref_table' => 'table'
    ));

?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array('label' => '短信模版类型',
            'title' => '',
            'type' => 'select',
            'id' => 'tpl_type',
        	'data'=>array(
	    array('确认订单短信通知','确认订单短信通知'),array('通知配货短信通知','通知配货短信通知'),array('发货成功短信通知','发货成功短信通知'),
	    array('客户确认收货短信通知','客户确认收货短信通知'),array('派件短信通知','派件短信通知'),array('签收短信通知','签收短信通知'),
	    array('会员群发短信模板','会员群发短信模板'),array('','请选择')
	    )),
       array('label' => '短信模版名称',
            'title' => '',
            'type' => 'input',
            'id' => 'tpl_name',
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
            array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:sys/sms_tpl/detail&app_scene=edit', 'show_name' => '编辑'),
            array('id' => 'delete', 'title' => '删除',
                'callback' => 'do_delete',
                'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
        ),
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => '短信模版类型',
        'field' => 'tpl_type',
        'width' => '200',
        'align' => '',
    ),
	array('type' => 'text',
        'show' => 1,
        'title' => '短信模版名称',
        'field' => 'tpl_name',
        'width' => '200',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '模版内容',
        'field' => 'sms_info',
        'width' => '200',
        'align' => '',
        'format' => array('type' => 'truncate',
            'value' => 20,
        )
    ),
    
)
),
    'dataset' => 'sys/SmsTplModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
));

?>

<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/sms_tpl/do_delete');
?>', data: {id: row.id},
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


function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/sms_tpl/update_active');

?>',
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


$(function(){
	$(".control-label").css("width","110px");
})

</script>