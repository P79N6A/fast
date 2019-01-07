<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">挂起原因：</td>
        <td width="70%">
            <select id="is_pending_code">
            <?php
                $time = array();
                $sql = "select suspend_label_code,suspend_label_name,cancel_suspend_time from base_suspend_label where suspend_label_code<>'wait_check_refund'";
                $pending_label_arr = ctx()->db->get_all($sql);
                //echo '<hr/>$pending_label_arr<xmp>'.var_export($pending_label_arr,true).'</xmp>';die;
                foreach($pending_label_arr as $sub_arr){
                    $time[$sub_arr['suspend_label_code']] =  $sub_arr['cancel_suspend_time']==0?'':date('Y-m-d H:i:s',  strtotime("+{$sub_arr['cancel_suspend_time']} hour"));
                    echo "<option value='{$sub_arr['suspend_label_code']}'>{$sub_arr['suspend_label_name']}</option>";
                }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="30%" align="right">解挂时间：</td>
        <td width="70%">
            <input type="text" id="is_pending_time" style="width:150px" value="" class="input-normal calendar"/>
        </td>
    </tr>
    <tr>
        <td align="right">挂起备注：</td>
        <td>
            <textarea id="is_pending_memo" style="width:90%;height:100px;"></textarea>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pending_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_pending_ok").click(function(){
            var params = {'app_fmt':'json',sell_record_code: <?php echo $request['sell_record_code']?>,is_pending_code:$('#is_pending_code').val(),is_pending_time:$('#is_pending_time').val(),'is_pending_memo':$('#is_pending_memo').val(),'batch':'<?php echo isset($request['batch'])?$request['batch']:''; ?>'};
            $.post("?app_act=oms/sell_record/opt_pending", params, function(data){
                if(data.status != "1"){
                    //alert(data.message);
                    BUI.Message.Alert(data.message, 'error')
                } else {
                	BUI.Message.Alert(data.message, 'info')
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                }
            }, "json")
        });
        var time = $.parseJSON('<?php echo json_encode($time); ?>');
        $("#is_pending_code").change(function(){ 
            $("#is_pending_time").val(time[$(this).val()]);
        });
        $("#is_pending_code").change();
    });

BUI.use('bui/calendar',function(Calendar){
        var datepicker = new Calendar.DatePicker({
            trigger:'.calendar',
            showTime:true,
            autoRender : true
        });
    });
</script>