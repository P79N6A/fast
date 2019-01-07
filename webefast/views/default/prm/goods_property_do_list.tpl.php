<?php require_lib('util/oms_util', true);?>
<script type="text/javascript" src="../../webpub/js/jquery-1.8.1.min.js"></script>
<style>
.calendar-time{width:100px;}
th{width:200px;}
</style>
<br/>
<div><button class="button">商品扩展属性</button></div>
<br/>
<div class="span25" id="panel_detail" >
<table class="table table-bordered">
<thead>
<tr><th style="text-align: center">操作</th><th style="text-align: center">扩展属性代码</th>
<th style="text-align: center">扩展属性名称</th><th style="text-align: center">扩展属性别名</th></tr>
</thead>
<tbody>

<?php foreach($response['data'] as $value){?>
<tr class="detail_<?php echo $value['property_set_id'] ?>">
<td style="text-align: center">
<button class="button button-small edit" onclick="detail_edit(<?php echo $value['property_set_id'] ?>)" title="编辑" style="display:inline-block;">
编辑</button>
<button class="button button-small save hide" onclick="detail_save(<?php echo $value['property_set_id'] ?>)" title="保存" style="display: none;">
保存
</button>
</td>
<td style="text-align: center"><?php if($value['property_set_id'] == 10) echo "0".$value['property_set_id']; else echo "00".$value['property_set_id']; ?></td><td style="text-align: center"><?php echo "扩展属性".$value['property_set_id'] ?></td>
<td style="text-align: center" name = "property_val_title">
<span style="display: inline;"><?php echo $value['property_val_title'] ?></span>
<input class="property_val_title" type="text" value="<?php echo $value['property_val_title'] ?>" style="width: 200px; display: none;">

</td></tr>
<?php }?>


</tbody>
</table>

</form>
</div>
<script>

//明细编辑
function detail_edit(id){
    var item = $("#panel_detail table tbody").find(".detail_"+id);
    item.find(".edit").hide();
    item.find(".save").show();
    item.find("td[name=property_val_title]").find("input").show();
    item.find("td[name=property_val_title]").find("span").hide();

}

//明细保存
function detail_save(id){
    var item = $("#panel_detail table tbody").find(".detail_"+id)

    var params = {
        "property_set_id": id,
        "property_val_title": item.find("td[name=property_val_title]").find("input").val()
    }
    //console.log(params);return;
    $.post("?app_act=prm/goods_property/opt_save_detail", params, function(data){
        if(data.status == 1){
            item.find(".edit").show();
            item.find(".save").hide();
            var value = item.find("td[name=property_val_title]").find("input").val();
            item.find("td[name=property_val_title]").find("input").hide();
            item.find("td[name=property_val_title]").find("span").show();
            item.find("td[name=property_val_title]").find("span").html(value);
//            location.reload();
            //刷新按钮权限
//            btn_check();
        } else {
            BUI.Message.Alert(data.message, 'error');
        }
    }, "json")
}

</script>