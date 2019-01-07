<?php render_control('PageHead', 'head1',array('title'=>'报价模板列表',
    	'links'=>array(
            array('url'=>'market/planprice/detail&app_scene=add', 'title'=>'新建报价模板',  'pop_size'=>'500,400'),
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
            'type' => 'select',
            'id' => 'product',
            'class'=>'input-large',
            'data'=>ds_get_select('chanpin',2)
        ),
        array (
            'label' => '营销类型',
            'type' => 'select',
            'id' => 'strategy_type',
            'class'=>'input-large',
            'data'=>ds_get_select('market',2)
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
                'title' => '模板名称',
                'field' => 'price_name',
                'width' => '250',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '营销类型',
                'field' => 'price_stid_name',
                'width' => '100',
                'align' => '' ,
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'price_cpid_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '产品版本',
                'field' => 'price_pversion',
                'width' => '80',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('product_version'))
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '启用状态',
                'field' => 'price_status',
                'width' => '100',
                'align' => '' ,
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'price_note',
                'width' => '200',
                'align' => '' ,
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
                		'act'=>'market/planprice/detail&app_scene=view', 'show_name'=>'查看报价方案'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'market/planprice/detail&app_scene=edit', 'show_name'=>'编辑报价方案'),   
                        array('id'=>'enable', 'title' => '启用', 
                		'callback'=>'do_enable', 'show_cond'=>'obj.price_status != 1'),
                	array('id'=>'disable', 'title' => '停用', 
                		'callback'=>'do_disable', 'show_cond'=>'obj.price_status == 1', 
                		'confirm'=>'确认要停用吗？'),
                        
                ),
            )
        ) 
    ),
    'dataset' => 'market/PlanpriceModel::get_plan_list',
    'queryBy' => 'searchForm',
    'idField' => 'price_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
     'events'=>array(
            'rowdblclick'=>array('ref_button'=>'view')),
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
        var url='<?php echo get_app_url('market/planprice/set_active');?>';
	$.ajax({ type: 'POST', dataType: 'json',  
            url:url+"_"+active,
            data: {price_id: row.price_id, type: active}, 
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
