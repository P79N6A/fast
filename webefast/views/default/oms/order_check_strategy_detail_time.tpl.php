<style type="text/css">
.form-horizontal .control-label {
    width: 100px;
}
</style>

<?php

$data = array();
if (isset($response['data']) && $response['data'] != '') {
    $data = $response['data'];
}

//require_lib('security/CSRFHandler', true);
//$csrf_field = array('field' => CsrfHandler::TOKEN_NAME,'value'=>CsrfHandler::get_token());
?>
<form id="form1" class="form-horizontal bui-form-horizontal bui-form bui-form-field-container" method="post" action="?app_act=oms/order_check_strategy/do_add_time" aria-disabled="false" aria-pressed="false">
	<?php foreach ($response['execut_time'] as $key => $time){?>
	<div class="row">
	<div class="control-group span11">
	<label class="control-label span3">执行时间点<?php echo ($key + 1);?>：</label>
	<div class="span8 controls">
	<input id="execut_time<?php echo $key;?>"  class="input-normal bui-form-field" type="text" value="<?php echo isset($time['content'])?$time['content']:'00:00'; ?>" name="execut_time[]" data-rules="{required: true}">
	<?php if ($key == 0){echo '<span style="color:red">格式：00:00</span>';} else {?>
	<a onclick="removefa2(<?php echo $key;?>);" id="del_time<?php echo $key;?>" href="#">删除</a>
	<?php }?>
	</div>
	</div>
	</div>
	<?php }?>
	
	<div class="row">
	<div class="control-group span11">
	<label class="control-label span3">执行时间点：</label>
	<div class="span8 controls " id="fa2">
	
	</div>
	<div class="span8 controls " > <a onclick="addfa2();" href="#">添加</a></div>
	</div>
	</div>
	<div class="row form-actions actions-bar">
	<div class="span13 offset3 ">
	<button id="submit" class="button button-primary" type="submit">保存</button>
	<button id="reset" class="button " type="reset">重置</button>
	</div>
	</div>
</form>
<div>
<span style="color:red;">提示：默认按以上时间点执行，若有业务需要可以修改时间点或者点击+新增时间点</span>
</div>
<script type="text/javascript">
var count_num = "<?php echo count($response['execut_time']);?>";
var form = new BUI.Form.HForm({
	srcNode : '#form1',
	submitType : 'ajax',
	callback : function(data){
		var type = data.status == 1 ? 'success' : 'error';
		if (data.status == 1) {
			ui_closePopWindow("<?php echo $request['ES_frmId']?>")
			
			//window.location.reload();
		} else {
			BUI.Message.Alert(data.message, function() { }, type);
		}
		
	}
}).render();

form.on('beforesubmit', function () {
	 
  
});
function addfa2(){
	$("#fa2").append('<input id="execut_time'+count_num+'" class="input-normal bui-form-field" type="text" value="00:00" name="execut_time[]" data-rules="{required: true}"> <a onclick="removefa2('+count_num+');" id="del_time'+count_num+'" href="#">删除</a>');
	count_num = parseInt(count_num) +1;
}
function removefa2(c_num){
	$("#execut_time"+c_num).remove();
	$("#del_time"+c_num).remove();
}
</script>

