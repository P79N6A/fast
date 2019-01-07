<?php
$is_power = load_model('sys/PrivilegeModel')->check_priv('prm/spec1/detail&app_scene=add');
if($is_power == TRUE){
    $links = array(array('url'=>'prm/spec1/detail&app_scene=add', 'title'=>'添加'.$response['goods_spec1_rename'], 'is_pop'=>true, 'pop_size'=>'500,400'),);
}
 render_control('PageHead', 'head1',
        array('title'=>$response['goods_spec1_rename'],
            'links'=>$links,
            'ref_table'=>'table'
    ));
?>


<?php
render_control ( 'SearchForm', 'searchForm', array (
    'buttons' => array (
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
        
    ),
    
    'fields' => array (

    		array (
    				'label' => '名称/代码',
    				'type' => 'input',
    				'id' => 'code_name'
    		),
                array(
			'label' => '更新时间',
			'type' => 'group',
			'field' => 'lastdate',
			'child' => array(
				array('title' => 'start', 'type' => 'date', 'field' => 'changed_time_start'),
				array('pre_title' => '~', 'type' => 'date', 'field' => 'changed_time_end', 'remark' => ''),
			)
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
                'width' => '150',
                'align' => '',
                'buttons' => array (

                	array('id'=>'edit', 'title' => '编辑','priv' => 'prm/spec1/detail&app_scene=edit', 
                		'act'=>'pop:prm/spec1/detail&app_scene=edit', 'show_name'=>'编辑',
                		'show_cond'=>'obj.is_buildin != 1'),

                   array('id'=>'delete', 'title' => '删除','priv' => 'prm/spec1/do_delete', 'show_cond'=>'obj.is_common == 1', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),

                ),
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '代码',
                'field' => 'spec1_code',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'spec1_name',
                'width' => '100',
                'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '描述',
            		'field' => 'remark',
            		'width' => '200',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '更新时间',
            		'field' => 'lastchanged',
            		'width' => '200',
            		'align' => ''
            ),

        )
    ),
    'dataset' => 'prm/Spec1Model::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'spec1_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'spec1_list', 'name' => '规格1导出','export_type'=>'file'),
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('prm/spec1/do_delete');?>', data: {spec1_id: row.spec1_id},
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
</script>

