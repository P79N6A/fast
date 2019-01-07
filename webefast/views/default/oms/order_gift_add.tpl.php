<div class="clearfix" style="text-align: left;">
所需要添加赠品的订单数：
<span id ="order_num"><?php echo $response['count'];?></span>
</div>
<table cellspacing="0" class="table table-bordered">
    <tr>
        <td align="right">添加赠品的商品条形码</td>
            <td>
                <input type="text" name="sku" id="sku" /><span style="color:red;">*</span>
            </td>
    </tr>
    <tr>
        <td align="right">赠品数量</td>
            <td>
                <input type="text" name="num" id="num" onchange="changeNum()" onkeyup="value=value.replace(/[^\d]/g,'') " onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))"/><span style="color:red;">*</span>
            </td>
    </tr>
    <tr>
        <td align="right">赠品数量</td>
            <td>
                <span id="sum_num"></span>
            </td>
    </tr>
</table>

<div id="u13" class="text" style="color:red;">
    <p><span style="font-family:'Applied Font Regular', 'Applied Font';">说明：</span></p>
    <p><span style="font-family:'Applied Font Regular', 'Applied Font';">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1、因赠品添加后强制占库存，所以请首先确保添加的赠品库存充足，否则无法扫描出库</span></p>
    <p><span style="font-family:'Applied Font Regular', 'Applied Font';">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2、请确保所选订单均需添加赠品，避免重复添加</span></p>
</div>

<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="add" onclick="submit()" >添加</button>
</div>

<script>
function changeNum () {
var sum_num = 0;
if($("#num").val() == '' || $("#num").val() == 0 )
sum_num = 0;
else
sum_num  = $("#num").val()*$("#order_num").html();

$("#sum_num").html(sum_num);
}

var  is_adding = 0;
function submit(){

    var sku = $("#sku").val();  
    var num = $("#num").val();

    if(sku == ''){

	    alert("赠品的商品条形码不能为空！");
	    return false;
        
    }
    if(num == ''){

        alert("赠品数量不能为空！");
        return false;
        
    }
    if(num <= 0){

        alert("赠品数量不能小等于0！");
        return false;
        
    }
    if(is_adding>0){
        alert("正在添加...");
         $('#add').attr('disabled',true);
        return false;   
    }
    
    is_adding++;
      $('#add').attr('disabled',true);
    var id = '<?php echo $response['id'] ?>';
    
    $.ajax({ type: 'POST', dataType: 'json',
        
    url: '<?php echo get_app_url('oms/order_gift/add_action');?>', data: {sku: sku , num:num , id:id},
    success: function(ret) {
        var type = ret.status;
        $('#add').attr('disabled',false);
        is_adding = 0;
        if(type == -1){
            BUI.Message.Alert(ret.message);
            return false;
        }

        if(type == 1){
            BUI.Message.Alert('增加成功！');
            location.reload();
        }
        
    }
    });
}

</script>
</html>