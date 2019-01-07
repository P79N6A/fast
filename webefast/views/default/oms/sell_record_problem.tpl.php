<table cellspacing="0" class="table table-bordered">
    <tr>
        <td align="right">问题原因：</td>
        <td>
            <select id="problem_code">
                <?php
                $problem_map = oms_tb_all("base_question_label",array());
                foreach($problem_map as $v){
                    echo "<option value='{$v['question_label_code']}'>{$v['question_label_name']}</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td align="right">问题备注：</td>
        <td>
            <textarea id="problem_remark" style="width: 80%;"></textarea>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_problem_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_problem_ok").click(function(){
            var params = {'app_fmt':'json',sell_record_code: <?php echo $request['sell_record_code']?>,problem_code:$('#problem_code').val(),problem_remark:$('#problem_remark').val()};
            $.post("?app_act=oms/sell_record/opt_problem", params, function(data){
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