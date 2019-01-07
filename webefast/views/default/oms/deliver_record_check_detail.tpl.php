<style>
    .scan_num{font-size: 20px;}
    #scan_num{color: green;}
    #table1{font-size: 16px;}
    #table1 th{font-weight: normal;}
.sku_num{width: 50px;cursor:pointer;text-decoration:underline;}
.update_scan_num{width: 50px;cursor:pointer;text-decoration:underline;color:green;font-size: 20px}
</style>
<div class="row offset3">
    <div class="span6">
        <label class="control-label">商品数量：</label>
        <span class="controls" id="count_num"></span>
    </div>
    <div class="span6">
        <label class="control-label">扫描数量：</label>
        <span class="controls" id="scan_num">0</span>
    </div>
    <div class="span6">
        <label class="control-label">差异数量：</label>
        <span class="controls" id="diff_num">0</span>
    </div>
</div>

<table class="table" id="table1">
    <thead>
    <tr>
        <th>商品名称</th>
        <th>商品编码</th>
        <th>规格1</th>
        <th>规格2</th>
        <th  style="display:none">商品SKU</th>
        <th>商品条形码</th>
        <?php if ($data['lof_status']['lof_status'] == 1) { ?>
			<th style="width:90px;" >批次号</th>
		<?php } ?> 
        <th>商品数量</th>
        <th>扫描数量</th>
    </tr>
    </thead>
    <tbody>
    <?php $sum = 0; $scan = 0; foreach($data['detail_list'] as $key=>$val) {?>
    <tr>
        <td class="deliver_record_detail_id hide"><?php echo $val['deliver_record_detail_id']?></td>
        <td class="goods_name"><?php echo $val['goods_name']?></td>
        <td class="goods_code"><?php echo $val['goods_code']?></td>
        <td class="spec1_name"><?php echo $val['spec1_name']?></td>
        <td class="spec2_name"><?php echo $val['spec2_name']?></td>
     <td class="sku" style="display:none"><?php echo $val['sku']?></td>
        <td class="barcode"><?php echo $val['barcode']?></td>
		<?php if ($data['lof_status']['lof_status'] == 1) { ?>
				<td style="width:90px;" id="<?php echo  $val['barcode'].'_lof_no';?>"><?php echo $val['lof_no']?></td>
		<?php } ?>
        <td class="num"><?php echo $val['num']?></td>
        <td class="scan_num" width="10%"><?php echo "<span style='color:green;font-weight: bold;' id='sku_num_{$val['deliver_record_detail_id']}' onclick=update_num('{$val['deliver_record_detail_id']}') class=sku_num>{$val['scan_num']}</span>";?></td>
    </tr>
    <?php $sum += $val['num']; $scan += $val['scan_num']; }?>
    </tbody>
