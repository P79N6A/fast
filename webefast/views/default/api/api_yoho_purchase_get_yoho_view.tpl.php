<?php echo load_js("baison.js,record_table.js", true); ?>
<style>
    .page_container { padding: 0; }
    #weipinhui .t_right { text-align: right; }
</style>
<?php
$time_start = date("Y-m-d H:i:s",strtotime('-3 day'));
?>
<table cellspacing="0" class="table" style="border:solid 1px #dddddd;" id="weipinhui">
    <tr>
        <td class="t_right">店铺：</td>
        <td>
            <select name="distributor_code" id="shop_code">
                <option value="" class="choosecheck">请选择</option>
                <?php
                foreach ($response['shop'] as $k => $v) { ?>
                    <option value="<?php echo $v['shop_code'] ?>"><?php echo $v['shop_name'] ?></option>
                <?php } ?>
            </select>
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr>
        <td class="t_right">开始时间：</td>
        <td>
            <input type="text" id="start_time" name="start_time" class="input-normal calendar calendar-time bui-form-field-time bui-form-field" style="width:180px;" value="<?php echo  $time_start?>"/>
        </td>
    </tr>
    <tr>
        <td class="t_right">结束时间：</td>
        <td>
            <input type="text" id="end_time" name="end_time" class="input-normal calendar calendar-time bui-form-field-time bui-form-field" style="width:180px;" value="<?php echo date('Y-m-d H:i:s')?>"/>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="ok">确定</button>
    <input type="hidden" name="weipinhui_v" id="weipinhui_v" value="" />
</div>
<script>
    $(document).ready(function () {
        $("#ok").click(function () {
            $("#ok").html("正在获取，请稍后。。。");
            $("#ok").attr("disabled", "disalbed");
            var shop_code = $("#shop_code").val();
            var start_time = $("#start_time").val();
            var end_time = $("#end_time").val();
            if (!shop_code) {
                BUI.Message.Alert('请选择店铺', 'error');
                $("#ok").html("确定");
                $("#ok").removeAttr("disabled");
            }else {
                var params = {
                    "shop_code": shop_code,
                    "start_time": start_time,
                    "end_time": end_time,
                    "app_fmt": "json",
                };

                $.post("?app_act=api/api_yoho_purchase/get_yoho_by_api", params, function (data) {
                    var type = data.status == 1 ? 'success' : 'error';
                    if (type == 'error') {
                        $("#ok").html("确定");
                        $("#ok").removeAttr("disabled");
                        BUI.Message.Alert(data.message, 'error');
                    } else {
                        $("#ok").html("确定");
                        $("#ok").removeAttr("disabled");
                        BUI.Message.Alert(data.message, function () {
                            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                        }, type);
                    }
                }, "json");
            }
        });
    });

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