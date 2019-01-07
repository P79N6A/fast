<style type='text/css'>
    html { overflow-x: hidden; overflow-y: hidden; }
</style>
<body>
<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="100%">
            <textarea id="cancel_remark" style="width: 99%; height: 70px;" placeholder="请输入取消原因" name="cancel_remark"></textarea>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>
</body>
<script>
    $(document).ready(function(){
        $("#btn_pay_ok").click(function(){
                    <?php 
                        if(!empty($request['deliver_record_id'])){
                    ?>
                    $.ajax({ type: 'POST', dataType: 'json',
                    url: '<?php echo get_app_url('oms/deliver_record/do_cancel');?>', 
                    data: {deliver_record_id: "<?php echo $request['deliver_record_id']; ?>",waves_record_id: "<?php echo $request['waves_record_id']; ?>",remark:$("#cancel_remark").val()},
                    success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('取消成功', type);
                        ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
                        <?php }else{ ?>
                            $.ajax({ type: 'POST', dataType: 'json',
                            url: '<?php echo get_app_url('oms/waves_record/do_cancel');?>', data: {waves_record_id: "<?php echo $request['waves_record_id']; ?>",remark:$("#cancel_remark").val()},
                            success: function(ret) {
                                var type = ret.status == 1 ? 'success' : 'error';
                                if (type == 'success') {
                                    BUI.Message.Alert('取消成功', type);
                                    ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                                } else {
                                    BUI.Message.Alert(ret.message, type);
                                }
                            }
                        });
                        <?php } ?>
        })
    })
</script>   