<table class='table_panel1' >
           <tr><td style="width:15%;">商品名称</td><td style="width:15%;">商品编码</td><td style="width:10%;">规格1</td><td style="width:10%;">规格2</td><td style="width:80px;">商品条形码</td><td style="width:80px;;">出库数量</td><td>扫描数量</td></tr>
           <?php if(isset($response['goods']) && $response['goods'] <> ''){ ?>
            <?php foreach($response['goods'] as $k1=>$v1){ ?>
		  
		     <tr><td ><?php echo $v1['goods_name'] ?></td><td ><?php echo $v1['goods_code'] ?></td><td > <?php echo $v1['spec1_name'] ?></td>
		     <td ><?php echo $v1['spec2_name'] ?></td><td ><?php echo $v1['barcode'] ?></td><td ><?php echo $v1['out_num'] ?></td><td><font color="red"><span class="<?php echo "in_".$v1['barcode'] ?>" ><?php echo $v1['in_num'] ?></span></font></td></tr>
		   
		    <?php } ?>
		    
		    <?php } ?>
		
		  </table>