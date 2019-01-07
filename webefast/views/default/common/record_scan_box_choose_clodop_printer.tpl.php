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
            <?php if(in_array('auto_print_box',$response['printer'])){ ?>
                    get_list('b2b_box_printer','auto_print_box_printer');
            <?php } ?>
            <?php if(in_array('auto_print_jit_box',$response['printer'])){ ?>
                get_list('box_printer','auto_print_jit_box_printer');             
            <?php } ?>
            <?php if(in_array('auto_print_general_box',$response['printer'])){ ?>
                get_list('box_printer','auto_print_general_box_printer');                        
            <?php } ?>
            <?php if(in_array('auto_print_aggr_box',$response['printer'])){ ?>
                get_list('aggr_printer','auto_print_aggr_box_printer');             
            <?php } ?>
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
    function get_list(id,cookie){
        CLODOP.Create_Printer_List(document.getElementById(id));
        $("#"+id+" option").each(function () {
                if ($(this).text() == $.cookie(cookie)) {
                    $(this).attr("selected", "selected");
                }
        });
    }
    function do_Print() {
            <?php if(in_array('auto_print_box',$response['printer'])){ ?>
                $.cookie('auto_print_box_printer',$("#b2b_box_printer  option:selected").html(), {expires: 30});
            <?php } ?>
            <?php if(in_array('auto_print_jit_box',$response['printer'])){ ?>
                $.cookie('auto_print_jit_box_printer',$("#box_printer  option:selected").html(), {expires: 30});
            <?php } ?>
            <?php if(in_array('auto_print_general_box',$response['printer'])){ ?>
                $.cookie('auto_print_general_box_printer',$("#box_printer  option:selected").html(), {expires: 30});
            <?php } ?>
            <?php if(in_array('auto_print_aggr_box',$response['printer'])){ ?>
                $.cookie('auto_print_aggr_box_printer',$("#aggr_printer  option:selected").html(), {expires: 30});
            <?php } ?>
            parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
            parent.$(".bui-ext-mask").remove();
    }

</script>
<div class="print_setting" style="display:none">
    <table cellspacing="0" class="table table-bordered" style="margin-top: 15px;">        
        <tr <?php if(!in_array('auto_print_box',$response['printer'])){ ?> style="display:none;" <?php } ?>>
            <td width="30%" align="right">装箱单打印机：</td>
            <td width="70%">
                <select name="auto_print_box" id="b2b_box_printer"></select>
                <img title="选择快递单打印机，下次打印时默认为上次选择的打印机（关闭浏览器后清空）" alt="" src="assets/images/tip.png" width="25" height="25">
            </td>
        </tr>
        <tr <?php if(!in_array('auto_print_jit_box',$response['printer']) && !in_array('auto_print_general_box',$response['printer'])){ ?> style="display:none;" <?php } ?>>
            <td width="30%" align="right">箱唛打印机：</td>
            <td width="70%">
                <?php if(in_array('auto_print_jit_box',$response['printer'])){ ?>
                <select name="auto_print_jit_box" id="box_printer"></select>
                <?php }?>
                <?php if(in_array('auto_print_general_box',$response['printer'])){ ?>
                <select name="auto_print_general_box" id="box_printer"></select>
                <?php }?>
                <img title="选择快递单打印机，下次打印时默认为上次选择的打印机（关闭浏览器后清空）" alt="" src="assets/images/tip.png" width="25" height="25">
            </td>
        </tr>
        <tr <?php if( !in_array('auto_print_aggr_box',$response['printer']) ){ ?> style="display:none;" <?php }?>>
            <td width="30%" align="right">汇总单打印机：</td>
            <td width="70%">
                <select name="auto_print_aggr_box" id="aggr_printer"></select>
                <img title="选择快递单打印机，下次打印时默认为上次选择的打印机（关闭浏览器后清空）" alt="" src="assets/images/tip.png" width="25" height="25">
            </td>
        </tr>
    </table>
    <div class="clearfix" style="text-align: center;margin-top: 20px;">
        <button class="button button-primary" id="btn_pay_ok" onclick="do_Print()">确定</button>
    </div>
</div>
