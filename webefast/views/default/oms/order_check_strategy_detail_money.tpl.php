<style type="text/css">
    .form-horizontal{padding: 10px;}
    body{overflow: hidden;}
    .control-group{margin-bottom: 20px;}
    .controls input{width: 80px;text-align: center;height: 23px;}
    .controls b{margin: 5px;}
    .form-actions {text-align: center;}
</style>
<div class="form-horizontal">
    <div class="row">
        <div class="control-group">
            <div class="span8 controls">
                <input type="text" id="min_money" name="min_money" class="input-normal bui-form-field" value="<?php echo $response['data']['min_money']; ?>">
                <b><</b><span><b>订单金额</b></span><b><=</b>
                <input type="text" id="max_money" name="max_money" class="input-normal bui-form-field" value="<?php echo $response['data']['max_money']; ?>">
            </div>
        </div>
    </div>
    <div>
        <span style="color:red;margin: 10px;">提示：1、此金额范围外的订单不会自动确认<br><b style="margin-left: 49px;"></b>2、若为空则订单自动确认时不做校验</span>
    </div>
</div>

<div class="row form-actions actions-bar">
    <div class="span8 offset3">
        <button class="button button-primary" onclick="submit()">保存</button>
        <button type="reset" class="button">重置</button>
    </div>
</div>


<script type="text/javascript">
    var min_money = '<?php echo $response['data']['min_money']; ?>';
    var max_money = '<?php echo $response['data']['max_money']; ?>';

    function submit() {
        var new_min_money = $("#min_money").val().trim();
        var new_max_money = $("#max_money").val().trim();
        if (new_min_money == min_money && new_max_money == max_money) {
            BUI.Message.Tip("金额未修改,无需更新", 'warning');
            return false;
        }
        if (new_min_money != '') {
            new_min_money = parseFloat(new_min_money);
        }
        if (new_max_money != '') {
            new_max_money = parseFloat(new_max_money);
        }
        var reg =  /^\d+(.\d+)?$/;
        if ((new_min_money != '' && !reg.test(new_min_money)) || (new_max_money != '' && !reg.test(new_max_money))) {
            BUI.Message.Tip("金额必须为正数", 'warning');
            return false;
        }
        if (new_min_money != '' && new_max_money != '' && new_min_money >= new_max_money) {
            BUI.Message.Tip('最大金额必须大于最小金额', 'warning');
            return false;
        }
        var param = {min_money: new_min_money, max_money: new_max_money};
        $.post("?app_act=oms/order_check_strategy/strategy_detail_set", {param: param, strategy_code: 'not_auto_confirm_with_money'}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Tip("设置成功", 'success');
                ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                parent.refreshlist.load();
            } else {
                BUI.Message.Tip(ret.message, 'error');
            }
        }, "json");
    }


</script>