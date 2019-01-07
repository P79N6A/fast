<?php render_control('PageHead', 'head1',
    array('title' => '订单问题原因列表',
        'links' => array(
            array('url' => 'base/order_problem_reason/detail&app_scene=add', 'title' => '新增订单问题原因', 'is_pop' => true, 'pop_size' => '500,400'),
        ),
        'ref_table' => 'table'
    ));

?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array('label' => '问题原因代码',
            'title' => '问题原因代码',
            'type' => 'input',
            'id' => 'problem_reason_code',
        ),
        array('label' => '问题原因名称',
            'title' => '问题原因名称',
            'type' => 'input',
            'id' => 'problem_reason_name',
        ),
    )
));

?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
    array('type' => 'text',
        'show' => 1,
        'title' => '问题原因代码',
        'field' => 'problem_reason_code',
        'width' => '200',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '问题原因名称',
        'field' => 'problem_reason_name',
        'width' => '200',
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
    array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '300',
        'align' => '',
        'buttons' => array(
            array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:base/order_problem_reason/detail&app_scene=edit', 'show_name' => '编辑'),
            array('id' => 'delete', 'title' => '删除',
                'callback' => 'do_delete',
                'confirm' => '确认要删除此问题原因原因吗？'),
        )
    ),
)
),
    'dataset' => 'base/OrderProblemReasonModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'problem_reason_id',
));

?>

<script type="text/javascript">
<!--
function do_delete(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('base/order_problem_reason/do_delete');
?>', data: {problem_reason_id: row.problem_reason_id},
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