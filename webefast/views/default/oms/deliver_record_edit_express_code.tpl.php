<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">配送方式：</td>
        <td width="70%">
            <select name="express_code" id="express_code">
                <?php $list = oms_tb_all('base_express', array('status' => 1)); foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['express_code']?>"><?php echo $v['express_name']?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_pay_ok").click(function(){
            var params = {
            	"record_code":<?php echo $request['record_code']?>,
                "deliver_record_id_list": <?php echo isset($request['deliver_record_id_list'])?json_encode(explode(',', $request['deliver_record_id_list'])):'[]';?>,
                "express_code": $("#express_code").val(),
                "type":<?php echo $request['type']?>,
            };

            $.post("?app_act=oms/deliver_record/edit_express_code_action", params, function(data){
                BUI.Message.Alert(data.message, 'info')
                ui_closePopWindow("<?php echo $request['ES_frmId']?>")
            }, "json")
        })
    })
</script>