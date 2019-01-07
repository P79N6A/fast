<form  id="form" action="?app_act=oms/sell_record_cz/save_config&app_fmt=json" method="post" >
<table cellspacing="0" class="table ">
	 <tr>
        <td class="tdlabel">com端口号：</td>
        <td >
        <input id="com_name" type="text"  name="cz_com_name" data-rules="{required : true}" value="<?php echo $response['data']['cz_com_name'];?>">
        </td>
    </tr>
    <tr>
        <td class="tdlabel">波特率：</td>
        <td >
           <input id="baud_rate" type="text"  name="cz_baud_rate" data-rules="{required : true}" value="<?php echo $response['data']['cz_baud_rate'];?>">
        </td>
    </tr>
  <tr>
		<td class="tdlabel"></td>
		<td >
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
		            	ui_closePopWindow("<?php echo $request['ES_frmId']?>");
		            	window.location.href = "?app_act=oms/sell_record_cz/view";
	                }
	            }, type);
			}
	}).render();

</script>
<?php echo load_js('comm_util.js')?>
<?php echo load_js("pur.js",true);?>