<?php echo load_js('jquery.cookie.js'); ?>
<table cellspacing="0" class="table table-bordered" style="margin-top: 15px;">
    <tr>
        <td width="30%" align="right">打印机：</td>
        <td width="70%">
            <select name="printer" id="cloud_printer">
                <?php foreach ($response['printer_list'] as $printer) { ?>
                    <option value="<?php echo $printer ?>" <?php if ($response['default_printer'] == $printer) {echo "selected=selected";} ?>><?php echo $printer ?></option>
                <?php } ?>
            </select>
            <img title="选择快递单打印机，下次打印时默认为上次选择的打印机（关闭浏览器后清空）" alt="" src="assets/images/tip.png" width="25" height="25">
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;margin-top: 20px;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>
<script>
    $(document).ready(function () {
        $("#container").removeClass("page_container");
        $("#btn_pay_ok").click(function () {
            print_express_name = <?php echo "'".$request['print_express_name']."'" ?> //现在打印的快递名称
            $.cookie(print_express_name, $("#cloud_printer").val()); //保存现在打印的快递名称
            $.cookie('could_printer',$("#cloud_printer").val());
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
        })
    })
</script>