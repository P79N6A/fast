<?php render_control('PageHead', 'head1',
array('title'=>'用户列表',
	'links'=>array(
        array('url'=>'sys/user/detail&app_scene=add', 'title'=>'新增用户', 'is_pop'=>true, 'pop_size'=>'500,400',),
	),
	'ref_table'=>'table'
));?>
<?php
$buttons = array(
            array(
                'label' => '查询',
		'id' => 'btn-search',
		'type' => 'submit',
                ),

            );
if (load_model('sys/PrivilegeModel')->check_priv('sys/user/export_list')) {
	$buttons[] = array(
		'label' => '导出',
		'id' => 'exprot_list',
	);
}
render_control ( 'SearchForm', 'searchForm', array (
    'buttons' =>$buttons,
    'fields' => array (
        array (
            'label' => '关键字',
            'title' => '登录名/真实名',
            'type' => 'input',
            'id' => 'keyword' 
        ),
        array (
            'label' => '有效',
            'title' => '是否有效',
            'type' => 'select',
            'id' => 'status',
            'data'=>ds_get_select_by_field('boolstatus')
        ),
    ) 
) );
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
                'width' => '300',
                'align' => '',
                'buttons' => array (
                	//array('id'=>'audit', 'title' => '审核', ),
                	array('id'=>'view', 'title' => '查看', 
                			'act'=>'pop:sys/user/detail&app_scene=view', 'show_name'=>'查看用户'),
                	array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'pop:sys/user/detail&app_scene=edit', 'show_name'=>'编辑用户', 
                		'show_cond'=>'obj.is_buildin != 1'),
                	array('id'=>'reset_password', 'title' => '重设密码', 'callback'=>'do_reset_pwd', 
                        'show_cond'=>"obj.user_code != 'admin'", 'confirm'=>'确认要重置<b>[{user_name}]</b>的密码吗？'),
                	array('id'=>'enable', 'title' => '启用', 
                		'callback'=>'do_enable', 'show_cond'=>'obj.status != 1'),
                	array('id'=>'disable', 'title' => '停用', 
                		'callback'=>'do_disable', 'show_cond'=>'obj.status == 1', 
                		'confirm'=>'确认要停用<b>[{user_name}]</b>吗？'),
                	array('id'=>'list_row', 'title' => '角色列表', 
                		'act'=>'pop:sys/user/role_list&user_id={user_id}', 'show_name'=>'<i>[{user_name}]</i>关联的角色', 
                		'pop_size'=>'800,500'),
                        array('id'=>'delete', 'title' => '删除',
                                'callback' => 'do_delete',
                                'show_cond'=>'obj.status != 1 && obj.delete_auth != 0'),
                ),
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '登录名',
                'field' => 'user_code',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '真实名',
                'field' => 'user_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '是否有效',
                'field' => 'status',
                'width' => '100',
                'align' => '' 
            ),

        ) 
    ),
    'dataset' => 'sys/UserModel::get_by_page',
    'queryBy' => 'searchForm',
    'export'=> array('id'=>'exprot_list','conf'=>'user_list','name'=>'用户列表', 'export_type' => 'file'),
    'idField' => 'user_id',
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) );
?>
<script src="assets/js/ncode.js"></script>
<script type="text/javascript">
    
function do_reset_pwd (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json', 
    url: '<?php echo get_app_url('sys/user/reset_pwd');?>', data: {user_id: row.user_id}, 
    success: function(ret) {
    	var type = ret.status == 1 ? 'success' : 'error';
    	if (type == 'success') {
        BUI.Message.Alert('新密码：'+ncode(ret.data), type);
    	} else {
            if(ret.status == -10){
                BUI.Message.Alert(ret.message,function(){
		window.open(ret.data);
                }, type);
            }else{
                BUI.Message.Alert(ret.message, type);
            }
            
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
        url: '<?php echo get_app_url('sys/user/update_active');?>',
        data: {user_id: row.user_id, type: active}, 
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
function do_delete(_index, row){
    $.ajax({
        type: 'POST', dataType: 'json', 
        url: "<?php echo (get_app_url('sys/user/do_delete'));?>",
        data:{user_id: row.user_id},
        success:function(ret){
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