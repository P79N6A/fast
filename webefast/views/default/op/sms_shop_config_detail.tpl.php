
<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    #panel_baseinfo input{width: 140px;}
    #panel_baseinfo select{width: 145px;}
    .sear_ico{ top:3px;}
    .form-horizontal .controls {margin-left: 0px;float:none;}
    .shdz .valid-text{ display:inline-block; width:194px;}
    .panel{margin-bottom: 20px;}
    .tr_th{background-color: #f5f5f5;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '店铺短信参数设置',
    'links' => array(),
    'ref_table' => 'table'
));
?>
<form id="form1" method="post" action="?app_act=op/sms_shop_config/add_detail" tabindex="0" style="outline: none;">
    <input type="hidden" name="shop_code" value="<?php echo $request['_id'];?>">
    <input type="hidden" name="is_active" value="<?php echo isset($response['sms_shop_config']['is_active']) ? $response['sms_shop_config']['is_active']:'';?>">
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">基本信息</h3>
            <div class="pull-right"></div>
        </div>
        <div class="panel-body" id="panel_baseinfo">
            <table cellspacing="0" class="table table-bordered">
                <tbody>
                    <tr>
                        <td>店铺名称：</td>
                        <td><?php echo isset($response['shop_info']['shop_name']) ? $response['shop_info']['shop_name'] : '';?></td>
                        <td>平台：</td>
                        <td><?php echo isset($response['shop_info']['sale_channel_name']) ? $response['shop_info']['sale_channel_name'] : '';?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">
            <table cellspacing="0" class="table table-bordered">
                <tbody>
                    <tr class="tr_th">
                        <th>参数项</th>
                        <th>参数值</th>
                        <th>说明</th>
                    </tr>
                    <tr>
                        <td>启用时间</td>
                        <td><input name="enable_time" id="enable_time" class="input-normal calendar calendar-time bui-form-field-date bui-form-field" value="<?php echo isset($response['sms_shop_config']['enable_time']) ? $response['sms_shop_config']['enable_time']: date('Y-m-d H:i:s');?>" aria-disabled="false" aria-pressed="false" type="text"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>发送时间</td>
                        <td>
                            <input id="send_start_time" type="text" value="<?php echo isset($response['sms_shop_config']['send_start_time']) ? $response['sms_shop_config']['send_start_time']:'';?>" name="send_start_time" placeholder="时间格式如08:08">
                            ~<input id="send_end_time" type="text" value="<?php echo isset($response['sms_shop_config']['send_end_time']) ? $response['sms_shop_config']['send_end_time']:'';?>" name="send_end_time" placeholder="时间格式如08:08">
                        </td>
                        <td>该参数影响短信任务发送，在该时间段内才会发送任务列表中待发送信息</td>
                    </tr>
                    <tr>
                        <td>发送订单类型</td>
                        <td>
                            <input id="order_checkbox" type="checkbox" value="0" name="order_type[]" <?php echo (isset($response['sms_shop_config']['order_type']) && strpos($response['sms_shop_config']['order_type'], '0') !== false) ? 'checked':'';?>>&nbsp;<label for="order_checkbox">普通订单</label>&nbsp;
                            <input id="fx_order_checkbox" type="checkbox" value="2" name="order_type[]" <?php echo (isset($response['sms_shop_config']['order_type']) && strpos($response['sms_shop_config']['order_type'], '2') !== false) ? 'checked':'';?>>&nbsp;<label for="fx_order_checkbox">网络分销订单</label>&nbsp;
                            <input id="tfx_order_checkbox" type="checkbox" value="1" name="order_type[]" <?php echo (isset($response['sms_shop_config']['order_type']) && strpos($response['sms_shop_config']['order_type'], '1') !== false) ? 'checked':'';?>>&nbsp;<label for="tfx_order_checkbox">淘分销订单</label>&nbsp;
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>备注</td>
                        <td>
                            <textarea id="remark" style="width: 99%; height: 39px;" name="remark" value='<?php echo isset($response['sms_shop_config']['remark']) ? $response['sms_shop_config']['remark']:'';?>' class="bui-form-field" aria-disabled="false" aria-pressed="false" placeholder="请输入备注"></textarea>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">
            <table cellspacing="0" class="table table-bordered">
                <tbody>
                    <tr class="tr_th">
                        <th>业务内容</th>
                        <th>是否启用</th>
                        <th>短信模板</th>
                    </tr>
                    <tr>
                        <td>发货通知模板</td>
                        <td>
                            <input id="delivery_notice_status_close" type="radio" value="0" name="delivery_notice_status" <?php echo (isset($response['sms_shop_config']['delivery_notice_status']) && $response['sms_shop_config']['delivery_notice_status'] == 1) ? '':'checked';?>>&nbsp;<label for="delivery_notice_status_close">关闭</label>&nbsp;
                            <input id="delivery_notice_status_open" type="radio" value="1" name="delivery_notice_status" <?php echo (isset($response['sms_shop_config']['delivery_notice_status']) && $response['sms_shop_config']['delivery_notice_status'] == 1) ? 'checked':'';?>>&nbsp;<label for="delivery_notice_status_open">开启</label>&nbsp;
                        </td>
                        <td>
                            <select id="" name="delivery_notice_tpl_id">
                                <?php if (isset($response['select']['delivery_notice'])){
                                foreach ($response['select']['delivery_notice'] as $key => $value) {
                                ?>
                                <option value="<?php echo $key;?>" <?php echo (isset($response['sms_shop_config']['delivery_notice_tpl_id']) && $response['sms_shop_config']['delivery_notice_tpl_id'] == $key) ? 'selected':'';?>><?php echo $value;?></option>
                            <?php }}?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="text-align: center;">
        <button class="button button-primary" type="submit">保存</button>&nbsp;&nbsp;
        <button class="button button-primary" type="reset" id='btn_reset'>重置</button>&nbsp;&nbsp;
        <button class="button button-primary" type="button" onclick="close_page()">取消</button>
    </div>
