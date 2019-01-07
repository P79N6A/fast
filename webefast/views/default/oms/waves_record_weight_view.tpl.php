    <div style="margin-bottom:10px;font-weight:bold;">波次单号：<?php echo $request['waves_record_code'];?></div>
    <div>
    实际重量：<input type="text" name="cz_weight" id="cz_weight" value=""  />    千克
    </div>
    <div style="margin-left:70px;margin-top:10px;">
        <input type="button" id="btn_ok" class="button button-primary" value="确定" >
    </div>
    <div id="error_msg" style="color:red;"></div>
<script>
$("#btn_ok").click(function(){
        var reg =/^\d+(\.\d+)?$/;
	var weight = ($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, "");
//	if (!weight){
//    	BUI.Message.Alert('请输入实际重量', 'error')
       if (($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, "") == '') {        
		   $("#error_msg").html("重量不能为空");
		   document.getElementById("cz_weight").focus();
		   return;
	   } else if(!reg.test(($("#cz_weight").val()).replace(/(^\s*)|(\s*$)/g, ""))) {
            $("#error_msg").html("称重重量不能为非数字或负数");
            document.getElementById("cz_weight").focus();
            return;
        } else {
            $("#error_msg").html("");
            var data = {wave_record_id:<?php echo $request['wave_record_id']; ?>, weight: weight};
            $.post('?app_act=oms/waves_record/do_wave_weight', data, function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'info')
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
                } else {
                    $("#error_msg").html(data.message);
                }
            }, 'json');
        }
    });
   
</script>