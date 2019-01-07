<style>
    .cowry_desc{
        margin: 10px;
    }
    .cowry_desc #editor{
        width: 90%;
        height: 300px;
    }
</style>
<form class="form-horizontal" id="form_cowry_desc" action="?app_act=api/tb_issue/save_desc&tab_type=cowry_desc" method="post">
    <input type="hidden" name="shop_code" value="<?php echo $request['shop_code'] ?>">
    <input type="hidden" name="goods_code" value="<?php echo $request['goods_code'] ?>">
    <div class="cowry_desc">
        <textarea id="editor" name="desc" placeholder="请在这填写宝贝描述"><?php echo $response['cowry_desc']['desc'] ?></textarea>
    </div>
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button type="submit" class="button button-primary" id="submit">提交</button>
        </div>
    </div>
</form>
<script>
    $('#editor').focus();
    BUI.use('bui/form', function (Form) {
        new Form.HForm({
            srcNode: '#form_cowry_desc',
            submitType: 'ajax',
            callback: function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'success');
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }
        }).render();
    });
</script>