<?php render_control('PageHead', 'head1',
		array('title'=>'会员列表',
				
				'links'=>array(
						array('url'=>'cangku/cangku/detail&app_scene=add', 'title'=>'添加仓库', 'is_pop'=>true, 'pop_size'=>'500,400'),
				),
				'ref_table'=>'table'
));?>


<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
    
    		array (
    				'label' => '会员名称',
    				'type' => 'input',
    				'id' => 'user_name',
    		),	
    		array (
    				'label' => '销售渠道',
    				'type' => 'select',
    				'id' => 'sale_channel_id',
    				'data'=>$response['sale_channel'],
    		),
    		array (
    				'label' => '来源店铺',
    				'type' => 'select',
    				'id' => 'shop_id',
    				'data'=>$response['sale_channel'],
    		),
    		array (
    				'label' => '黑名单',
    				'type' => 'select',
    				'id' => 'is_active',
    				'data'=>ds_get_select_by_field('is_black'),
    		),
    		array (
    				'label' => '收货人',
    				'type' => 'input',
    				'id' => 'consignee',
    		),
    		array (
    				'label' => '手机号',
    				'type' => 'input',
    				'id' => 'tel',
    		),
    		array (
    				'label' => '街道地址',
    				'type' => 'input',
    				'id' => 'address',
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
                'title' => '会员名称',
                'field' => 'user_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '白名单',
            		'field' => 'is_active',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '收货人',
            		'field' => 'consignee',
            		'width' => '100',
            		'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '手机',
                'field' => 'tel',
                'width' => '100',
                'align' => '' 
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '地址',
            		'field' => 'address',
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
                		'act'=>'pop:cangku/cangku/detail&app_scene=edit', 'show_name'=>'编辑', 
                		'show_cond'=>'obj.is_buildin != 1'),
                   array('id'=>'delete', 'title' => '删除', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),
                	
                ),
            )
        ) 
    ),
    'dataset' => 'member/MemberModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'member_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<div><span><input type="button" onClick="do_delete_items();" value="删除"></span></div>
<link href="assets/css/jquery.multiSelect.css" rel="stylesheet" type="text/css" />
<?php //echo load_js('jquery.multiSelect.js');?>

<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',  
    url: '<?php echo get_app_url('cangku/cangku/do_delete');?>', data: {store_id: row.store_id}, 
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
function do_delete_items() {
	var selections =  tableGrid.getSelection();
	/*
	for(var i in selections) {
	    alert(selections[i].member_id);
	}*/
	var ids = [];
     BUI.each(selections,function(item){
       ids.push(item.member_id);
     });
     
     alert(ids);
	BUI.Message.Confirm('确认要删除此信息吗？',function(){
		
	   alert("dong");	
	},'question');
}
</script>

