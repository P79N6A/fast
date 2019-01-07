<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" text_align="center">拣货员：</td>
        <td width="70%">
            <select name="pick_member_code" id="pick_member_code">
                <?php $list = oms_tb_all('base_store_staff', array('status' => 1, 'staff_type' => 0));
                foreach ($list as $k => $v) { ?>
                    <option value="<?php echo $v['staff_code'] ?>"><?php echo $v['staff_name'] ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
</table>
<br/>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>
<br/>
<span style="color: red">友情提示：拣货绩效统计报表可显示拣货订单数以及商品数</span>
<script>
    $(document).ready(function () {
        $("#btn_pay_ok").click(function () {
            var params = {
                "waves_record_id_list": <?php echo json_encode(explode(',', $request['waves_record_id']))?>,
                // "waves_record_id_list": <?php/// echo $request['waves_record_id'];?>,
                "pick_member_code": $("#pick_member_code").val()
            };
            $.post("?app_act=oms/waves_record/update_pick_member", params, function (data) {
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