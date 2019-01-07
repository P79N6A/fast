<?php
render_control('PageHead', 'head1',
	array('title' => '快捷菜单设置',
		'ref_table' => 'table',
        )
);
$buttons = array(
	array(
		'label' => '查询',
		'id' => 'btn-search',
		'type' => 'submit',
	),
);
$fields =  array(
    array(
        'label' => '模块名称',
        'type' => 'input',
        'id' => 'cate_name',
        'title' => '支持模糊查询',
    ),
    array(
        'label' => '分组名称',
        'type' => 'input',
        'id' => 'group_name',
        'title' => '支持模糊查询',
    ),
    array(
        'label' => '菜单名称',
        'type' => 'input',
        'id' => 'action_name',
        'title' => '支持模糊查询',
    ),

);

render_control('SearchForm', 'searchForm', array(
	'buttons' => $buttons,
	'show_row' => 2,
	'fields' =>$fields,
));
render_control('DataTable', 'table', array(
	'conf' => array(
		'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array(
                    array('id'=>'enable', 'title' => '加入快捷菜单', 
                		'callback'=>'do_enable', 'show_cond'=>'obj.status != 1'),
                	array('id'=>'disable', 'title' => '移出快捷菜单', 
                		'callback'=>'do_disable', 'show_cond'=>'obj.status == 1', 
                		'confirm'=>'确认要停用快捷菜单 <b>[{action_name}]</b> 吗？'),
                ),
            ),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '模块名称',
				'field' => 'cate_name',
				'width' => '120',
				'align' => '',
			),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '分组名称',
				'field' => 'group_name',
				'width' => '120',
				'align' => '',
			),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '菜单名称',
				'field' => 'action_name',
				'width' => '160',
				'align' => '',
			),
		),
	),
	'dataset' => 'sys/ShortcutMenuModel::get_by_page',
	'queryBy' => 'searchForm',
	'idField' => 'shortcut_menu_table',
    'ColumnResize' => true,
));

?>
<script type="text/javascript">
function do_enable(_index, row) {
	_do_set_active(_index, row, 1);
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 0);
}
function _do_set_active(_index, row, active) {
    $.ajax({ type: 'POST', dataType: 'json',  
        url: '<?php echo get_app_url('sys/shortcut_menu/update_active');?>',
        data: {action_code: row.action_code, type: active}, 
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert('操作成功！', type);
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}  
</script>