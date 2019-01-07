<table cellspacing="0" class="table table-bordered">
      <tr>
        <td width="30%" align="right">商品名称：</td>
        <td width="70%">
        <?php echo $response['data']['num_iid'];?> </td>
    </tr>
    <tr>
        <td width="30%" align="right">旧商家编码：</td>
        <td width="70%"><?php echo $response['data']['outer_id'];?> </td>
    </tr>
     <tr>
        <td width="30%" align="right">新商家编码：</td>
        <td width="70%">
        <input type="text" name='outer_id' id='outer_id'/>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确认修改</button>
</div>

<?php echo load_js('comm_util.js')?>
<script type="text/javascript">
$(document).ready(function(){
    $("#btn_pay_ok").click(function(){
        var params = {
            "num_iid": '<?php $num_iid = isset($request['num_iid'])?$request['num_iid']:'';echo $num_iid;?>',
            "sku_id": '<?php $sku_id = isset($request['sku_id'])?$request['sku_id']:'';echo $sku_id;?>',
            "outer_id": $("#outer_id").val()
        };

        $.post("?app_act=api/taobao/goods/do_update_code&app_fmt=json", params, function(data){
        	var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message,function(){
           	ui_closePopWindow(<?php echo $response['data']['ES_frmId'];?>);
               }, type);
        }, "json")
    })
})
</script>

