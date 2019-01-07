<?php render_control('PageHead', 'head1');?>
<form  class="form-horizontal" id="form1" action="?app_act=servicenter/productissue/do_research" method="post">
<?php render_control('Form', 'form1', array(
        'noform'=>true,
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'研发受理人', 'type'=>'select_pop', 'field'=>'sue_research', 'select'=>'sys/orguser','show_scene'=>'add,edit'),
//                        array('title'=>'研发受理人', 'type'=>'input', 'field'=>'sue_research_name','show_scene'=>'view'),
                        ),
        'hidden_fields'=>array(array('field'=>'sue_number'),array('field'=>'type')), 
//        'hidden_fields'=>array(array('field'=>'type')),     
	), 
	'buttons'=>array(
			array('label'=>'确认', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
   ),
	'act_add'=>'servicenter/productissue/do_research',
	'data'=>$response['data'], 
)); 
?>
</form>
<script type="text/javascript">
    var form =  new BUI.Form.HForm({
           srcNode : '#form1',
           submitType : 'ajax',
           callback : function(data){
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == 1) {
                    ui_closePopWindow('<?php echo CTX()->request['ES_frmId']?>'); 
                    window.location.reload();
                } else {
                    BUI.Message.Alert(data.message, function() { }, type);
                }
            }
   }).render();
   
    function formBeforesubmit() {
	return true; // 如果不想让表单继续提交，则return false
    }
</script>
