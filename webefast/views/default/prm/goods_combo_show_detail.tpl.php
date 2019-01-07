
          <table class='table_panel1' style="background-color: #f1f1f1;">
            <tr><td style="width:15%;">商品名称</td><td style="width:15%;">商品编码</td><td style="width:10%;"><?php echo $response['goods_spec1_rename'];?></td><td style="width:10%;"><?php echo $response['goods_spec2_rename'];?></td><td style="width:100px;">商品条形码</td><td style="width:80px;;">吊牌价</td><td style="width:80px;">数量</td><td style="width:100px;">操作</td></tr>
           <?php if(isset($response['diy']) && $response['diy'] <> ''){ ?>
            <?php foreach($response['diy'] as $k1=>$v1){ ?>
		  
		  <tbody id="tiaoma">
		     <tr><td ><?php echo $v1['goods_name'] ?></td><td ><?php echo $v1['goods_code'] ?></td>
		     <td > 
		     <select style="width:80px;" name="<?php echo 'spec1_code['.$v1['goods_combo_diy_id'].']'; ?>"  data-rules="{required : true}">
			       <?php foreach($v1['spec1_data'] as $k_s1=>$v_s1){ 
			       	     
			       	?>
			    	<option  value ="<?php echo $v_s1['spec1_code']; ?>" <?php if($v1['spec1_code'] == $v_s1['spec1_code']){ ?> selected <?php } ?> ><?php echo $v_s1['spec1_name']; ?></option>
			       <?php } ?>
			 </select>
		     </td>
		     <td >
		      <select style="width:80px;" name="<?php echo 'spec2_code['.$v1['goods_combo_diy_id'].']'; ?>"  data-rules="{required : true}">
			       <?php foreach($v1['spec2_data'] as $k_s2=>$v_s2){ 
			       	     
			       	?>
			    	<option  value ="<?php echo $v_s2['spec2_code']; ?>" <?php if($v1['spec2_code'] == $v_s2['spec2_code']){ ?> selected <?php } ?> ><?php echo $v_s2['spec2_name']; ?></option>
			       <?php } ?>
			 </select>
		     </td>
                 <input  name="<?php echo 'diy_combo_diy_price[' . $v1['goods_combo_diy_id'] . ']'; ?>" type="hidden"   value="<?php echo isset($v1['price']) ? $v1['price'] : '' ?>">
		     <td ><?php echo isset($v1['barcode'])?$v1['barcode']:'' ?></td><td ><?php echo isset($v1['sell_price'])?$v1['sell_price']:'' ?></td> <td><span >
		<input id= "" name="<?php echo 'diy_price['.$v1['goods_combo_diy_id'].']'; ?>" type="text" style="width:40px;" onblur="inputbarcord(this);" value="<?php echo $v1['num'] ?>">
		</span></td><td ><span onclick="del_diy('<?php echo $v1['goods_combo_diy_id'] ?>')">删除</span></td></tr>
		   
		    <?php } ?>
		    
		    <?php } ?>
		  </tbody >
		  </table>

