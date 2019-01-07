<style>
   .page_container {padding: 0 1%;}
   .table,.table tr,.table td{border: 0;}
   .sms_info{width: 95%;height: 100px;}
</style>
<form id="form1" method="post" action="?app_act=op/sms_tpl/opt_send_test" tabindex="0" style="outline: none;">
    <div class="panel">
        <div class="panel-body table_sms_tpl" id="table_sms_tpl">
            <input type="hidden" name="id" value="<?php echo isset($request['_id']) ? $request['_id'] : '';?>">
            <table cellspacing="0" class="table table-bordered" >
                <tbody>
                    <tr>
                        <td class="first_td">手机号码：</td>
                        <td>
                            <input class="bui-form-field" type="text" name="tel" data-rules="{required : true}" value="<?php echo isset($request['tel']) ? $request['tel'] : ''; ?>">
                            <b style="color:red"> *</b>
                        </td>
                        <td class="first_td">选择订单：</td>
                        <td>
                            <input class="bui-form-field" type="text" name="sell_record_code" id="sell_record_code" data-rules="{required : true}" value="" disabled="disabled">
                            <a href='#' id = 'select_order'><img src='assets/img/search.png'></a>
                        </td>
                    </tr>
                    <tr>
                        <td class="first_td">内容预览：</td>
                        <td colspan="3">
                            <textarea id="sms_info" name="sms_info" class="bui-form-field sms_info" data-rules="{required : true}"><?php echo (isset($request['sms_sign']) && '' != $request['sms_sign']) ? '【'.$request['sms_sign'].'】' : '';echo isset($request['sms_info']) ? $request['sms_info'] : ''; ?></textarea>
                            <b style="color:red"> *</b>
                        </td>
                    </tr>
                </tbody>
            </table>
            
        </div>
        
    </div>
    <div style="text-align: center;">
        <button class="button button-primary" type="submit" id='btn_send_test'>发送测试</button>&nbsp;&nbsp;<b style="color:red"> *</b>测试短信发送成功也会扣点哦
    </div>
</form>
<script>

$(function(){
    
    //选择订单
    $('#select_order').click(function () {
        select_order();
    });
});
    
    var old_data = <?php echo json_encode($request)?>;//原始数据
    //表单提交事件
    var ES_frmId = '<?php echo $request['ES_frmId']; ?>';
    BUI.use('bui/form', function (Form) {
        var form1 = new BUI.Form.HForm({
            srcNode: '#form1',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status != '1') {
                    BUI.Message.Alert(data.message, 'error');
                }else{
                    BUI.Message.Alert(data.message, 'success');
                }
                 return;
            }
        }).render();
        form1.on('beforesubmit', function () {
            //验证
            return true;
        });
    });
    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: true,
            autoRender: true
        });
    });
    //选择已发货订单
    parent.add_c = function (row) {
        $('#sell_record_code').val(row.sell_record_code);
        change_sms_tpl_val(row);
    };
    function select_order(){
        new ESUI.PopWindow("?app_act=op/sms_tpl/select_order", {
            title: "选择已发货订单",
            width: 960,
            height: 500,
            onBeforeClosed: function () {
            },
            onClosed: function () {
            }
        }).show();
    }
    //替换预览内容变量
    function change_sms_tpl_val(row){
        var params = {row:row,sms_info:old_data.sms_info};
        $.post("?app_act=op/sms_tpl/replace_tpl_var", params, function (ret) {
                if (ret.status == 1) {
                    var new_sms_info = '';
                    if ('' != old_data.sms_sign){
                        new_sms_info += '【' + old_data.sms_sign + '】';
                    }
                    new_sms_info += ret.data;
                    $('#sms_info').val(new_sms_info);
                }else{
                    BUI.Message.Alert(ret.message, 'error');
                }
            }, "json");
    }
</script>