<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="15%" align="right">仓库：</td>
        <td width="35%">
            <select name="store_code" id="store_code">
                <?php $list = oms_tb_all('base_store', array('status'=>1)); foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['store_code']?>"><?php echo $v['store_name']?></option>
                <?php } ?>
            </select>
        </td>
        <td width="15%" align="right">库位：</td>
        <td width="35%">
            <input type="text" name="shelf_code" id="shelf_code" value="">
        </td>
    </tr>
    <tr>
        <td colspan="4"><div id="msg" style="padding: 5px; color: #ff0000;"></div></td>
    </tr>
</table>

<script>
    $(document).ready(function(){
        $("#shelf_code").keyup(function(event){
            if (event.keyCode == 13) {
                var params = {
                    "store_code": $("#store_code").val(),
                    "shelf_code": $("#shelf_code").val()
                };

                $("#store_code").attr("disabled", true)
                $("#shelf_code").attr("disabled", true)
                $.post("?app_act=prm/goods_shelf/scanning_unbind_action", params, function(data){
                    $("#msg").html(data.message);
                    $("#store_code").removeAttr("disabled")
                    $("#shelf_code").removeAttr("disabled")
                }, "json")
            }
        })
    })
</script>