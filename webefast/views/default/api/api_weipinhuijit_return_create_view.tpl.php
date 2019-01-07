<style>
    .page_container{padding:0;}
    #weipinhui .t_right{text-align: right;padding-top: 6px;}
</style>
<table cellspacing="0" class="table" style="border:solid 1px #dddddd;" id="weipinhui">
    <tr>
        <td class="t_right">批发通知单：</td>
        <td >
            <select name="notice_code" id="notice_code">
                <?php foreach ($response['notice'] as $k => $v) { ?>
                    <option value="<?php echo $v; ?>"><?php echo $v ?></option>
                <?php } ?>
                <option value="">新建批发退货通知单</option>
            </select>
            <span style="color:red">*</span>
        </td>
        <td class="t_right">分销商：</td>
        <td>
            <select name="distributor_code" id="distributor_code">
                <option value="" class="choosecheck">请选择</option>
                <?php
                $list = oms_tb_all('base_custom', array());
                foreach ($list as $k => $v) {
                    ?>
                    <option value="<?php echo $v['custom_code'] ?>"><?php echo $v['custom_name'] ?></option>
                <?php } ?>
            </select>
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr>
        <td class="t_right">入库仓库：</td>
        <td>
            <select name="store_code" id="store_code">
                <option value="" class="choosecheck">请选择</option>
                <?php
                $list = oms_tb_all('base_store', array());
                foreach ($list as $k => $v) {
                    ?>
                    <option value="<?php echo $v['store_code'] ?>"><?php echo $v['store_name'] ?></option>
                <?php } ?>
            </select>
            <span style="color:red">*</span>
        </td>
        <td class="t_right">退货类型：</td>
        <td>
            <select name="return_type_code" id="return_type_code">
                <?php
                foreach ($response['return_type'] as $k => $v) {
                    ?>
                    <option value="<?php echo $v['return_type_code'] ?>" <?php if($v['return_type_code']=='301'){?>selected = "selected"<?php } ?>><?php echo $v['return_type_name'] ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr style="height: 35px;">
        <td class="t_right">销货商品价格：</td>
        <td colspan="3" id="price" style="padding-top:6px;">
            <label class="radio" ><input type="radio" checked="checked" name="price" value="actual_unit_price">供货价（不含税，默认）</label>
            <label class="radio" style="margin-left:20px;"><input type="radio" name="price" value="actual_market_price">供货价（含税）</label>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="ok">确定</button>
    <input type="hidden" name = "weipinhui_v" id="weipinhui_v" value=""/>
</div>
<script>
    $(document).ready(function () {
        get_notice_info();
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
                    "return_id": '<?php echo $request['return_id']; ?>',
                    "store_code": store_code,
                    "return_notice_code": $("#notice_code").val(), //通知单
                    "distributor_code": distributor_code,
                    "return_type_code": return_type_code,
                    "price_type": $('#price input[name="price"]:checked ').val(),
                    "app_fmt": "json"
                };

                $.post("?app_act=api/api_weipinhuijit_return/do_create", params, function (data) {
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

    function get_notice_info() {
        var notice_code = $("#notice_code").val();
        if (notice_code != '') {
            var params = {"notice_code": notice_code,
                "app_fmt": "json"};
            $.post("?app_act=api/api_weipinhuijit_return/get_notice_info", params, function (data) {
                $("#distributor_code").find("option[value='" + data.distributor_code + "']").attr("selected", true);
                $("#store_code").find("option[value='" + data.store_code + "']").attr("selected", true);
                $("#return_type_code").find("option[value='" + data.return_type_code + "']").attr("selected", true);
            }, "json");
        }
    }

    $('#notice_code').change(function () {
        if ($('#notice_code').val() == '') {
            $("#notice_code option").each(function (i, o) {
                if (i == 1) {
                    $("#distributor_code option[value='']").attr('selected', 'selected');
                    $("#store_code option[value='']").attr('selected', 'selected');
                    $("#return_type_code option[value='']").attr('selected', 'selected');
                }
            });
        }
    });

</script>