<div style='margin-top:300px;text-align:center;' id="_msg"></div>
<script type="text/javascript">

	var fullMask = null;//提示层
	BUI.use(['bui/mask'],function(Mask){

		fullMask = new Mask.LoadMask({
			el : '#_msg',
			msg : '<?php echo($response['message']);?>'
		});
	});

	fullMask.show();

	<?php if (1 == $response['status']){?>

		var url = '?app_act=tbauth/do_auth';
		$.post(url, function(data) {
			var ret = $.parseJSON(data);

			if (ret.status == 1) {
				$('#tbauth_succ').show();
			} else if(ret.status == 2) {
				//跳转到产品
				location.href = ret.data;
			//	alert(ret.message);
			}
			fullMask.hide();
		});
	<?php } ?>
</script>


<img id="tbauth_succ" src="assets/images/tbauth_succ.jpg" style="display: none;" />