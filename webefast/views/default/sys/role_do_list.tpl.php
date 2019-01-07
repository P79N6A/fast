<?php render_control('PageHead', 'head1',
array('title'=>'角色列表',
	'links'=>array(
		 array('url'=>'sys/role/detail&app_scene=add', 'title'=>'新增角色', 'is_pop'=>true, 'pop_size'=>'800,400'),
	),
	'ref_table'=>'table'
));?>
<?php
render_control ( 'SearchForm', 'searchForm', array ('col'=>3,
		'cmd' => array (
				'label' => '查询',
				'id' => 'btn-search' 
		),
		'fields' => array (
				array (
						'label' => '关键字',
						'title' => '代码/名称',
						'type' => 'input',
						'id' => 'keyword' 
				),
				array (
						'label' => '内置角色',
						'type' => 'select',
						'id' => 'is_buildin',
						'data' => array_from_dict ( array (
								'' => '请选择',
								'1' => '是',
								'0' => '否' 
						) ) 
				) 
		) 
) );
?>
<?php
$check = load_model('sys/PrivilegeModel')->check_priv('sys/role_profession/do_list&app_scene=edit', 1);
$check_delete = load_model('sys/PrivilegeModel')->check_priv('sys/role/do_delete');
$security_role_id = load_model('sys/EfastPrivilegeModel')->get_security_role_id();

$buttons = array(
    //array('id'=>'audit', 'title' => '审核', ),
    array('id' => 'view', 'title' => '查看', 'act' => 'pop:sys/role/detail&app_scene=view', 'show_name' => '查看角色'),
    array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:sys/role/detail&app_scene=edit', 'show_name' => '编辑角色', 'show_cond' => 'obj.is_buildin != 1'),
    array('id' => 'copy', 'title' => '复制', 'act'=>'pop:sys/role/detail&app_scene=add&opt=copy', 'show_name' => '复制角色', 'show_cond' => 'obj.role_id != 1 && obj.role_id != 100 && obj.role_id != '.$security_role_id),);
if ($check_delete === true) {
    $buttons[] = array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'show_name' => '删除', 'show_cond' => "obj.role_id != 1 && obj.role_id != 100 && obj.role_id != '{$security_role_id}'",'confirm' => '确认要删除此角色吗？');
}
$buttons[] = array('id' => 'allot', 'title' => '分配权限', 'act' => 'sys/role/allot&app_scene=edit', 'show_name' => '分配权限[{role_code}-{role_name}]', 'show_cond' => 'obj.role_id != 1&& obj.role_id != '.$security_role_id, 'pop_size' => '800,500');
if ($check === true) {
    $buttons[] = array('id' => 'profession', 'title' => '业务/数据权限', 'callback' => 'profession_detail', 'show_name' => '业务/数据权限[{role_code}-{role_name}]', 'show_cond' => 'obj.role_id != 1 && obj.role_id != 100 && obj.role_id != '.$security_role_id, 'pop_size' => '800,500');
}

render_control ( 'DataTable', 'table', array (
		'conf' => array (
				'list' => array (
				
						array (
								'type' => 'button',
								'show' => 1,
								'title' => '操作',
								'field' => '_operate',
								'width' => '200',
								'align' => '',
								'buttons' => $buttons,
						),
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '代码',
								'field' => 'role_code',
								'width' => '100',
								'align' => '' 
						),
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '名称',
								'field' => 'role_name',
								'width' => '100',
								'align' => '' 
						),
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '描述',
								'field' => 'role_desc',
								'width' => '200',
								'align' => '',
								/*
								'format' => array (
										'type' => 'truncate',
										'value' => 20 
								) */
						),
						/*
						array (
								'type' => 'checkbox',
								'show' => 1,
								'title' => '内置',
								'field' => 'is_buildin',
								'width' => '50',
								'align' => '',
								
						),*/
						
				) 
		),
		'dataset' => 'sys/RoleModel::get_by_page',
		'queryBy' => 'searchForm',
		'idField' => 'role_id',
//		'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
<?php if ($check === TRUE): ?> 
    function profession_detail(_index, row){
        var url = "<?php echo get_app_url('sys/role_profession/do_list&app_scene=edit'); ?>";
        url += "&role_code="+row.role_code;
        //location.href= url;
        _id = ES_PAGE_ID+'$profession$'+_value;
        _url = '?app_act=sys/role_profession/do_list&app_scene=edit&role_code='+row.role_code+'&role_id='+row.role_id;
        _url = template(_url, row);
        openPage(_id,_url,template('业务/数据权限', row));
        return;
    }
<?php endif; ?>

<?php if ($check_delete === TRUE): ?>
    //角色删除
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('sys/role/do_delete'); ?>',
            data: {role_id: row.role_id},
            success: function (ret) {
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
<?php endif; ?>
</script>
