<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="30%" align="right">解挂时间：</td>
        <td width="70%">
            <input type="text" id="is_pending_time" value="" class="input-normal calendar"/>
        </td>
    </tr>
    <tr>
        <td align="right">挂起原因：</td>
        <td>
            <textarea id="is_pending_reason" style="width:90%;height:100px;"></textarea>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_psending_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_psending_ok").click(function(){
            var params = {'app_fmt':'json',sell_record_code: <?php echo $request['sell_record_code']?>,is_pending_time:$('#is_pending_time').val(),'is_pending_reason':$('#is_pending_reason').val()};
            $.post("?app_act=oms/sell_record/opt_psending", params, function(data){
                if(data.status != "1"){
                    alert(data.message)
                } else {
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                }
            }, "json")
        })
    });

BUI.use('bui/calendar',function(Calendar){
    var datepicker = new Calendar.DatePicker({
        trigger:'.calendar',
        autoRender : true
    });
});    
</script>