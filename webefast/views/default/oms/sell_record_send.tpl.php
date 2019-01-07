<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">配送方式：</td>
        <td width="70%">
            <select name="express_code" id="express_code">
                <?php $list = oms_tb_all('base_express', array('status' => 1)); foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['express_code']?>"><?php echo $v['express_name']?></option>
                <?php } ?>
            </select>
            <script>$("#express_code").val("<?php echo $response['record']['express_code']?>")</script>
        </td>
    </tr>
    <tr>
        <td align="right">快递单号：</td>
        <td><input type="text" name="express_no" id="express_no" value="<?php echo $response['record']['express_no']?>"></td>
    </tr>
    <tr>
        <td align="right">校验物流单号：</td>
        <td><input type="checkbox" name="check_express_no" id="check_express_no" value="1" checked></td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_pay_ok").click(function(){
            //BUI.Message.Confirm('确认要发货吗？',function(){
            //},'question');




            BUI.Message.Show({
                title : '自定义提示框',
                msg : '确认要发货吗？',
                icon : 'question',
                buttons : [
                    {
                        text:'是',
                        elCls : 'button button-primary',
                        handler : function(){
                            var params = {
                                "sell_record_code": <?php echo $request['sell_record_code']?>,
                                "express_code": $("#express_code").val(),
                                "express_no": $("#express_no").val()
                            };
                            $("input[name=check_express_no]:checked").each(function(){
                                params["check_express_no"] = $(this).val();
                            });

                            $.post("?app_act=oms/sell_record/opt_send", params, function(data){
                                if(data.status != "1"){
                                    BUI.Message.Alert(data.message, 'error')
                                } else {
                                    ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                                }
                            }, "json")
                        }
                    },
                    {
                        text:'否',
                        elCls : 'button',
                        handler : function(){
                            ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                        }
                    }

                ]
            });

        })
    })
</script>