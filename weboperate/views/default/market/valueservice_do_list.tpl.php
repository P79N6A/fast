<?php render_control('PageHead', 'head1',
array('title'=>'增值服务列表',
	'links'=>array(
        array('url'=>'market/valueservice/detail&app_scene=add', 'title'=>'新增增值服务', 'is_pop'=>false, 'pop_size'=>'500,400'),
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
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'value_cp_id',
            'data'=>ds_get_select('chanpin',1)
        ),
        array (
            'label' => '类别',
            'title' => '类别',
            'type' => 'select',
            'id' => 'value_cat',
            'data'=>ds_get_select('valueserver_cat',1)
        ),
        array (
            'label' => '状态',
            'title' => '状态',
            'type' => 'select',
            'id' => 'value_enable',
            'data'=>ds_get_select_by_field('valuetype')
        ),
        array (
            'label' => '是否发布',
            'title' => '是否发布',
            'type' => 'select',
            'id' => 'value_publish_status',
            'data'=>ds_get_select_by_field('is_publish')
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
                'title' => '代码',
                'field' => 'value_code',
                'width' => '80',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'value_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品',
                'field' => 'value_cp_id_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '产品版本',
                'field' => 'value_cp_version',
                'width' => '80',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('product_version'))
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '类别',
                'field' => 'value_cat_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '价格',
                'field' => 'value_price',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '最低版本要求',
                'field' => 'value_require_version_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '状态',
                'field' => 'value_enable',
                'width' => '80',
                'align' => '',
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                        array('id'=>'view', 'title' => '查看', 
                		'act'=>'market/valueservice/detail&app_scene=view', 'show_name'=>'查看增值服务'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'market/valueservice/detail&app_scene=edit', 'show_name'=>'编辑增值服务', 
                		'show_cond'=>'obj.is_buildin != 1'),
                        array('id'=>'enable', 'title' => '启用', 
                		'callback'=>'do_enable', 'show_cond'=>'obj.value_enable != 1'),
                	array('id'=>'disable', 'title' => '停用', 
                		'callback'=>'do_disable', 'show_cond'=>'obj.value_enable == 1', 
                		'confirm'=>'确认要停用吗？'),
                    array('id'=>'pub', 'title' => '发布',
                        'callback'=>'publish', 'show_cond'=>'obj.value_publish_status == 0&&obj.value_enable == 1',
                        ),
                    array('id'=>'dis_pub', 'title' => '停止发布',
                        'callback'=>'dis_publish', 'show_cond'=>'obj.value_publish_status == 1&&obj.value_enable == 1',
                        'confirm'=>'确认要停止发布吗？'),
                ),
            )
        ) 
    ),
    'dataset' => 'market/ValueModel::get_valueserver',
    'queryBy' => 'searchForm',
    'idField' => 'value_id',
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
        var url='<?php echo get_app_url('market/valueservice/set_active');?>';
	$.ajax({ type: 'POST', dataType: 'json',  
    url:url+"_"+active,
    data: {value_id: row.value_id, type: active}, 
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

function publish(_index, row) {
    _do_set_publish(_index, row, 'enable');
}
function dis_publish(_index, row) {
    _do_set_publish(_index, row, 'disable');
}
function _do_set_publish(_index, row, active) {
    var params = {
        "type": active,
        "value_id": row.value_id,
    };
    $.post("?app_act=market/valueservice/set_publish&app_fmt=json", params, function (ret) {
        var type = ret.status == 1 ? 'success' : 'error';
        if (type == 'success') {
            BUI.Message.Alert(ret.message, type);
            tableStore.load();
        } else {
            BUI.Message.Alert(ret.message, type);
        }
    }, "json");
}

</script>
