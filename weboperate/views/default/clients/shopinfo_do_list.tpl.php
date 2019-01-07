<?php render_control('PageHead', 'head1',
array('title'=>'店铺信息',
	'links'=>array(
        array('url'=>'clients/shopinfo/detail&app_scene=add', 'title'=>'新建店铺', 'is_pop'=>false, 'pop_size'=>'500,400'),
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
            'label' => '店铺名称',
            'title' => '店铺',
            'type' => 'input',
            'id' => 'shopname' 
        ),
        array (
            'label' => '平台类型',
            'title' => '店铺',
            'type' => 'select',
            'id' => 'platform' ,
            'data'=>ds_get_select('shop_platform',2)
        ),
        array (
            'label' => '所属客户',
            'title' => '客户名称',
            'type' => 'input',
            'id' => 'clientname' 
        ),
        array (
            'label' => '代理名称',
            'title' => '代理商名称',
            'type' => 'input',
            'id' => 'agent_name'

        ),       
//        array (
//            'label' => '按单',
//            'type' => 'select',
//            'id' => 'isad',
//            'data'=>ds_get_select_by_field('boolstatus')
//        ),
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
                'title' => '店铺名称',
                'field' => 'sd_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '平台类型',
                'field' => 'sd_pt_id_name',
                'width' => '100',
                'align' => '' 
            ),    
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '所属客户',
                'field' => 'kh_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '代理名称',
                'field' => 'sd_agent',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '服务负责人',
                'field' => 'sd_servicer_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '200',
                'align' => '',
                'buttons' => array (
                	array('id'=>'view', 'title' => '详细', 
                		'act'=>'clients/shopinfo/detail&app_scene=view', 'show_name'=>'详细信息'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'clients/shopinfo/detail&app_scene=edit', 'show_name'=>'编辑店铺', 
                		'show_cond'=>'obj.is_buildin != 1'),
                        array('id'=>'databind', 'title' => '推送绑定', 
                		'callback'=>'do_databind', 'show_cond'=>'obj.sd_databind != 1'),
                	array('id'=>'nodatabind', 'title' => '取消绑定', 
                		'callback'=>'do_nodatabind', 'show_cond'=>'obj.sd_databind == 1', 
                		'confirm'=>'确认取消绑定？'),
                ),
            )
        ) 
    ),
    'dataset' => 'clients/ShopModel::get_shop_info',
    'queryBy' => 'searchForm',
    'idField' => 'sd_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>

<script type="text/javascript">
function do_databind(_index, row) {
	_do_set_databind(_index, row, 'databind');
}
function do_nodatabind(_index, row) {
	_do_set_databind(_index, row, 'nodatabind');
}
function _do_set_databind(_index, row, databind) {
        var url='<?php echo get_app_url('clients/shopinfo/set_shop');?>';
	$.ajax({ type: 'POST', dataType: 'json',  
            url:url+"_"+databind,
            data: {sd_id: row.sd_id, type: databind}, 
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
