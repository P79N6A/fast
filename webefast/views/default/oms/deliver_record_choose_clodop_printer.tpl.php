<?php echo load_js('jquery.cookie.js'); ?>
<style>
    .print_msg_string{
        position: relative;
        left: 30%;top: 85%;
        font: normal bold 16px/20px arial,sans-serif;
    };
</style>
<script src='http://127.0.0.1:8000/CLodopfuncs.js'></script>
<script>
    $(document).ready(function () {
        initWebSocket();
    });

    var socket;
    var initWebSocket = function () {
        if (window.WebSocket) {
            socket = new WebSocket("ws://127.0.0.1:8000");
            socket.onopen = function (event) {
                $("#container").removeClass("page_container");
                 $(".print_setting").show();
                var default_clodop_printer = $.cookie('_clodop_printer');
                CLODOP.Create_Printer_List(document.getElementById('clodop_printer'));
                $("#clodop_printer option").each(function () {
                    if ($(this).text() == default_clodop_printer) {
                        $(this).attr("selected", "selected");
                    }
                });
            };
            socket.onerror = function (event) {
                var msg = '<h3>无法连接到Clodop打印组件</h3>' +
                    '<p style="text-align: center;"><a href="http://www.mtsoftware.cn/download/CLodop_Setup_for_Win32NT_2.112.zip">点击下载</a></p>';
                parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                parent.parent.BUI.Message.Alert(msg, 'error');
                return;
            };
        } else {
            parent.parent.BUI.Message.Alert('浏览器不支持打印控件', 'error');
        }
    }
    
    function do_Print() {
        $(".print_setting").attr("style", "display:none")
        $(".print_msg").attr("style", "display:block")
        var clodop_printer = $("#clodop_printer  option:selected").text();
        var record_ids = '<?php echo $request['record_ids'] ?>';//需要打印的订单号
        var waves_record_ids = '<?php echo $request['waves_record_ids'] ?>';
        var new_clodop_print = '<?php echo $request['new_clodop_print'] ?>';
        var is_print_express = '<?php echo $request['is_print_express'] ?>';
        var print_templates_code = '<?php echo $request['print_templates_code'] ?>';
        var frame_id = '<?php echo $request['frame_id'] ?>';
        var unable_printer = '<?php echo $request['unable_printer'] ?>';//是否弹出打印机
        var ES_frmId = "<?php echo $request['ES_frmId'] ?>";
        if (is_print_express === '1') {
            var url = "?app_act=oms/deliver_record/print_express&iframe_id=" + frame_id + '&clodop_printer=' + clodop_printer + '&new_clodop_print=' + new_clodop_print;
            url += "&deliver_record_ids=" + record_ids;
            if (waves_record_ids != '') {
                url += "&waves_record_ids=" + waves_record_ids;
            }
            if (unable_printer != '') {
                url += "&unable_printer=" + unable_printer;
            }
            var iframe = $('<iframe id="' + frame_id + ' width="0" height="0"></iframe>').appendTo('body');
            iframe.attr('src', url);
        } else if (print_templates_code == 'invoice_record') {//发票打印
            var u = '?app_act=sys/flash_print/do_print_td&template_id=31&model=oms/InvoiceRecordModel&typ=default&record_ids=' + record_ids;
            window.open(u);
        } else {
            var u = '?app_act=tprint/tprint/do_print&print_templates_code=' + print_templates_code + '&record_ids=' + record_ids + '&clodop_printer=' + clodop_printer + '&new_clodop_print=' + new_clodop_print;
            if(print_templates_code == "oms_waves_record_clothing"){
                u += "&print_type=50&frm="+ES_frmId;
            }
            $("#print_iframe").attr('src', u);
        }
        $.cookie('_clodop_printer', clodop_printer);
    }
           

</script>
<div class="print_setting" style="display:none">
    <table cellspacing="0" class="table table-bordered" style="margin-top: 15px;">
        <tr>
            <td width="30%" align="right">打印机：</td>
            <td width="70%">
                <select name="printer" id="clodop_printer"></select>
                <img title="选择快递单打印机，下次打印时默认为上次选择的打印机（关闭浏览器后清空）" alt="" src="assets/images/tip.png" width="25" height="25">
            </td>
        </tr>
    </table>
    <div class="clearfix" style="text-align: center;margin-top: 20px;">
        <button class="button button-primary" id="btn_pay_ok" onclick="do_Print()">确定</button>
    </div>
</div>
<div class="print_msg" style="display: none">
    <span class="print_msg_string">打印进行中，请勿关闭页面！</span>
</div>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>