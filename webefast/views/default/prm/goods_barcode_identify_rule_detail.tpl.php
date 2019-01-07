<?php

render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '编辑条码识别方案',
    'links' => array(
        'prm/brand/do_list' => '条码识别列表'
    )
));
?>
<?php

$data = array();
if (isset($response['data']) && $response['data'] != '') {
    $data = $response['data'];
}

//require_lib('security/CSRFHandler', true);
//$csrf_field = array('field' => CsrfHandler::TOKEN_NAME,'value'=>CsrfHandler::get_token());
?>
<form id="form1" class="form-horizontal bui-form-horizontal bui-form bui-form-field-container" method="post" action="?app_act=prm/goods_barcode_identify_rule/<?php echo $response['action']; ?>" aria-disabled="false" aria-pressed="false">
	<input id="name" class="bui-form-field" type="hidden" value="1" name="do" style="display: none;" aria-disabled="false">
	<input id="app_scene" class="bui-form-field" type="hidden" value="edit" name="app_scene" style="display: none;" aria-disabled="false">
	<input id="rule_id" class="bui-form-field" type="hidden" value="<?php echo isset($response['data']['rule_id'])?$response['data']['rule_id']:''; ?>" name="rule_id" style="display: none;" aria-disabled="false">
	<!--  <input class="bui-form-field" type="hidden" value="b6e7ffc77c292f953bcd5b61f8e6bdba6fbbf13c" name="__es_csrf_t__" style="display: none;" aria-disabled="false">-->
	<input type="hidden" name="<?php echo $response['data']['csrf_field']['field']?>" value="<?php echo $response['data']['csrf_field']['value']?>"/>
	<div class="row">
	<div class="control-group span11">
	<label class="control-label span3">方案名称：</label>
	<div class="span8 controls">
	<input id="rule_name"  class="input-normal bui-form-field" type="text" value="<?php echo isset($response['data']['rule_name'])?$response['data']['rule_name']:''; ?>" name="rule_name" aria-disabled="false" data-rules="{required: true}">
	</div>
	</div>
	</div>
	<div class="row">
	<div class="control-group span11">
	<label class="control-label span3">优先级：</label>
	<div class="span8 controls">
	<input id="priority" class="input-normal bui-form-field" type="text" value="<?php echo isset($response['data']['priority'])?$response['data']['priority']:''; ?>" name="priority" aria-disabled="false" aria-pressed="false" disabled="disabled">
	</div>
	</div>
	</div>
	<div class="row">
	<div class="control-group span11">
	<label class="control-label span3">
	方案1
	<input class="bui-form-field-radio bui-form-check-field bui-form-field" type="radio" <?php if(isset($response['data']['rule_sort']) && $response['data']['rule_sort'] == '1'){ ?> checked="checked" <?php } ?> name="rule_sort" <?php if( $response['action'] == 'do_add'){ ?> checked="checked" <?php } ?> value="1" aria-disabled="false">
	：
	</label>
	<div class="span8 controls">
	去掉前
	<input id="fangan1_length1" class="input-normal bui-form-field" type="text" aria-pressed="false" aria-disabled="false" name="fangan1_length1" data-rules="{regexp: /^\d*$/}"  style="width: 50px;" value="<?php echo isset($response['data']['fangan1_length1'])?$response['data']['fangan1_length1']:''; ?>">
	位，去掉后
	<input id="fangan1_length2" class="input-normal bui-form-field" type="text" aria-pressed="false" aria-disabled="false" name="fangan1_length2"  data-rules="{regexp: /^\d*$/}" value="<?php echo isset($response['data']['fangan1_length2'])?$response['data']['fangan1_length2']:''; ?>" style="width: 50px;">
	位，剩余中间字符作为条码
	</div>
	</div>
	</div>
	<div class="row">
	<div class="control-group span11">
	<label class="control-label span3">
	方案2
	<input class="bui-form-field-radio bui-form-check-field bui-form-field" type="radio" <?php if(isset($response['data']['rule_sort']) && $response['data']['rule_sort'] == '2'){ ?> checked="checked" <?php } ?>  name="rule_sort" value="2"  aria-disabled="false">
	：
	</label>
	<div class="span8 controls " id="fa2">
	<?php if(isset($response['data']['fangan2'])){ ?>
		<?php foreach($response['data']['fangan2'] as $k=>$v){ ?>
				从第
				<input  class="input-normal bui-form-field" type="text" data-rules="{regexp: /^\d*$/}" aria-pressed="false" aria-disabled="false" name="fangan2_length[]" style="width: 50px;" value="<?php echo $v[0]; ?>">
				位开始，截取
				<input id="fangan2_length2" class="input-normal bui-form-field" type="text" data-rules="{regexp: /^\d*$/}" aria-pressed="false" aria-disabled="false" name="fangan2_length[]" value="<?php echo $v[1]; ?>" style="width: 50px;">
				位     
				<br>
				
		 <?php } ?>
	<?php }else{ ?>
	                                          从第
				<input  class="input-normal bui-form-field" type="text" aria-pressed="false" data-rules="{regexp: /^\d*$/}" aria-disabled="false" name="fangan2_length[]" style="width: 50px;" value="">
				位开始，截取
				<input id="fangan2_length2" class="input-normal bui-form-field" type="text" data-rules="{regexp: /^\d*$/}" aria-pressed="false" aria-disabled="false" name="fangan2_length[]" value="" style="width: 50px;">
				位     
				<br> 
				从第
				<input  class="input-normal bui-form-field" type="text" aria-pressed="false" data-rules="{regexp: /^\d*$/}" aria-disabled="false" name="fangan2_length[]" style="width: 50px;" value="">
				位开始，截取
				<input id="fangan2_length2" class="input-normal bui-form-field" type="text" data-rules="{regexp: /^\d*$/}" aria-pressed="false" aria-disabled="false" name="fangan2_length[]" value="" style="width: 50px;">
				位     
				<br> 
	 <?php } ?>
	</div>
	<div class="span8 controls " > <a onclick="addfa2();" href="#">添加</a></div>
	</div>
	</div>
	<div class="row" style="border: medium none;">
	<div class="control-group span11">
	<label class="control-label span3">备注：</label>
	<div class="span8 controls">
	<textarea id="remark" class="bui-form-field" rows="5" cols="40" name="remark" aria-disabled="false" aria-pressed="false"></textarea>
	</div>
	</div>
	</div>
	<div class="row form-actions actions-bar">
	<div class="span13 offset3 ">
	<button id="submit" class="button button-primary" type="submit">提交</button>
	<button id="reset" class="button " type="reset">重置</button>
	</div>
	</div>
</form>
<script type="text/javascript">

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
	 
    $("#priority").attr("disabled", false);
});
function addfa2(){
	$("#fa2").append('从第<input  class="input-normal bui-form-field" type="text" aria-pressed="false" aria-disabled="false" name="fangan2_length[]" style="width: 50px;" value="">位开始，截取<input id="fangan2_length2" class="input-normal bui-form-field" type="text" aria-pressed="false" aria-disabled="false" name="fangan2_length[]" value="" style="width: 50px;">位    <br>');
}
</script>

