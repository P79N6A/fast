<?php render_control('PageHead', 'head1',
array('title'=>'增值服务类别',
	'links'=>array(
        array('url'=>'market/valueservice_cat/detail&app_scene=add', 'title'=>'新增增值服务类别', 'is_pop'=>true, 'pop_size'=>'500,400'),
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
            'title' => '类别代码/类别名称',
            'type' => 'input',
            'id' => 'keyword' 
        ),
        array (
            'label' => '状态',
            'title' => '是否有效',
            'type' => 'select',
            'id' => 'vc_enable',
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
                'title' => '类别代码',
                'field' => 'vc_code',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '类别名称',
                'field' => 'vc_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '关联产品',
                'field' => 'vc_cp_id_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '有效类别',
                'field' => 'vc_enable',
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
                		'act'=>'pop:market/valueservice_cat/detail&app_scene=edit', 'show_name'=>'编辑增值服务类别', 
                		'show_cond'=>'obj.is_buildin != 1'),
                	array('id'=>'enable', 'title' => '启用', 
                		'callback'=>'do_enable', 'show_cond'=>'obj.vc_enable != 1'),
                	array('id'=>'disable', 'title' => '停用', 
                		'callback'=>'do_disable', 'show_cond'=>'obj.vc_enable == 1', 
                		'confirm'=>'确认要停用此类别吗？'),
                ),
            )
        ) 
    ),
    'dataset' => 'market/Value_catModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'vc_id',
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
        var url='<?php echo get_app_url('market/valueservice_cat/set_active');?>';
	$.ajax({ type: 'POST', dataType: 'json',  
    url:url+"_"+active,
    data: {vc_id: row.vc_id, type: active}, 
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