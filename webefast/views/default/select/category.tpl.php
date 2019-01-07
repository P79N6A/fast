<?php render_control('PageHead', 'head1',
    array('title' => '分类',
	    'links'=>array(
	    		array('url'=>'prm/category/detail&app_scene=add', 'title'=>'添加分类', 'is_pop'=>true, 'pop_size'=>'500,400'),
	    ),
        'ref_table' => 'table'
        ));
 
?>
<?php
render_control ('SearchForm', 'searchForm', array ('cmd' => array ('label' => '查询',
            'id' => 'btn-search'
            ),
          'fields' => array (
		        array (
		        		'label' => '名称/代码',
		        		'type' => 'input',
		        		'id' => 'code_name'
		        ),
                     array (
		        		'label' => '',
		        		'type' => 'hidden',
		        		'id' => 'no_category_id'
		        ),
            
            )
        ));

?>
<?php

render_control ('DataTable', 'table', array ('conf' => array ('list' => array (
                array ('type' => 'text',
                    'show' => 1,
                    'title' => '代码',
                    'field' => 'category_code',
                    'width' => '200',
                    'align' => ''
                    ),
                array ('type' => 'text',
                    'show' => 1,
                    'title' => '名称',
                    'field' => 'category_name',
                    'width' => '200',
                    'align' => ''
                    ),
                array ('type' => 'text',
                    'show' => 1,
                    'title' => '描述',
                    'field' => 'remark',
                    'width' => '200',
                    'align' => ''
                    ),
                array ('type' => 'button',
                    'show' => 1,
                    'title' => '查看',
                    'field' => '_operate',
                    'width' => '300',
                    'align' => '',
                    'buttons' => array (
                    	array('id'=>'edit', 'title' => '编辑',
                    		'act'=>'pop:prm/category/detail&app_scene=edit', 'show_name'=>'编辑'),	 
                    	array('id'=>'delete', 'title' => '删除', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),
                    	array('id'=>'add', 'title' => '新增子分类',
                    			'act'=>'pop:prm/category/detail&app_scene=add&child=1', 'show_name'=>'新增子分类'),
                        array('id' => 'child', 'title' => '查看下级',
                            'callback' => 'do_list_child','show_cond'=>'obj.has_next == 1'),
                           
                        array('id' => 'parent', 'title' => '查看上级',
                            'callback' => 'do_list_parent','show_cond'=>'obj.has_parent == 1'),
                        ),
                    ),
                )
            ),
        'dataset' => 'prm/CategoryModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'category_id',
         'init'=>(isset($request['ES_pFrmId']))?'nodata':'',
        ));

?>

<script type="text/javascript">
<!--
function do_delete(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('prm/category/do_delete');
?>', data: {category_id: row.category_id},
    success: function(ret) {
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

function do_list_child(_index, row){
	var obj = {"category_type":"child","category_id":row.category_id,"type":"","name":""};
	obj.start = 1;
	//for(var s in obj){
	//	alert(obj[s]);
	//}
	tableStore.load(obj);
}

function do_list_parent(_index, row){
	var obj = {"category_type":"parent","category_id":row.p_id,"type":"","name":""};
	obj.start = 1;
	tableStore.load(obj);
}

//-->
</script>
<?php if(isset($request['ES_pFrmId'])):?>
<script>
 
 $(function(){
      var  now_category_id =  getTopFrameWindowByName('<?php echo $request['ES_pFrmId']; ?>').category_id;
      $('#no_category_id').val(now_category_id);

      $('#btn-search').click();
 });
   
</script>
<?php endif;?>

<?php //echo_selectwindow_js($request, 'table', array('id'=>'category_id', 'code'=>'category_code', 'name'=>'category_name')) ?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'category_code', 'code'=>'category_code', 'name'=>'category_name')) ?>