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
    var type = "<?php echo $request['type'];?>"; 
    var url = "";
    $(document).ready(function(){
        $("#btn_pay_ok").click(function(){
            var params = <?php echo json_encode($request);?>;
            if(type=='multi'){
                url = '?app_act=oms/sell_record_notice/edit_express_code_multi_action';
                params.express_code_new=$("#express_code").val();
                params.sku_list=<?php echo json_encode($request['sku_list']);?>;
            }else{
                url = '?app_act=oms/sell_record_notice/edit_express_code_action';
                params.express_code_new=$("#express_code").val();
                params.sku_list=<?php echo json_encode(explode(',', $request['sku_list']))?>;
            }
            $.post(url, params, function(data){
                BUI.Message.Show({
                    title : '自定义提示框',
                    msg : data.message,
                    icon : data.status != '1' ? "error" : "info",
                    buttons : [
                        {
                            text:'确定',
                            elCls : 'button button-primary',
                            handler : function(){
                                ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                            }
                        }
                    ]
                });
            }, "json")
        })
    })
</script>

