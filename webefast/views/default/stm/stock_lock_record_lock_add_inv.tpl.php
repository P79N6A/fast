<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="35%" text_align="center">商品编码：</td>
        <td width="65%">
            <?php echo $response['data']['goods_code'] ?>
        </td>
    </tr>
    <tr>
        <td width="35%" text_align="center">当前实际锁定库存数：</td>
        <td width="65%">
            <?php
            if ($request['lof_status'] == 0) {
                echo $response['data']['available_num'];
            } else {
                echo $response['data']['num'];
            }
            ?>
        </td>
    </tr>
    <tr>
        <td width="35%" text_align="center">最大可追加数：</td>
        <td width="65%">
            <?php echo $response['data']['inv_available_mum']; ?>
        </td>
    </tr>
    <tr>
        <td width="35%" text_align="center">请输入库存锁定追加数：</td>
        <td width="65%">
            <input type="text" style="width: 105px" id="add_inv"><span style="color: red">仅允许录入正整数或负整数</span>
        </td>
    </tr>
</table>
<span style="color: red" id="error"></span>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>
<script>
    $(document).ready(function () {
        var lof_status = '<?php echo $request['lof_status'] ?>';
        $("#btn_pay_ok").click(function () {
            $("#btn_pay_ok").html("正在(追加/释放)，请稍后。。。");
            $("#btn_pay_ok").attr("disabled", "disalbed");
            if ($("#add_inv").val() == ''||$("#add_inv").val() == '0') {
                $("#error").text('请输入整数！');
                $("#btn_pay_ok").html("确定");
                $("#btn_pay_ok").removeAttr("disabled");
                return;
            }
            $("#error").text('');
            var params = {
                "id": <?php echo $request['id']; ?>,
                "add_inv_num": $("#add_inv").val(),
                "lof_status": lof_status,
                "inv_available_mum": '<?php echo $response['data']['inv_available_mum'];?>'
            };
            $.post("?app_act=stm/stock_lock_record/add_inv_action", params, function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, function () {
                        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
                    }, 'success');
                } else {
                    $("#btn_pay_ok").html("确定");
                    $("#btn_pay_ok").removeAttr("disabled");
                    //BUI.Message.Alert(data.message, 'error');
                    $("#error").text(data.message);
                }
            }, "json")
        })
    })
</script>