</form>
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#btn_reset').click(function(){
            reset_init();
        });
    });
    var old_data = <?php echo json_encode($response['sms_shop_config'])?>;
    var ES_frmId = '<?php echo $request['ES_frmId']; ?>';
    var flag = true;
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
            if ($("input[name=shop_code]").val() == '') {
                BUI.Message.Alert('店铺参数缺失', 'error');
                return false;
            }
            var preg_time = /^([01][0-9]|2[0-3]):([0-5][0-9])$/;
            var send_start_time = $("input[name=send_start_time]").val();
            if (send_start_time !== '' && null === send_start_time.match(preg_time)) {
                BUI.Message.Alert('请输入正确的发送时间，如08:08', 'error');
                return false;
            }
            var send_end_time = $("input[name=send_end_time]").val();
            if (send_end_time !== '' && null === send_end_time.match(preg_time)) {
                BUI.Message.Alert('请输入正确的发送时间，如08:08', 'error');
                return false;
            }
            if ((send_start_time === '' && send_end_time !== '') || (send_start_time !== '' && send_end_time === '')) {
                BUI.Message.Alert('发送时间错误, 起始时间和结束时间必须同时设置', 'error');
                return false;
            }
            if (send_start_time !== '' && send_end_time !== '' && send_end_time <= send_start_time) {
                BUI.Message.Alert('发送时间错误, 结束时间必须大于起始时间', 'error');
                return false;
            }
            return flag;
        });
    });

    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: true,
            autoRender: true
        });
    });
function close_page(){
    $(window.parent.document).find(".tab-nav-actived .tab-item-close").click();
}
//还原修改前设置
function reset_init(){
    if ('undefined' != typeof(old_data.delivery_notice_status) && 1 == old_data.delivery_notice_status){
        $('#delivery_notice_status_open').attr("checked","checked");
    }else{
        $('#delivery_notice_status_close').attr("checked","checked");
    }
    if ('undefined' != typeof(old_data.remark)){
        $('#remark').text(old_data.remark);
    }
}
</script>