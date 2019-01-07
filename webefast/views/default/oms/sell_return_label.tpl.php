<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">退单标签：</td>
        <td width="70%">
            <select name="label_code" id="label_code">
                <?php $list = oms_tb_all('base_return_label', array()); foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['return_label_code']?>"><?php echo $v['return_label_name']?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_ok").click(function(){
            var params = {'app_fmt':'json',sell_return_code: <?php echo $request['sell_return_code']?>,label_code:$('#label_code').val()};
            $.post("?app_act=oms/sell_return/opt_label", params, function(data){
                if(data.status != "1"){
                    BUI.Message.Alert(data.message, 'error')
                } else {
                	BUI.Message.Alert(data.message, 'info')
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                }
            }, "json")
        });
    });

</script>