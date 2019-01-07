<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">分销商：</td>
        <td width="70%">
            <select name="distributor_code" id="distributor_code">
                <?php $list = oms_tb_all('base_custom', array());
                foreach ($list as $k => $v) { ?>
                    <option value="<?php echo $v['custom_code'] ?>"><?php echo $v['custom_name'] ?></option>
<?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="30%" align="right">仓库：</td>
        <td width="70%">
            <select name="store_code" id="store_code">
                <?php $list = oms_tb_all('base_store', array());
                foreach ($list as $k => $v) { ?>
                    <option value="<?php echo $v['store_code'] ?>"><?php echo $v['store_name'] ?></option>
<?php } ?>
            </select>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>

<script>
    $(document).ready(function () {
        $("#btn_pay_ok").click(function () {
            var params = {
                "pick_id": <?php echo $request['pick_id']; ?>,
                "store_code": $("#store_code").val(),
                "distributor_code": $("#distributor_code").val(),
                "app_fmt": "json",
            };

            $.post("?app_act=api/api_weipinhuijit_pick/do_create_notice_record", params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'error') {
                    BUI.Message.Alert(data.message, 'error')
                } else {
                    BUI.Message.Alert(data.message, function () {
                        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
                    }, type)


                }
            }, "json")
        })
    })
</script>