

<?php 

render_control('Form', 'form1', array(
	'conf' => array(
		'fields' => array(
			array('title' => '库位代码', 'type' => 'input', 'field' => 'shelf_code', 'edit_scene' => 'add'),
			array('title' => '库位名称', 'type' => 'input', 'field' => 'shelf_name'),
			array('title' => '所属仓库', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store'], 'edit_scene' => 'add'),
			array('title' => '启用', 'type' => 'checkbox', 'field' => 'status'),
			array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
			
			
		),
		'hidden_fields' => array(array('field' => 'shelf_id')),
	),
	'buttons' => array(
		array('label' => '提交', 'type' => 'submit'),
		array('label' => '重置', 'type' => 'reset'),
	),
	'act_edit' => 'base/shelf/do_edit', //edit,add,view
	'act_add' => 'base/shelf/do_add',
	'data' => $response['data'],
	'rules' => array(
		array('shelf_code', 'require'),
		array('shelf_name', 'require'),
		
	),
));?>

<span id="kehu_code" style="display: none;">oms_test</span>

<script type="text/javascript">
	var scene = "<?php echo $app['scene'];?>";
	$("#shop_code").attr("disabled", "disabled");
	
	form.on('beforesubmit', function () {
	    $("#shop_code").attr("disabled", false);
	});
   
	
	
</script>
