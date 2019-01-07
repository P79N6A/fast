<?php
/*
$spec2_realname = load_model('prm/GoodsSpec2Model')->get_spec2_realname();
render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '编辑'.$spec2_realname,
	'links'=>array(
		'prm/spec2/do_list'=>$spec2_realname.'列表'
	)
));
 */
?>
 <?php 
  if ($response['app_scene'] == 'add')
  $remark = "一旦保存不能修改";
  else 
  $remark = "";
 ?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'代码', 'type'=>'input', 'field'=>'spec2_code', 'remark'=>$remark, 'edit_scene'=>'add'),
			array('title'=>'名称', 'type'=>'input', 'field'=>'spec2_name'),
			array('title'=>'描述', 'type'=>'textarea', 'field'=>'remark'),
		),
		'hidden_fields'=>array(array('field'=>'spec2_id')),
	),
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'prm/spec2/do_edit', //edit,add,view
	'act_add'=>'prm/spec2/do_add',
	'data'=>$response['data'],

	'rules'=>array(
		array('spec2_code', 'require'),
		array('spec2_name','require'),
	),
)); ?>
<script>
var is_add = '<?php echo $response['app_scene']?>';
if(is_add == 'add'){
var  check =0;
var ret = false;
form.on('beforesubmit',function(){
   if(check==0){
	   check++;
       //判断规格代码中有没有空格
       var spec2_code = $("#spec2_code").val();
       if (spec2_code.indexOf(" ") >=0){
           BUI.Message.Alert("规格代码输入有空格！",'error');
           return false;
       }
	   check_spec_name();
	   return false;
	 }
   check = 0;
   return ret;
	
});

    function check_spec_name(){
            var spec2_name = $("#spec2_name").val();
            var spec2_id = $("#spec2_id").val();
            var d = {'spec2_name':spec2_name,'spec2_id':spec2_id,'app_fmt': 'json'};
            $.post('?app_act=prm/spec2/add_check_name', d, function(data){
                    if (data.status == -1){
                    BUI.Message.Confirm('规格名称已存在，是否继续添加？',function(){
                            ret = true;
                            form.submit();
                },'warning');
            } else {
                    ret = true;
                    form.submit();
            }

        }, "json");
    } 
}
</script>


