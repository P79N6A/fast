
          <table class='table_panel1' >
           <tr><td style="width:15%;">商品名称</td><td style="width:15%;">商品编码</td><td style="width:10%;">规格1</td><td style="width:10%;">规格2</td><td style="width:80px;">商品条形码</td><td style="width:80px;;">吊牌价</td><td>数量</td><td>操作</td></tr>
           <?php if(isset($response['diy']) && $response['diy'] <> ''){ ?>
            <?php foreach($response['diy'] as $k1=>$v1){ ?>
		  
		  <tbody id="tiaoma">
		  
		     <tr><td ><?php echo $v1['goods_code'] ?></td><td ><?php echo $v1['goods_code'] ?></td><td > <?php echo $v1['spec1_code_name'] ?></td>
		     <td ><?php echo $v1['spec2_name'] ?></td><td >(元)</td><td ></td><td><span >
		<input id= "" name="<?php echo 'diy['.$v1['goods_diy_id'].']'; ?>" type="text" style="width:40px;" onblur="inputbarcord(this);" value="<?php echo $v1['num'] ?>">
		</span></td><td ><span onclick="del_diy('<?php echo $v1['goods_diy_id'] ?>')">删除</span></td></tr>
		   
		    <?php } ?>
		    
		    <?php } ?>
		  </tbody >
		  </table>
