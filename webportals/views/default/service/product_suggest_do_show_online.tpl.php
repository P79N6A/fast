<?php render_control('PageHead', 'head1');?>
<?php echo load_js('ueditor1_4_3/ueditor.config.js,ueditor1_4_3/ueditor.all.js')?>
<form  class="form-horizontal" id="form1" action="?app_act=servicenter/productxqissue/do_xqissue_online" method="post">
<?php render_control('Form', 'form1', array(
        'noform'=>true,
	'conf'=>array(
		'fields'=>array(
                        array('title'=>'上线备注', 'type'=>'richinput', 'field'=>'xqsue_idea','span'=>15,),
                ),
        'hidden_fields'=>array(array('field'=>'xqsue_number'),array('field'=>'type')), 
	), 
	'buttons'=>array(
			array('label'=>'确认', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
   ),
	'act_add'=>'servicenter/productxqissue/do_xqissue_online',
	'data'=>$response['data'], 
        'rules'=>array(
                array('xqsue_idea', 'require')),
        'event'=>array('beforesubmit'=>'formBeforesubmit'),
)); 
?>
</form>
<script type="text/javascript">
    var opt_type = '<?php echo $request['type'];?>';
    var form =  new BUI.Form.HForm({
           srcNode : '#form1',
           submitType : 'ajax',
           callback : function(data){
                var type = data.status == 1 ? 'success' : 'error';
                
                if (data.status == 1) {
                    ui_closePopWindow('<?php echo $request['ES_frmId']?>'); 
                    if(opt_type==1){
                        window.location.reload();
                    } 
                } else {
                    BUI.Message.Alert(data.message, function(){
                            	ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                            },type)
                }

            }
   }).render();
   
    function formBeforesubmit() {
	return true; // 如果不想让表单继续提交，则return false
    }
</script>
