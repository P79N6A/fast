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
        var new_clodop_print = '<?php echo $request['new_clodop_print'] ?>';
        var list_type = '<?php echo $request['list_type']  ?>';
        var frame_id = '<?php echo $request['frame_id'] ?>';
        var url = '';
        var iframe = '';
        if(list_type === '1') { //入库单打印条码
            url = "?app_act=sys/record_templates/print_barcode&iframe_id=" + frame_id + "&record_ids=" + record_ids + '&clodop_printer=' + clodop_printer + '&new_clodop_print=' + new_clodop_print + "&list_type=1";
            iframe = $('<iframe id="' + frame_id + ' width="0" height="0"></iframe>').appendTo('body');
        } else if(list_type === '2') {//条码打印条码
            url = "?app_act=sys/record_templates/print_barcode&iframe_id=" + frame_id + "&record_ids=" + record_ids + '&clodop_printer=' + clodop_printer + '&new_clodop_print=' + new_clodop_print + "&list_type=2";
            iframe = $('<iframe id="' + frame_id + ' width="0" height="0"></iframe>').appendTo('body');
        }   else if(list_type === '3') {//条码打印条码
            url = "?app_act=sys/record_templates/print_barcode&iframe_id=" + frame_id + "&record_ids=" + record_ids + '&clodop_printer=' + clodop_printer + '&new_clodop_print=' + new_clodop_print + "&list_type=3";
            iframe = $('<iframe id="' + frame_id + ' width="0" height="0"></iframe>').appendTo('body');
        }
        iframe.attr('src', url);
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