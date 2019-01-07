
 <?php
  if ($response['app_scene'] == 'add')
  $remark = "一旦保存不能修改";
  else
  $remark = "";
 ?>
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'代码', 'type'=>'input', 'field'=>'spec1_code', 'remark'=>$remark, 'edit_scene'=>'add'),
			array('title'=>'名称', 'type'=>'input', 'field'=>'spec1_name'),
			array('title'=>'描述', 'type'=>'textarea', 'field'=>'remark'),
		),
		'hidden_fields'=>array(array('field'=>'spec1_id')),
	),
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'prm/spec1/do_edit', //edit,add,view
	'act_add'=>'prm/spec1/do_add',
	'data'=>$response['data'],

	'rules'=>array(
		array('spec1_code', 'require'),
		array('spec1_name','require'),
	),
	//'event'=>array('beforesubmit'=>'check_spec_name'),
)); ?>
<script>
var is_add = '<?php  echo $response['app_scene'];?>';
if (is_add == 'add') {
var  check =0;
var ret = false;
form.on('beforesubmit',function(){
   if(check==0){
	   check++;
	   //判断规格代码中有没有空格
       var spec1_code = $("#spec1_code").val();
       if (spec1_code.indexOf(" ") >=0){
           BUI.Message.Alert("规格代码输入有空格！",'error');
           return false;
       }
	   check_spec_name() ;
	   return false;
	 }
   check = 0;
   return ret;

});

    function check_spec_name(){
            var spec1_name = $("#spec1_name").val();
            var spec1_id = $("#spec1_id").val();
            var d = {'spec1_name':spec1_name,'spec1_id':spec1_id,'app_fmt': 'json'};
            $.post('?app_act=prm/spec1/add_check_name', d, function(data){
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


