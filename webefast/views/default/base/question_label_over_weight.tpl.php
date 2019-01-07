<div>
    订单商品重量合计等于或超过 <input type="text" style="width: 15%" placeholder="" class="input" value="<?php echo $response['content'];?>" id="weight"/>kg，订单转单自动设问。
</div>
<br/>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>
<br/>
<span style="color: red">注：<br/>
1.请输入大于0的数字，仅支持3位小数<br/>
2.若未输入值或0，该设问规则不生效<br/>
    3.使用时请开启参数：转单计算订单理论重量<br/>
</span>

<script>
    $(document).ready(function () {
        $("#btn_pay_ok").click(function () {
            var reg = /^\d+(\.\d+)?$/;
            if (($("#weight").val()).replace(/(^\s*)|(\s*$)/g, "") == '') {
                BUI.Message.Alert('重量不能为空!', 'error');
                document.getElementById("weight").focus();
                return;
            } else if (!reg.test(($("#weight").val()).replace(/(^\s*)|(\s*$)/g, ""))) {
                BUI.Message.Alert('重量不能为非数字或负数', 'error');
                document.getElementById("weight").focus();
                return;
            }
            var params = {
                "content": $("#weight").val()
            };
            var url = '<?php echo get_app_url('base/question_label/do_edit_weight'); ?>';
            $.post(url, params, function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, function () {
                        ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                    }, 'success');
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json")
        })
    })
</script>