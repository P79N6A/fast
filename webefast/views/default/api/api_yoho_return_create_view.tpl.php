<style>
    .page_container{padding:0;}
    #weipinhui .t_right{text-align: right;padding-top: 6px;}
</style>
<table cellspacing="0" class="table" style="border:solid 1px #dddddd;" id="weipinhui">
    <tr>
        <td class="t_right">分销商：</td>
        <td>
            <select name="distributor_code" id="distributor_code">
                <option value="" class="choosecheck">请选择</option>
                <?php
                $list = oms_tb_all('base_custom', array());
                foreach ($list as $k => $v) {?>
                    <option value="<?php echo $v['custom_code'] ?>"><?php echo $v['custom_name'] ?></option>
                <?php } ?>
            </select>
            <span style="color:red">*</span>
        </td>
        <td class="t_right">入库仓库：</td>
        <td>
            <select name="store_code" id="store_code">
                <option value="" class="choosecheck">请选择</option>
                <?php
                $list = oms_tb_all('base_store', array());
                foreach ($list as $k => $v) {?>
                    <option value="<?php echo $v['store_code'] ?>"><?php echo $v['store_name'] ?></option>
                <?php } ?>
            </select>
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr>
        <td class="t_right">退货类型：</td>
        <td>
            <select name="return_type_code" id="return_type_code">
                <?php foreach ($response['return_type'] as $k => $v) {?>
                    <option value="<?php echo $v['return_type_code'] ?>" <?php if($v['return_type_code']=='301'){?>selected = "selected"<?php } ?>><?php echo $v['return_type_name'] ?></option>
                <?php } ?>
            </select>
        </td>
        <td class="t_right"></td>
        <td></td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="ok">确定</button>
</div>
<script>
    $(document).ready(function () {
        $("#ok").click(function () {
            $("#ok").html("正在生成，请稍后。。。");
            $("#ok").attr("disabled", "disalbed");
            var store_code = $("#store_code").val();
            var distributor_code = $("#distributor_code").val();
            var return_type_code = $("#return_type_code").val();
            if (!store_code || !distributor_code || !return_type_code) {
                BUI.Message.Alert('请将必填项填写完整', 'error');
                $("#ok").html("确定");
                $("#ok").removeAttr("disabled");
            } else {
                var params = {
                    "return_id": '<?php echo $request['id']; ?>',
                    "store_code": store_code,
                    "distributor_code": distributor_code,
                    "return_type_code": return_type_code,
                    "app_fmt": "json"
                };

                $.post("?app_act=api/api_yoho_return/do_create", params, function (data) {
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


</script>