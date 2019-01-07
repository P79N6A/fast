<style>
    .page_container { padding: 0; }
    #weipinhui .t_right { text-align: right; }
</style>
<table cellspacing="0" class="table" style="border:solid 1px #dddddd;" id="weipinhui">
    <tr>
        <td colspan="4"><strong>批发信息</strong></td>
    </tr>
    <tr>
        <td class="t_right">分销商：</td>
        <td>
            <select name="distributor_code" id="distributor_code">
                <option value="" class="choosecheck">请选择</option>
                <?php
                $list = oms_tb_all('base_custom', array());
                foreach ($list as $k => $v) { ?>
                    <option value="<?php echo $v['custom_code'] ?>"><?php echo $v['custom_name'] ?></option>
                <?php } ?>
            </select>
            <span style="color:red">*</span>
        </td>
        <td class="t_right">出库仓库：</td>
        <td>
            <select name="store_code" id="store_code">
                <option value="" class="choosecheck">请选择</option>
                <?php
                $list = oms_tb_all('base_store', array());
                foreach ($list as $k => $v) { ?>
                    <option value="<?php echo $v['store_code'] ?>" <?php echo !empty($response['shop']['store_code']) && $response['shop']['store_code'] === $v['store_code'] ? 'selected' : '' ?> ><?php echo $v['store_name'] ?></option>
                <?php } ?>
            </select>
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr>
        <td class="t_right">配送方式：</td>
        <td>
            <select name="express_code" id="express_code">
                <option value="">请选择</option>
                <?php
                $list = oms_tb_all('base_express', array('status' => 1));
                foreach ($list as $k => $v) { ?>
                    <option value="<?php echo $v['express_code'] ?>" <?php echo !empty($response['shop']['express_code']) && $response['shop']['express_code'] === $v['express_code'] ? 'selected' : '' ?> ><?php echo $v['express_name'] ?></option>
                <?php } ?>
            </select>
        </td>
        <td class="t_right">快递单号：</td>
        <td>
            <input type="text" name="express" id="express" />
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="ok">确定</button>
    <input type="hidden" name="weipinhui_v" id="weipinhui_v" value="" />
</div>
<script>
    $(document).ready(function () {
      //  get_tel(1);
        $("#ok").click(function () {
            $("#ok").html("正在生成，请稍后。。。");
            $("#ok").attr("disabled", "disalbed");
            var store_code = $("#store_code").val();
            var express = $("#express").val();
            var express_code = $("#express_code").val();
            var distributor_code = $("#distributor_code").val();
            var tel = $("#tel").val();
            if (!store_code || !distributor_code) {
                BUI.Message.Alert('请将必填项填写完整', 'error');
                $("#ok").html("确定");
                $("#ok").removeAttr("disabled");
            }else {
                var params = {
                    "purchase_id": '<?php echo $request['purchase_id']; ?>',
                    "store_code": store_code,
                    "express": express, //快递单号
                    "express_code": express_code, //配送方式
                    "distributor_code": distributor_code,
                    "tel": tel,
                    "app_fmt": "json",
                };

                $.post("?app_act=api/api_yoho_purchase/do_create", params, function (data) {
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

    //$('#distributor_code').change(function () {
    //    get_tel(0);
    //});


    //联系人电话默认显示分销商中维护的电话
    //_type=1,加载页面时触发，=0，更改分销商触发
    //function get_tel(_type) {
    //    var distributor_code = $('#distributor_code').val();
    //    var tel = $('#tel').val();
    //    if (distributor_code == '' || tel != '' && _type == 1) {
    //        return;
    //    }
    //    $.post('?app_act=base/custom/get_custom_by_code', {custom_code: distributor_code}, function (ret) {
    //        if (ret.status == 1) {
    //            $('#tel').val(ret.data['mobile']);
    //        }
    //    }, 'json');
    //}
</script>