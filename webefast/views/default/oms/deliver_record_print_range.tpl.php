<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="50%" align="right" style="font-size:12px;">请设置要打印<?php echo $response['type']?>的订单序号范围：</td>
        <td width="50%">
            <input type="text" id="min" name="min" value="" style="width:60px;">
            ~
            <input type="text" id="max" name="max" value="" style="width:60px;">
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    提示：如设置20~100，则只打印订单序号为20至100的订单，包含20、100<br><br>
</div>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">打印<?php echo $response['type']?></button>
</div>

<script>
    var printType = "<?php echo $request['print_type']?>"
    var wavesRecordId = <?php echo $request['waves_record_id']?>;
    var deliver_template_print = "<?php echo $response['print_delivery_record_template'];?>";
    var new_clodop_print = "<?php echo $response['new_clodop_print'];?>";
    function print_default(t, ids) {
        var url = "?app_act=sys/danju_print/do_print_record&app_page=null"
        url += "&print_data_type="+t
        url += "&record_ids="+ids
        var window_is_block = window.open(url)
        if (null == window_is_block) {
            alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口")
        }
    }

    $(document).ready(function(){
        $("#btn_pay_ok").click(function(){
            var min = $("#min").val()
            var max = $("#max").val()
            if(min == "" || max == "") {
                return
            }
            var params = {
                "waves_record_id": <?php echo $request['waves_record_id']?>,
                "print_type": printType,
                "min": min,
                "max": max
            };
            $.post("?app_act=oms/deliver_record/print_range_action", params, function(data){
                //BUI.Message.Alert(data.message, 'info')
                if(printType == "express") {
                    parent.print_express(data.toString(), 0, 0);
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                } else if (printType == "deliver") {
                    parent.print_sellrecord(data.toString(), 0, 0);
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                }
            }, "json")
        })
    })
</script>

<!-- 打印快递单公共文件 -->
<?php //include_once (get_tpl_path('oms/print_express'));
?>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>