<?php echo load_js('comm_util.js') ?>
<?php
if ($response['status'] == 0) {
    $links = array(
        array('url' => 'crm/express_strategy/detail&app_scene=add', 'title' => '添加'),
        array('url' => 'crm/express_strategy/get_op_express_by_goods', 'title' => '商品指定快递'),
    );
} else {
    $links = array(
        array('url' => 'crm/express_strategy/detail&app_scene=add', 'title' => '添加'),
        array('url' => 'crm/express_strategy/get_op_express_by_remark', 'title' => '买家留言匹配'),
        array('url' => 'crm/express_strategy/get_op_express_by_goods', 'title' => '商品指定快递'),
    );
}
array_push($links, array('url' => 'crm/express_strategy/get_op_express_by_user', 'title' => '会员指定快递'));
render_control('PageHead', 'head1', array('title' => '订单快递适配策略',
    'links' => $links,
    'ref_table' => 'table'
));
?>


<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
			array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                	array('id'=>'edit', 'title' => '编辑',
                		'act'=>'crm/express_strategy/detail&app_scene=edit', 'show_name'=>'编辑',
                		'show_cond'=>'obj.status != 1'),
                     	array('id'=>'edit', 'title' => '查看',
                		'act'=>'crm/express_strategy/detail&app_scene=edit', 'show_name'=>'编辑',
                		'show_cond'=>'obj.status == 1'),
                	array(
	                  'id' => 'delete',
	                  'title' => '删除',
	                  'callback' => 'do_delete',
	                  'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？',
                            'show_cond'=>'obj.status != 1'
	                  ), 
		            array('id' => 'enable', 'title' => '启用',
		                'callback' => 'do_enable', 'show_cond' => 'obj.status != 1'),
		            array('id' => 'disable', 'title' => '停用',
		                'callback' => 'do_disable', 'show_cond' => 'obj.status == 1'),
                ),
            ),

            array (
                'type' => 'text',
                'show' => 1,
                'title' => '策略名称',
                'field' => 'policy_express_name',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '区域范围',
                'field' => 'area_range',
                'width' => '350',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<span title="{area_range_all}">{area_range}</span>',
                ),
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '可达快递',
                'field' => 'express_range',
                'width' => '300',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<span title="{express_range_all}">{express_range}</span>',
                ),
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '策略状态',
                'field' => 'status_html',
                'width' => '70',
                'align' => '',
            ),        
        )
    ),
    'dataset' => 'crm/ExpressStrategyModel::get_by_page',
    //'queryBy' => 'searchForm',
    'idField' => 'policy_express_id',
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) );
?>

<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('crm/express_strategy/do_delete');?>', data: {policy_express_id: row.policy_express_id},
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
    url: '<?php echo get_app_url('crm/express_strategy/update_active');

?>',
    data: {policy_express_id: row.policy_express_id, type: active},
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