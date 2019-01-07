<style type="text/css">
 .table_panel{
	width:800px;
 }
 .table_panel td {
    border-top: 0px solid #dddddd;
    line-height: 20px;
    padding: 10px;
    text-align: left;
    vertical-align: top;
}
.table_panel1 td {
    border:1px solid #dddddd;
    line-height: 20px;
    padding: 10px;
    text-align: left;
    vertical-align: top;
}

</style>
<?php render_control('PageHead', 'head1',
array('title'=>'套餐商品库存'));

?>
<ul class="nav-tabs oms_tabs">
  <?php foreach($response['store_data'] as $k=>$v){ ?>
    <li
     <?php if($response['store_code'] === $v['store_code'] ){ ?>
     		class="active"
     <?php } ?>
     ><a href="?app_act=prm/goods_combo/goods_inv&store_code=<?php echo $v['store_code'] ?>&_id=<?php echo $response['_id'] ?>"  ><?php echo $v['store_name'] ?></a></li>
  <?php } ?>
   <!--   <li class="active"><a href="#" >套餐明细</a></li>-->
   
</ul>
<br><br>
<table class='table_panel1' ><input type="hidden" id="goods_code" name="goods_code" value="<?php echo $response['goods_code'];?>">
  <tr>
  <td style="width:150px;">套餐名称</td>
  <td style="width:80px;">套餐编码</td>
  <td ><?php echo $response['goods_spec1_rename'];?></td>
  <td ><?php echo $response['goods_spec2_rename'];?></td>
  <td style="width:200px;"> 套餐条形码 </td>
  <td > 可用库存 </td>
  <td> 套餐价格(元) </td>
  
  </tr>
  <?php foreach($response['barcord'] as $k=>$v){ ?>
  <tr>
 <td style="width:20px;" ><span onClick="show_detail('<?php echo $v['sku'] ?>');"><i class="bui-grid-cascade-icon"> </i>&nbsp;&nbsp;</span> <?php echo $v['goods_code_name'] ?></td>
  <td><?php echo $v['goods_code'] ?> </td>
  <td><?php echo $v['spec1_code_name']."[".$v['spec1_code']."]" ?> </td>
  <td ><?php echo $v['spec2_code_name']."[".$v['spec2_code']."]" ?></td>
  <td ><?php echo $v['barcode'] ?></td>
  <td ><?php echo $v['diy_min'] ?> </td>
  <td > <?php echo $v['price'] ?></td>
  
  </tr>
   
 <?php } ?>
  
  
</table>
