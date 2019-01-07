<?php render_control('PageHead', 'head1',
array('title'=>'岗位信息',
	'links'=>array(
        array('url'=>'archives/jobs/detail&app_scene=add', 'title'=>'新增岗位', 'is_pop'=>true, 'pop_size'=>'500,400'),
	),
	'ref_table'=>'table'
));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '关键字',
            'title' => '岗位代码/岗位名称',
            'type' => 'input',
            'id' => 'keyword' 
        ),
        array (
            'label' => '岗位状态',
            'title' => '是否有效',
            'type' => 'select',
            'id' => 'poststate',
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
                'type' => 'text',
                'show' => 1,
                'title' => '岗位序号',
                'field' => 'post_code',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '岗位名称',
                'field' => 'post_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '有效岗位',
                'field' => 'post_state',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array (
                	array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'pop:archives/jobs/detail&app_scene=edit', 'show_name'=>'编辑岗位', 
                		'show_cond'=>'obj.is_buildin != 1'),
                	array('id'=>'enable', 'title' => '启用', 
                		'callback'=>'do_enable', 'show_cond'=>'obj.post_state != 1'),
                	array('id'=>'disable', 'title' => '停用', 
                		'callback'=>'do_disable', 'show_cond'=>'obj.post_state == 1', 
                		'confirm'=>'确认要停用此岗位吗？'),
                ),
            )
        ) 
    ),
    'dataset' => 'archives/ArchiveModel::get_jobs_info',
    'queryBy' => 'searchForm',
    'idField' => 'post_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
        var url='<?php echo get_app_url('archives/jobs/set_active');?>';
	$.ajax({ type: 'POST', dataType: 'json',  
    url:url+"_"+active,
    data: {post_id: row.post_id, type: active}, 
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