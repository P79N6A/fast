
<style>
  .search{margin-top: 5%;}
 .place{margin: auto 30%;}
</style>
<div class="search">
    <lable class="control-label">选 择 店 铺</lable>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <select name="shop_code" id="shop_code" data-rules="{required : true}">
        <?php
        foreach ($response as $k => $v) {
            $row = array_values($v);
            ?>
            <option  value ="<?php echo $row[0]; ?>" ><?php echo $row[1]; ?></option>
        <?php } ?>
    </select>
</div>
<br />
<div class="place">
    <button  class="button button-primary" onclick="get_cloud_express_tpl()">下载</button>
</div>
<script>
    function get_cloud_express_tpl() {
        var shop_code = $('#shop_code').val();
        var params = {shop_code:shop_code};
        $.post("?app_act=sys/express_tpl/get_cloud_express_tpl", params, function (data) {
            if (data.status == 1) {
                parent.BUI.Message.Alert(data.message, 'success');
                ui_closePopWindow('<?php echo $request['ES_frmId'] ?>');
                window.location.reload();
            }else{
                parent.BUI.Message.Alert(data.message, 'error');
                ui_closePopWindow('<?php echo $request['ES_frmId'] ?>');
                window.location.reload();
            }
        }, "json")
    }
</script>	