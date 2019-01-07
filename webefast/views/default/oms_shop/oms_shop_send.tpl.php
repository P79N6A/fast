<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">快递公司：</td>
        <td width="70%">
            <select name="express_code" id="express_code">
                <?php
                $list = oms_tb_all('base_express', array('status' => 1));
                foreach ($list as $k => $v) {
                    ?>
                    <option value="<?php echo $v['express_code'] ?>"><?php echo $v['express_name'] ?></option>
                <?php } ?>
            </select>
            <script>$("#express_code").val("<?php echo $response['record']['express_code'] ?>")</script>
        </td>
    </tr>
    <tr>
        <td align="right">快递单号：</td>
        <td><input type="text" name="express_no" id="express_no" value="<?php echo $response['record']['express_no'] ?>"></td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_send_ok">确定</button>
</div>

<script>
    $(document).ready(function () {
        $("#btn_send_ok").click(function () {
            BUI.Message.Show({
                msg: '确定要发货吗？',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            var data = {express_code: $("#express_code").val(), express_no: $("#express_no").val()};
                            var params = {type: 'send', record_code: <?php echo $request['record_code'] ?>, data: data};
                            $.post("?app_act=oms_shop/oms_shop/opt", params, function (ret) {
                                if (ret.status == 1) {
                                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                                } else {
                                    BUI.Message.Alert(ret.message);
                                }
                            }, "json");
                        }
                    },
                    {
                        text: '否',
                        elCls: 'button',
                        handler: function () {
                            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                        }
                    }
                ]
            });
        });
    });
</script>