<table cellspacing="0" class="table table-bordered">
    <tr>
        <td>
            <textarea id="communicate_log" style="width:90%;height:100px;"></textarea>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_psending_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_psending_ok").click(function(){
            var communicate_log = $("#communicate_log").val();
            if (communicate_log.length <=0){
                BUI.Message.Alert('沟通日志不能为空','error');
                return;
            }
            var params = {'app_fmt':'json',sell_record_code: <?php echo $request['sell_record_code']?>,communicate_log:communicate_log};
           
            $.post("?app_act=oms/sell_record/opt_communicate_log", params, function(data){
                if(data.status != "1"){
                     BUI.Message.Alert(data.message,'error');
                } else {
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                }
            }, "json");
        });
    });

BUI.use('bui/calendar',function(Calendar){
    var datepicker = new Calendar.DatePicker({
        trigger:'.calendar',
        autoRender : true
    });
});    
</script>