<?php echo load_js("baison.js",true);?>
<style>
#search_platform li{  float:left;  list-style:none;  width:120px;}
.api_button{  width:120px;  height:50px;}
.api_button_operate{ width:500px;  margin:auto;}
 #down_button{  position:absolute;  top:60px;  margin-left:350px;}
</style>
<script>
$(function(){
	BUI.use('bui/calendar',function(Calendar){
    	var datepicker = new Calendar.DatePicker({
	    	trigger:'.calendar',
	    	autoRender : true,
                showTime:true,
    	});
   });
})
</script>
<?php
$time_start = date("Y-m-d H:i:s",strtotime('-3 day'));
$time_end = date("Y-m-d H:i:s");
?>
<div class="search">
    <label class="control-label">时 间：</label>
    <input type="text" name="start_time" id="start_time" class="calendar" style="width:150px;height:30px;" value="<?php echo $time_start;?>"/>
     ~
    <input type="text" name="end_time" id="end_time" class="calendar" style="width:150px;height:30px;" value="<?php echo $time_end;?>"/>
    <br />
    <br />
    <label class="control-label">店  铺：</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 <select name="shop_code" id="shop_code" data-rules="{required : true}">
     <option value="">请选择店铺</option>
    <?php foreach($response['shop'] as $k=>$v){ ?>
     <option  value ="<?php echo $v['shop_code']; ?>" ><?php echo $v['shop_name']; ?></option>
    <?php } ?>
</select>
</div>
<br />
<div class="api_button_operate">
<input type="button" class="api_button" id="down_button" value="下载" onclick="task_down()">
</div>
<script type="text/javascript">
    var fullMask = null;//提示层
    BUI.use(['bui/mask'], function (Mask) {

        fullMask = new Mask.LoadMask({
            el: 'body',
            msg: '下载中,请耐心等待！'
        });
    });

    //下载
    function task_down() {
        var shop_code = $('#shop_code').val();
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        if (shop_code == '') {
            BUI.Message.Alert('请选择店铺', 'error');
        } else {
            $("#down_button").attr("disabled", true);
            var url = '?app_act=api/api_weipinhuijit_return/down_refund';
            fullMask.show();
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    'shop_code': shop_code,
                    'start_time': start_time,
                    'end_time': end_time,
                },
                dataType: "json",
                success: function (data) {
                    if (data.code == 2) {
                        var task_sn = data.task_sn;
                        check_progress(task_sn);
                    } else if (data.code == 1) {
                        BUI.Message.Alert('下载完成', 'success');
                        $("#down_button").attr("disabled", false);
                        fullMask.hide();
                    } else {
                        BUI.Message.Alert('下载失败', 'error');
                        $("#down_button").attr("disabled", false);
                        fullMask.hide();
                    }
                }
            });
        }

    }

    function check_progress(task_sn) {
        var check_url = '?app_act=oms/api_order/down_trade_check';
        $.ajax({
            type: "POST",
            url: check_url,
            data: {'task_sn': task_sn},
            dataType: "json",
            success: function (data) {
                if (data.code == 2) {
                    setTimeout(function () {
                        check_progress(task_sn)
                    }, 5000);
                } else if (data.code == 1) {
                    BUI.Message.Alert('下载完成', 'success');
                    $("#down_button").attr("disabled", false);
                    fullMask.hide();
                } else {
                    BUI.Message.Alert('下载失败', 'error');
                    $("#down_button").attr("disabled", false);
                    fullMask.hide();
                }
            }
        });
    }

</script>