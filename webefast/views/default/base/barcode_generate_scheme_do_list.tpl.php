<?php render_control('PageHead', 'head1',
		array('title'=>'条码生成方案',
				
				'links'=>array(
						array('url'=>'base/barcode_generate_scheme/detail&app_scene=add', 'title'=>'添加条码生成方案', 'is_pop'=>true, 'pop_size'=>'500,400'),
				),
				'ref_table'=>'table'
));?>


<?php
/*
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
    
    		
    ) 
) );
*/
?>

<?php

render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '方案名称',
                'field' => 'name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '模式',
                'field' => 'mode',
                'width' => '100',
                'align' => '' 
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '长度',
            		'field' => 'length',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '分隔符',
            		'field' => 'separate_sign',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '参照对象（位置1）',
            		'field' => 'refer1',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '参照对象（位置2）',
            		'field' => 'refer2',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '参照对象（位置3）',
            		'field' => 'refer3',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '是否启用',
            		'field' => 'img',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '描述',
            		'field' => 'note',
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
                		'act'=>'pop:base/barcode_generate_scheme/detail&app_scene=edit', 'show_name'=>'编辑', 
                		'show_cond'=>'obj.is_buildin != 1'),
                   array('id'=>'delete', 'title' => '删除', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),
                	
                ),
            )
        ) 
    ),
    'dataset' => 'base/BarcodeGenerateSchemeModel::get_by_page',
    //'queryBy' => 'searchForm',
    'idField' => 'scheme_id',
   
) );
?>
<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',  
    url: '<?php echo get_app_url('base/barcode_generate_scheme/do_delete');?>', data: {scheme_id: row.scheme_id}, 
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
