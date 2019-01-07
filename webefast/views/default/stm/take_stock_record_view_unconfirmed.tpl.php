<style>
table{
    border-top: 1px solid #dddddd;
    border-left: 1px solid #dddddd;
}

table td,
table th{
    border-bottom: 1px solid #dddddd;
    border-right: 1px solid #dddddd;
    line-height:30px;
    text-align: center;
}


.bui-grid-table .bui-grid-hd{
    text-align: center;
}
</style>
<div class="panel record_table">
<div class="panel-header clearfix"><h3 class="pull-left">基本信息 <i class="icon-folder-open toggle"></i></h3></div>
</div>
<table class="bui-grid-table" cellspacing="0" cellpadding="0">
<tr>
<th class="bui-grid-hd">操作</th>
<th class="bui-grid-hd">单据类型</th>
<th class="bui-grid-hd">单据编号</th>
<th class="bui-grid-hd">业务日期</th>
<th class="bui-grid-hd">仓库</th>
</tr>
<?php foreach ($response['pur_purchaser'] as $pur_purchaser):?>
<tr>
<td><a href="?app_act=pur/purchase_record/view&purchaser_record_id=<?php echo $pur_purchaser['purchaser_record_id']?>" >查看</a></td>
<td>采购入库单</td>
<td><a href="?app_act=pur/purchase_record/view&purchaser_record_id=<?php echo $pur_purchaser['purchaser_record_id']?>" ><?php echo $pur_purchaser['record_code']?></a></td>
<td><?php echo $pur_purchaser['record_time']?></td>
<td><?php echo get_store_name_by_code($pur_purchaser['store_code'])?></td>
</tr>
<?php endforeach;?>

<?php foreach ($response['pur_return'] as $pur_return):?>
<tr>
<td><a href="?app_act=pur/return_record/view&return_record_id=<?php echo $pur_return['return_record_id']?>" >查看</a></td>
<td>采购退货单</td>
<td><a href="?app_act=pur/return_record/view&return_record_id=<?php echo $pur_return['return_record_id']?>" ><?php echo $pur_return['record_code']?></a></td>
<td><?php echo $pur_return['record_time']?></td>
<td><?php echo get_store_name_by_code($pur_return['store_code'])?></td>
</tr>
<?php endforeach;?>


<?php foreach ($response['stm_adjust'] as $stm_adjust):?>
<tr>
<td><a href="?app_act=stm/stock_adjust_record/view&stock_adjust_record_id=<?php echo $stm_adjust['stock_adjust_record_id']?>" >查看</a></td>
<td>仓库调整单</td>
<td><a href="?app_act=stm/stock_adjust_record/view&stock_adjust_record_id=<?php echo $stm_adjust['stock_adjust_record_id']?>" ><?php echo $stm_adjust['record_code']?></a></td>
<td><?php echo $stm_adjust['record_time']?></td>
<td><?php echo get_store_name_by_code($stm_adjust['store_code'])?></td>
</tr>
<?php endforeach;?>

<?php foreach ($response['store_out'] as $stm_adjust):?>
<tr>
<td><a href="?app_act=wbm/store_out_record/view&store_out_record_id=<?php echo $stm_adjust['store_out_record_id']?>" >查看</a></td>
<td>批发销货单</td>
<td><a href="?app_act=wbm/store_out_record/view&store_out_record_id=<?php echo $stm_adjust['store_out_record_id']?>" ><?php echo $stm_adjust['record_code']?></a></td>
<td><?php echo $stm_adjust['record_time']?></td>
<td><?php echo get_store_name_by_code($stm_adjust['store_code'])?></td>
</tr>
<?php endforeach;?>

<?php foreach ($response['wbm_return'] as $stm_adjust):?>
<tr>
<td><a href="?app_act=wbm/return_record/view&return_record_id=<?php echo $stm_adjust['wbm/return_record/view&return_record_id']?>" >查看</a></td>
<td>批发退货单</td>
<td><a href="?app_act=wbm/return_record/view&return_record_id=<?php echo $stm_adjust['return_record_id']?>" ><?php echo $stm_adjust['record_code']?></a></td>
<td><?php echo date("Y-m-d",strtotime($stm_adjust['record_time']))?></td>
<td><?php echo get_store_name_by_code($stm_adjust['store_code'])?></td>
</tr>
<?php endforeach;?>

<?php foreach ($response['stm_store_shift'] as $stm_adjust):?>
<tr>
<td><a href="?app_act=stm/store_shift_record/view&shift_record_id=<?php echo $stm_adjust['shift_record_id']?>" >查看</a></td>
<td>移仓单</td>
<td><a href="?app_act=stm/store_shift_record/view&shift_record_id=<?php echo $stm_adjust['shift_record_id']?>" ><?php echo $stm_adjust['record_code']?></a></td>
<td><?php echo date("Y-m-d",strtotime($stm_adjust['record_time']))?></td>
<td><?php echo get_store_name_by_code($stm_adjust['shift_out_store_code'])?></td>
</tr>
<?php endforeach;?>

</table>
<script>

</script>