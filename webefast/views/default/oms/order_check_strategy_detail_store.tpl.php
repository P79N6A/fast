<style type="text/css">

    .span8 .control-label {
        display: inline-block;
        float: left;
        line-height: 25px;
        text-align: left;
        width: 250px;
    }
</style>
<div class="demo-content">

    <div class="row">
        <div class="span24 doc-content">
            <form class="form-horizontal" onsubmit="return false;">
                <?php foreach ($response['store'] as $key => $store) { ?>
                    <div class="span8">
                        <label class="control-label">
                            <input type="checkbox" name="store" <?php foreach ($response['stored'] as $k => $s) { ?> <?php if ($store['store_code'] == $s['content']) echo "checked='checked'" ?><?php } ?> value="<?php echo $store['store_code']; ?>">
                            <?php echo $store['store_name']; ?>
                        </label>
                    </div>
                <?php } ?>
                <div class="form-actions" style="text-align:center">
                    <button id="shop_code_submit"  class="button button-primary">保存</button>
                    <button type="reset" class="button">重置</button>
                </div>	
            </form>
        </div>

    </div>

    <div>
        <span style="color:red;">
            提示：选择仓库后，该仓库对应的订单都不会自动确认。
        </span>
    </div>
</div>
<script type="text/javascript">
    $("#shop_code_submit").click(function() {
        var store_code_value = [];
        $('input[name="store"]:checked').each(function() {
            store_code_value.push($(this).val());
        });
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/order_check_strategy/store_do_add'); ?>', data: {store_code: store_code_value},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('设置成功', type);
                    location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    });


</script>