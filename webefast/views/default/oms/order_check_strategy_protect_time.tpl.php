<style type="text/css">
.form-horizontal .control-label {
    width: 100px;
}
</style>
<form class="form-horizontal" onsubmit="return false;">
      <div class="row">
	<div class="control-group span11">
	<label class="control-label span3">订单保护期</label>
	<div class="span8 controls">
	<input id="protect_time"  class="input-normal bui-form-field" type="text" value="<?php echo isset($response['protect_time']['content'])?$response['protect_time']['content']:''; ?>" data-rules="{required: true}" name="protect_time">
	分钟
	</div>
	</div>
	</div>
<span style="color:red;">提示：为提高订单合并率，建议设置订单保护期；若无此需要，将订单保护期设置为0即可。</span>
	

            <div class="row form-actions actions-bar">
	<div class="span13 offset3 ">
			<button id="protect_time_submit"  class="button button-primary">保存</button>
			<button type="reset" class="button">重置</button>
		</div>	
                </div>	
      </form>

	
<script type="text/javascript">
$("#protect_time_submit").click(function(){
	var protect_time = $("#protect_time").val();   
	$.ajax({ type: 'POST', dataType: 'json',
		url: '<?php echo get_app_url('oms/order_check_strategy/protect_time_edit');?>', data: {protect_time: protect_time},
		success: function(ret) {
		   var type = ret.status == 1 ? 'success' : 'error';
           if (type == 'success') {
                BUI.Message.Alert('设置成功', type);
                location.reload();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
     	}
   });
});
</script>

