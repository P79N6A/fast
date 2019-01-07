<div class="clearfix" style="text-align: left;">
所需要删除赠品的订单数：
<span id ="order_num"><?php echo $response['count'];?></span>
</div>

<table cellspacing="0" class="table table-bordered">
    <tr>
        <td align="right">删除赠品的商品条形码</td>
            <td>
                <input type="text" name="sku" id="sku" /><span style="color:red;">*</span>
            </td>
    </tr>
</table>

<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" onclick="submit()" >删除</button>
</div>

<script>

function submit(){

    var sku = $("#sku").val();  
    if(sku == ''){
        alert("赠品的商品条形码不能为空！");
        return false;    
    }
        
    var id = '<?php echo $response['id'] ?>';
    
    $.ajax({ type: 'POST', dataType: 'json',
        
    url: '<?php echo get_app_url('oms/order_gift/delete_action');?>', data: {sku: sku , id:id},
    success: function(ret) {
        var type = ret.status;

        if(type == -1){
            BUI.Message.Alert('该该商品未启用！');
            return false;
        }
        if(type == -2){
            BUI.Message.Alert('订单无此赠品！');
            return false;
        }
        if(type == 1){
            BUI.Message.Alert('删除成功！');
            location.reload();
        }
        
    }
    });
}

</script>
</html>