<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">仓库：</td>
        <td width="70%">
            <select name="store_code" id="store_code">
                <?php $list = load_model('base/StoreModel')->get_purview_store(); foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['store_code']?>"><?php echo $v['store_name']?></option>
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
            $(this).attr("disabled","disabled");
            var params = {
                "sell_record_id_list": <?php echo json_encode(explode(',', $request['sell_record_id_list']))?>,
                "store_code": $("#store_code").val()
            };

            $.post("?app_act=oms/sell_record/edit_store_code_action", params, function(data){
                if(data.status != '1'){
                    $("#btn_pay_ok").removeAttr("disabled");
                    BUI.Message.Alert(data.message, 'error')
                }else {
                    BUI.Message.Alert(data.message, 'info')
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                }
            }, "json")
        })
    })
</script>