<style>
    .form-horizontal .control-group .span3{width:100px;}
</style>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'商品条形码', 'type'=>'input', 'field'=>'barcode','edit_scene'=>'add'),
			array('title'=>'商品编码', 'type'=>'input', 'field'=>'goods_code','edit_scene'=>'add'),
			array('title'=>'税收编码', 'type'=>'input', 'field'=>'tax_code'),
                        array('title'=>'单位', 'type'=>'input', 'field'=>'unit'),
                        array('title'=>'商品编码简称', 'type'=>'input', 'field'=>'goods_code_short'),
		),
		'hidden_fields'=>array(array('field'=>'tax_id')),
	),
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'prm/goods_tax_tl/do_edit', //edit,add,view
	//'act_add'=>'prm/spec1/do_add',
	'data'=>$response['data'],

	'rules'=>array(
		array('tax_code', 'require'),
                array('goods_code_short', 'require'),
	),
	//'event'=>array('beforesubmit'=>'check_spec_name'),
)); ?>
<script>

</script>



