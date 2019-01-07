<?php echo load_js('common.js,jquery.min.js,jquery.json.min.js,AjaxList.js,baison.js,openDiv.js,My97DatePicker/WdatePicker.js,jquery-ztree-2.1.js');?>
<?php echo load_css('zTreeStyle/zTreeStyle.css,openDiv.css');?>
<?php render_control('PageHead', 'head1',
array('title'=>'角色列表',
	'links'=>array(
          
	)
));?>

<?php render_control('SearchForm', 'SearchForm1', array(
	'cmd'=>array('label'=>'查询', 'id'=>'btn-search'),
	'fields'=>array(
		array('label'=>'关键字', 'title'=>'代码或名称', 'type'=>'input', 'id'=>'keyword'),
		array('label'=>'内置角色', 'type'=>'select', 'id'=>'buildin', 'data'=>array_from_dict(array(''=>'请选择', '1'=>'是','0'=>'否')))
	)));?>


<div id="table" style="width: 100%;">
<?php render_control('DataTable', 'table', array(
	'conf'=>array('list'=>array(
		array('type'=>'text', 	'show'=>1, 'title'=>'代码',    	'field'=>'role_code', 	'width'=>'180', 		'align'=>''),
		array('type'=>'text', 	'show'=>1, 'title'=>'名称',  	'field'=>'role_name', 	'width'=>'180', 		'align'=>''),
		array('type'=>'text', 	'show'=>1, 'title'=>'描述', 	    'field'=>'role_desc', 	'width'=>'200', 		'align'=>'', 'format'=>array('type'=>'truncate', 'value'=>20)),
		array('type'=>'text', 	'show'=>1, 'title'=>'内置角色', 	'field'=>'buildin', 	'width'=>'80', 		'align'=>'', 'format'=>array('type'=>'map_checked')),
	)), 
	'dataset'=>'sys/acl_role_model::get_by_page', 
	'table'=>'acl_role'
	)); ?>
</div>
<script type="text/javascript">
<?php render_control('AjaxListSearchJs', 'search', array('ref'=>'table', 'trigger'=>'#btn-search', 'event'=>'click', 'where'=>array('keyword', 'buildin')));?>
</script>