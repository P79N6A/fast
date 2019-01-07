<div>流程：创建盘点单-> 一键盘点-> 生成调整单-> 增加库存</div>
<br />
<table>
    <tr>
        <td>仓库&nbsp;&nbsp;&nbsp;</td>
        <td>
            <select id="store_code">
                <?php
                foreach ($response['store'] as $store) {
                    echo "<option value='{$store['store_code']}'>{$store['store_name']}</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>盘点类型&nbsp;&nbsp;&nbsp;</td>
        <td>
            <select>
                <option id="take_type"></option>
            </select>
        </td>
    </tr>
</table>
<div style="margin-top: 10px;">
    <div>当前初始化商品总数：<?php echo $response['data']['goods_type']; ?></div>
    <div>当前初始化商品总库存：<span id="stock_num"><?php echo $response['data']['num']; ?></span></div>
</div>
<div class="row form-actions actions-bar">
    <div class="span13 offset3 ">
        <button type="submit" class="button button-primary" id="submit">开始生成</button>
        <button type="reset" class="button " id="reset">取消</button>
    </div>
</div>
<div>
    <span id="msg" style="color: red;"></span>
</div>

<script>
    $(function(){
        $("body").attr('style','height:100%;width:400px;');
         $("#container").attr('style','height:170px;width:360px;'); 
        var type = '<?php echo $response['type'];?>';
        if(type === 'one_key'){
            $('#take_type').val('all');
            $('#take_type').html('全盘');
        }
        if(type === 'batch'){
            $('#take_type').val('part');
            $('#take_type').html('部分盘点（SKU级）');
        }
    });
    $("#reset").click(function(){
        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
    });
    $("#submit").click(function () {
        $("#msg").html('库存初始化中，请稍后.....');
        $("#submit").attr('disabled','disabled');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('prm/goods_init/do_stock_init'); ?>',
            data: {store_code: $("#store_code").val(), num: $("#stock_num").html(),type: $('#take_type').val(),api_goods_sku_id : '<?php echo $response['api_goods_sku_id'];?>', batch:'<?php echo $response['type'];?>'},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                   $("#msg").html('');
                   BUI.Message.Alert('库存初始化成功！', type);
                   window.location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    });
</script>