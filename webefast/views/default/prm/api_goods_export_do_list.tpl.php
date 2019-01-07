<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
</style>
<?php render_control('PageHead', 'head1',
    array('title' => '平台商品格式化工具',

        'links' => array(

        ),
        'ref_table' => 'table'
    ));?>
   <table cellspacing="0" class="table table-bordered">
   <tr>
   <td> <label style="display: inline-block; vertical-align: middle; padding: 0 10px; font-size:16px;">店铺</label><select name="shop_code" id="shop_code" data-rules="{required : true}" style="height:30px">
			       <?php foreach($response['shop'] as $k=>$v){ 
			       	     $row = array_values($v);
			       	?>
			    	<option  value ="<?php echo $row[0]; ?>" ><?php echo $row[1]; ?></option>
			       <?php } ?>
			        </select>
  
   <button id="exprot_list" class="button " type="button">导出</button></td>
    
   </tr>
   <table>
    <div id="u13" class="text">
          <p><span style="font-family:'Applied Font Regular', 'Applied Font';">说明：</span></p>
          <p><span style="font-family:'Applied Font Regular', 'Applied Font';">目的：</span><span style="font-family:'Applied Font Regular', 'Applied Font';">&nbsp;检查平台商品数据规范性，快速初始化系统的商品档案</span></p>
          <p><span style="font-family:'Applied Font Regular', 'Applied Font';">方法：</span>
          <span style="font-family:'Applied Font Regular', 'Applied Font';">1、选择店铺，点击【导出】按钮，保存文件；</span></p>
          <p><span style="font-family:'Applied Font Regular', 'Applied Font';">&nbsp; &nbsp; &nbsp;  2、检查商品数据，确定商品编码(即平台商品级商家编码)唯一且不为空，确定商品条形码</span></p>
          <p><span style="font-family:'Applied Font Regular', 'Applied Font';">&nbsp; &nbsp; &nbsp;  （即平台SKU级商家编码）唯一且不为空；若不唯一或为空，请进入平台，修改商品编辑后，再次导出数据；</span></p>
          <p><span style="font-family:'Applied Font Regular', 'Applied Font';">&nbsp; &nbsp; &nbsp;  3、对于已检查无误的文档，填写商品分类、商品品牌、商品季节、商品年份等数据；</span></p> 
          <p><span style="font-family:'Applied Font Regular', 'Applied Font';">&nbsp; &nbsp; &nbsp;  4、在商品-&gt;商品管理-&gt;商品信息导入，‘混合数据导入’中选择步骤1中保存的CSV文件，【导入】即可；</span></p> 
          <p><span style="font-family:'Applied Font Regular', 'Applied Font';">&nbsp; &nbsp; &nbsp;  5、查看商品档案、商品属性档案，店铺中的商品数据已经在系统创建成功。</span></p>
	</div>
<script type="text/javascript">
	$(document).ready(function(){
		$("#exprot_list").click(function(){
	        
			shop_code = $("#shop_code").val();
	       	var params = {
	                "shop_code": $("#shop_code").val(),
	               
	            };
	       	window.location.href = '?app_act=prm/api_goods_export/export&shop_code='+shop_code ;
				
	       });	
	});
</script>