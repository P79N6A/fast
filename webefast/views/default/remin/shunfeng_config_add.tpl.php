<form  id="form" action="?app_act=remin/shunfeng/do_config_add&app_fmt=json" method="post" >
<table cellspacing="0" class="table ">
	 <tr>
        <td class="tdlabel">校验码：</td>
        <td >
        <input id="checkword" type="text"  name="checkword" data-rules="{required : true}" value="<?php echo $response['config']['checkword'];?>">
        </td>
    </tr>
    <tr>
        <td class="tdlabel">接口URL：</td>
        <td >
           <input id="api_url" type="text"  name="api_url" data-rules="{required : true}" value="<?php echo $response['config']['api_url'];?>">
        </td>
    </tr>
    <tr>
        <td class="tdlabel">月结账号：</td>
        <td >
           <input id="j_custid" type="text"  name="j_custid" data-rules="{required : true}" value="<?php echo $response['config']['j_custid'];?>">
        </td>
    </tr>
  <tr>
		<td class="tdlabel"></td>
		<td >
			<input type="hidden" name="id" id="id" value="<?php echo $response['config']['id'];?>"/>
			<input type="hidden" name="express_id" id="express_id" value="<?php echo $request['express_id'];?>"/>
			<button id="submit" class="button button-primary" type="submit">提交</button>
			<button id="reset" class="button " type="reset">重置</button>
		</td> 
	</tr>
</table>
</form>

<script>

	var form =  new BUI.Form.HForm({
	    srcNode : '#form',
	    submitType : 'ajax',
	    callback : function(data){
				var type = data.status == 1 ? 'success' : 'error';
	            BUI.Message.Alert(data.message, function() {
	            	if (data.status == 1) {
		            	ui_closePopWindow("<?php echo $request['ES_frmId']?>")
	                }
	            }, type);
			}
	}).render();

</script>
<?php echo load_js('comm_util.js')?>
<?php echo load_js("pur.js",true);?>