</table>
<hr>
<input type="hidden" id="deliver_record_id" value="<?php echo $data['record']['deliver_record_id'];?>">
<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="10%" align="right">订单号：</td>
        <td width="40%" id="sell_record_code"><?php echo $data['record']['sell_record_code'];?></td>
        <td width="10%" align="right">下单时间：</td>
        <td width="40%"><?php echo $data['record']['record_time'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">收货人：</td>
        <td width="40%"><?php echo $data['record']['receiver_name'];?></td>
        <td width="10%" align="right">手机：</td>
        <td width="40%"><?php echo $data['record']['receiver_mobile'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">固定电话：</td>
        <td width="40%"><?php echo $data['record']['receiver_phone'];?></td>
        <td width="10%" align="right">邮编：</td>
        <td width="40%"><?php echo $data['record']['receiver_zip_code'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">配送方式：</td>
        <td width="40%"><?php echo $data['record']['express_name'];?></td>
        <td width="10%" align="right">快递单号：</td>
        <td width="40%"><?php echo $data['record']['express_no'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">详细地址：</td>
        <td width="40%"><?php echo $data['record']['receiver_address'];?></td>
        <td width="10%" align="right">订单备注：</td>
        <td width="40%"><?php echo $data['record']['order_remark'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">买家留言：</td>
        <td width="40%"><?php echo $data['record']['buyer_remark'];?></td>
        <td width="10%" align="right">商家备注：</td>
        <td width="40%"><?php echo $data['record']['seller_remark'];?></td>
    </tr>
    <tr>
        <td width="10%" align="right">仓库留言：</td>
        <td width="40%" colspan="3"><?php //echo $data['record']['store_remark'];?></td>
    </tr>
</table>
<script>
    $("#count_num").html('<?php echo $sum?>')
    $("#scan_num").html('<?php echo $scan?>')
    $("#diff_num").html('<?php echo $sum-$scan?>')
    var sell_record_code=<?php echo $data['record']['sell_record_code']?>;
    var count_num=<?php echo $sum;?>

 //修改商品扫描数量
function update_num(deliver_record_detail_id) {
    var num = $("#sku_num_"+deliver_record_detail_id).text();
    $("#sku_num_"+deliver_record_detail_id).parent().html("<input type='text' style='color:green;font-weight: bold;width:40%' onblur=update_goods_scan_num('"+deliver_record_detail_id+"') id='sku_num_" + deliver_record_detail_id + "' class='update_scan_num'>");
    $("#sku_num_"+deliver_record_detail_id).focus();
    $("#sku_num_"+deliver_record_detail_id).val(num);
    $("#sku_num_"+deliver_record_detail_id).keypress(function(event){
        if(event.keyCode == 13) {
            update_goods_scan_num(deliver_record_detail_id);
        }
    });
}
    
   function update_goods_scan_num(deliver_record_detail_id) {
    var scan_num = $("#sku_num_"+deliver_record_detail_id).val();
    if(scan_num == '') {
        display_err_tips('扫描数量不能为空');
        return false;
    }
    var url = '?app_act=oms/deliver_record/update_goods_scan_num';
    var params = {app_fmt: 'json', sell_record_code: sell_record_code, deliver_record_detail_id: deliver_record_detail_id,scan_num:scan_num};
    $.post(url,params,function(data){
        if(data.status != 1) {
           display_err_tips(data.message);
        } else {
            $("#sku_num_"+deliver_record_detail_id).parent().html("<span style='color:green;font-weight: bold' onclick=update_num('"+deliver_record_detail_id+"') class='sku_num' id='sku_num_" + deliver_record_detail_id + "'>" + scan_num + "</span>");
            update_sum_scan_num();
            var check = false;
            //扫描数等于商品数直接发货
            submit_it_new(check);
        }
    },'json')
}

function display_err_tips($msg){
	$("#msg").html($msg);
	$("#msg").show();
	$("#scan_barcode").val('');
	play_sound("error");
	setTimeout("$('#msg').hide()", 3000);
}

//更新扫描总数
function update_sum_scan_num(){
	var sum_scan_num = 0;
	$('.sku_num').each(function(i,e){
            sum_scan_num+=parseInt($(this).text());
        })
        $("#scan_num").html(sum_scan_num);
        update_sum_diff_num(sum_scan_num)
}

//更新差异数
function update_sum_diff_num(sum_scan_num){
	$('#diff_num').html(count_num - sum_scan_num);
}

  function submit_it_new(check, is_record) {
        if (is_record === undefined) {
            is_record = 0;
        }
        
        var isOK = false
        var obj = $("#table1").find("tbody").find("tr")
        if(obj.length > 0){
            isOK = true
        }
		
        obj.each(function(index, item){
            var vNum = parseInt($(item).find(".num").text())
            var vScanNum = parseInt($(item).find(".scan_num").text())
            if(vScanNum != vNum){
                isOK = false
            }
        })
        if(isOK || check){
            var params = {is_record: is_record, deliver_record_id: $("#deliver_record_id").val(), sell_record_code: $('#sell_record_code_input').val()}
            $.post("?app_act=oms/deliver_record/check_action", params, function(data){
                if(data.status != 1){
                    BUI.Message.Alert(data.message,'error');
                } else {
                	if(unique_status == 1 && unique_flag == 1 && parseInt($("#scan_num").html())==parseInt($("#count_num").html())){
                		unique_code_log();
                    }
                    
                    params = {is_record: 0, deliver_record_id: $("#deliver_record_id").val(), sell_record_code: $('#sell_record_code_input').val()}
                    $.post("?app_act=oms/deliver_record/scan_clear", params, function(data){
                    }, "json");
                    set_bar_status(true);
                    $("#deliver_detail").html("")
                    $("#barcode").val("")
                    $("#express_no").val("")
                    $("#express_no").removeAttr("disabled")

                    $("#barcode").attr("disabled", true)
                    $("#btn-submit").attr("disabled", true)
                    $("#btn-clear").attr("disabled", true)
                    $("#express_no").focus();
                    
                    $("#msg").html("发货成功, 请继续");
                    play_sound("success");
                }
            }, "json");
        }
    }
</script>