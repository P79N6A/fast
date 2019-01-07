<style>
    .anti_oversold{padding:20px;}
    .anti_oversold .row input[type='text']{width: 50px;}
    .anti_oversold .row{ margin-bottom:10px;}
    .anti_oversold select{ width:120px;}
</style>
<div class="anti_oversold">
    <div style="margin-bottom:15px;"><span id="msg">未启用防超卖预警配置，只有启用后才允许配置，启用请点击这里：</span><a href="#" id="anti_enable">启用</a></div>
    <div class="oversold" style="display:none">
        <div class="row"><label class="control-label">防超卖预警配置：</label></div>
        <div class="row shop_selector">
            当商品SKU可用库存低于(包含)：
            <input type="text" placeholder="" class="input warn_goods_val" value="" id="warn_goods_val1"/>，仅同步店铺&nbsp;
            <select class="input-small" name="shop" id="shop">
                <?php
                foreach ($response['shop'] as $shop_code => $shop_name) {
                    echo "<option value='{$shop_code}'>{$shop_name}</option>";
                }
                ?>
            </select>
            &nbsp;销售，当前策略其它店铺商品将下架。
            <!--<button type="button" class="button button-info" value="" id="btnOpenAuto">开启智能选择店铺</button>智能选择店铺暂停-->
        </div>
        <div class="row auto_shop_selector" style="display:none">
            当商品SKU可用库存低于(包含)：
            <input type="text" placeholder="" class="input warn_goods_val" value="" id="warn_goods_val2"/>，根据近 <input type="text" placeholder="" class="input" value="<?php echo $response['anti_oversold']['warn_goods_deliver_day']; ?>" id="warn_goods_deliver_day"/>天发货总量，智能选择销售店铺。
            <button type="button" class="button button-info" value="" id="btnCloseAuto">关闭智能选择店铺</button>
        </div>
        <div class="row form-actions actions-bar">
            <div class="span13 offset3 ">
                <button type="submit" class="button button-primary" id="submit">保存</button>
                <button type="reset" class="button">重置</button>
            </div>
        </div>
        <p style="color:red">
            注：可用库存是指所有已配置策略仓库的的可用库存合计。
        </p>
    </div>
</div>
<script>
    var warn_goods_sell_shop = "<?php echo $response['anti_oversold']['warn_goods_sell_shop'] ?>";
    var warn_goods_val = "<?php echo $response['anti_oversold']['warn_goods_val']; ?>";
    $(function () {
        var show_value = '<?php echo $response['anti_oversold_state']; ?>'; //防超卖预警开启状态
        var state = '<?php echo $response['anti_oversold']['is_smart_select']; ?>'; //智能选择店铺开启状态
        $("#shop option[value='" + warn_goods_sell_shop + "']").attr("selected", true);
        $(".warn_goods_val").val(warn_goods_val);
        set_autoshop(state);
        if (show_value == 1) {
            $(".oversold").attr("style", "display:block");
            $("#msg").html('已启用防超卖预警配置，停用请点击这里：');
            $("#anti_enable").html('停用');
        } else {
            $(".oversold").attr("style", "display:none");
            $("#msg").html('未启用防超卖预警配置，只有启用后才允许配置，启用请点击这里：');
            $("#anti_enable").html('启用');
        }
        if (scene == 'view') {
            if($("#anti_enable").text()=='启用'){
                $('#msg').parent().text('提示：防超卖预警未设置');
            }else{
                $('#msg').parent().remove();
            }
            $('.anti_oversold input').attr('disabled', 'disabled');
            $('.anti_oversold select').attr('disabled', 'disabled');
            $('.anti_oversold .actions-bar').remove();
            $('#btnOpenAuto').remove();
            $('#btnCloseAuto').remove();

        }
        $("#btnOpenAuto").click(function () {
            set_autoshop(1);
        });
        $("#btnCloseAuto").click(function () {
            set_autoshop(0);
        });
    });

    function set_autoshop(state) {
        $(".shop_selector").attr("style", state == 1 ? "display:none" : "display:block");
        $(".auto_shop_selector").attr("style", state == 1 ? "display:block" : "display:none");
        $("#btnOpenAuto").val(state);
        $("#btnCloseAuto").val(state == 1 ? 0 : 1);
        $(".warn_goods_val").val(warn_goods_val);
        $("#shop option[value='" + warn_goods_sell_shop + "']").attr("selected", true);
    }

    $("#anti_enable").click(function () {
        var anti_enable = $(this).text();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=op/inv_sync/update_anti_status',
            data: {value: anti_enable == '启用' ? 0 : 1,sync_code: '<?php echo $response['anti_oversold']['sync_code'] ?>'},
            success: function (ret) {
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type == 'success') {
                    if (anti_enable == '启用') {
                        $("#anti_enable").html('停用');
                    } else {
                        $("#anti_enable").html('启用');
                    }
                    get_tab('anti_oversold', 1);
                } else {
                    BUI.Message.Alert('启用失败！', 'error');
                }
            }
        });
    });

    $("#submit").click(function () {
        var sync_code = '<?php echo $response['anti_oversold']['sync_code'] ?>';
        var shop_code = $("#shop").val();
//        var state = $("#btnOpenAuto").val();
        var state = 0;
        var warn_goods_deliver_day = $("#warn_goods_deliver_day").val();
//        var warn_goods_val = state == '0' ? $("#warn_goods_val1").val() : $("#warn_goods_val2").val();//智能选择店铺暂停
        var warn_goods_val = $("#warn_goods_val1").val();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=op/inv_sync/save_oversold',
            data: {sync_code: sync_code, warn_goods_val: warn_goods_val, shop_code: shop_code, is_smart_select: state, warn_goods_deliver_day: warn_goods_deliver_day},
            success: function (ret) {
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, 'success');
                    get_tab('anti_oversold', 1);
                } else {
                    BUI.Message.Alert('保存失败！', 'error');
                }
            }
        });
    });
</script>