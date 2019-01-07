<?php render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑品牌',
	'links'=>array(
		'prm/category/do_list'=>'品牌列表'
	)
));?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
		    array('title'=>'上级分类', 'type'=>'select_pop', 'field'=>'p_code', 'select'=>'prm/category' ),
			array('title'=>'代码', 'type'=>'input', 'field'=>'category_code', 'edit_scene' => 'add' ),
			array('title'=>'名称', 'type'=>'input', 'field'=>'category_name'),
			array('title'=>'描述', 'type'=>'textarea', 'field'=>'remark'),
		), 
		'hidden_fields'=>array(array('field'=>'category_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'prm/category/do_edit', //edit,add,view
	'act_add'=>'prm/category/do_add',
	'data'=>$response['data'],
	
	'rules'=>array(
		array('category_code', 'require'),
		array('category_name','require'),
	),
)); ?>



<script>
    var category_id = 0;
    $(function(){
        category_id = $('#category_id').val();
    });
</script>