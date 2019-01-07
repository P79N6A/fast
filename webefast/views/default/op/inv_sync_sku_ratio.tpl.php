<style>
    .anti_oversold{padding:20px;}
    .anti_oversold .row input[type='text']{width: 50px;}
    .anti_oversold .row{ margin-bottom:10px;}
    .anti_oversold select{ width:120px;}
</style>
<div class="anti_oversold">
    <div class="oversold" style="">
        <div class="row"><label class="control-label">防超卖预警配置：</label></div>
        <div class="row shop_selector">
            当商品条码：<?php echo $response['goods_info']['barcode'];?> 可用库存低于(包含)：
            <input type="text" placeholder="" class="input warn_goods_val" value="" id="warn_sku_val"/>，仅同步店铺&nbsp;
            <select class="input-small" name="shop" id="shop">
                <option value=''>请选择</option>
                <?php
                $is_only = 0;
                foreach ($response['shop'] as $shop_code => $shop_name) {
                    $selected = '';
                    if ($is_only == 0) {
                        foreach ($response['warn_sku'] as $warn) {
                            if ($warn['shop_code'] == $shop_code && $is_only == 0) {
                                $selected = 'selected';
                                $is_only = 1;
                                break;
                            }
                        }
                    }
                    echo "<option value='{$shop_code}' {$selected}>{$shop_name}</option>";
                }
                ?>
            </select>
            &nbsp;销售，当前策略中其它店铺商品将同步0库存下架。
        </div>
        <div class="row form-actions actions-bar">
            <div class="span13 offset3 ">
                <button type="submit" class="button button-primary" id="submit">保存</button>
                <button type="reset" class="button" id="init">重置</button>
            </div>
        </div>
        <p style="color:red">
            注：可用库存是指所有已配置策略仓库的的可用库存合计。商品条码预警设置优先级高于全局预警设置。
        </p>
    </div>
</div>
<script>
    var scene='<?php echo $app['scene'] ?>';
    get_warn_info();
    //$("#shop").change(function () {
    //    get_warn_info();
    //});

    function get_warn_info() {
        var shop_code = $("#shop").val();
        if (shop_code == '') {
            $("#warn_sku_val").val(0);
            return;
        }
        var sync_code = '<?php echo $response['sync_code'] ?>';
        var sku = '<?php echo $response['sku'] ?>';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=op/inv_sync/get_sku_warn',
            data: {'sync_code': sync_code, 'shop_code': shop_code, 'sku': sku},
            success: function (ret) {
                var warn_val = 0;
                if (ret.status == 1) {
                    warn_val = ret.data.warn_sku_val;
                }
                $("#warn_sku_val").val(warn_val);
            }
        });
    }
    
    
    
    $("#submit").click(function () {
        var sync_code = '<?php echo $response['sync_code'] ?>';
        var shop_code = $("#shop").val();
        if (shop_code == '') {
            BUI.Message.Alert('请选择店铺！', 'error');
            return;
        }
        var warn_sku_val = $("#warn_sku_val").val();
        if (warn_sku_val <= 0) {
            BUI.Message.Alert('请输入正数！', 'error');
            return;
        }
        var sku = '<?php echo $response['sku'] ?>';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=op/inv_sync/save_warn_sku',
            data: {sync_code: sync_code, warn_sku_val: warn_sku_val, shop_code: shop_code, sku: sku},
            success: function (ret) {
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, 'success');
                } else {
                    BUI.Message.Alert('保存失败！', 'error');
                }
            }
        });
    });

    $(function () {
        if (scene == 'view') {
            $('#warn_sku_val').attr('disabled', 'disabled');
            $('#shop').attr('disabled', 'disabled');
            $('#submit').attr('disabled', 'disabled');
            $('#init').attr('disabled', 'disabled');
        }
    });
</script>