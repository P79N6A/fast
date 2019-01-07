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

<table class="table" id="table1" >
    <thead>
    <tr>
        <th>商品名称</th>
        <th>商品编码</th>
        <th>商品条形码</th>
        <th>唯一码</th>
        <th>检测站证书号</th>
        <th>主石重量</th>
        <th>辅石重量</th>
        <th>证书重量</th>
        <th  style="display:none">商品SKU</th>
        <th>商品数量</th>
        <th>扫描数量</th>
    </tr>
    </thead>

    <tbody id="record_detail">
        <?php 
        
        foreach($response['unique_list'] as $val): ?>
        <tr>
      
         <td><?php echo $val['goods_name']; ?></td>
          <td><?php echo $val['goods_code']; ?></td>
          <td><?php echo $val['barcode']; ?></td>
           <td><?php echo $val['unique_code']; ?></td>
            <td><?php echo $val['check_station_num']; ?></td>
             <td><?php echo $val['pri_diamond_weight']; ?></td>
                  <td><?php echo $val['ass_diamond_weight']; ?></td>
                       <td><?php echo $val['credential_weight']; ?></td>
                          <td class="sku" style="display:none"><?php echo $val['sku']; ?></td>
                          <td>1</td>
                          <td>1</td>
                          
                          

</tr>
 <?php endforeach;?>
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

<?php 

$sum = 0;
$scan = 0;
foreach ( $data['detail_list']  as $val){
    $sum+=$val['num'] ;
    $scan+=$val['scan_num'] ;
}

?> 

<script>
    $("#count_num").html('<?php echo $sum?>');
    $("#scan_num").html('<?php echo $scan?>');
    $("#diff_num").html('<?php echo $sum-$scan?>');
    var sell_record_code=<?php echo $data['record']['sell_record_code']?>;
    var count_num=<?php echo $sum;?>
    


function display_err_tips($msg){
	$("#msg").html($msg);
	$("#msg").show();
	$("#scan_barcode").val('');
	play_sound("error");
	setTimeout("$('#msg').hide()", 3000);
}

</script>




