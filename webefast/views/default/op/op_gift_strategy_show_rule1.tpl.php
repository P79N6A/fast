<form action="?app_act=op/op_gift_strategy/rule1_save" id="form2" method="post">
<?php if(isset($response['gift']) && $response['gift'] <> ''){ ?>
       <?php 
        $k = 0;
       foreach($response['gift'] as $k1=>$v1){ 
       		$k = $k+1;
       	?>
		  
<div class="panel">
   <input  name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|type]'; ?>" type="hidden" style="width:80px;"  value="<?php echo $v1['type'] ?>">
    <?php if($v1['type'] == '0'){ ?>
	    <div class="panel-header">
	        <h3 class="">活动规则<?php echo $k ?>：满赠          
	          &nbsp;&nbsp;&nbsp;
	          <input type="radio" class="give_way is_view radio-select-type" xulie="<?php echo $v1['op_gift_strategy_detail_id']; ?>" <?php if($v1['give_way'] == '0'){ ?> checked <?php } ?>  value ='0' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|give_way]'; ?>"> 固定送赠品 
	          <input type="radio" class="give_way is_view radio-select-type" xulie="<?php echo $v1['op_gift_strategy_detail_id']; ?>" <?php if($v1['give_way'] == '1'){ ?> checked <?php } ?>  value ='1' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|give_way]'; ?>"> 随机送赠品    
	           &nbsp;&nbsp;&nbsp;   
	           <input type="radio"  class= "is_view radio-select-type" <?php if($v1['is_mutex'] == '0'){ ?> checked <?php } ?>  value ='0' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|is_mutex]'; ?>"> 互斥 
	          <input type="radio" class= "is_view radio-select-type" <?php if($v1['is_mutex'] == '1'){ ?> checked <?php } ?>  value ='1' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|is_mutex]'; ?>"> 互溶   
                  &nbsp;&nbsp;&nbsp;
                  <input type="checkbox" class="is_view"  xulie="<?php echo $v1['op_gift_strategy_detail_id']; ?>" <?php if($v1['is_fixed_customer'] == '1'){ ?> checked <?php } ?>  value ='1' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|is_fixed_customer]'; ?>" />指定会员
	          <span style="float:right;"  onClick="rule_del('<?php echo $v1['op_gift_strategy_detail_id'] ?>');" class = "delDetail"> 移除规则 </span></h3> 
	    </div>
	    <div class="panel-body">
	    	<div class="row" >
	    	<table class='table_panel' >
	    	<tr>
	    	<td>订单满
	    	<input class="deci is_view"   name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|money_min]'; ?>" type="text" style="width:80px;"  value="<?php echo $v1['money_min'] ?>">
	    	~
	    	<input class="deci is_view" name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|money_max]'; ?>" type="text" style="width:80px;"  value="<?php echo $v1['money_max'] ?>">
	    	元进行礼品赠送，随机赠送商品数
	    	<span id="<?php echo 'gift_num_'.$v1['op_gift_strategy_detail_id']; ?>"  <?php if($v1['give_way'] == '0'){ ?>  display: none;  <?php } ?> ">
	    	<input class="int_num is_view"  name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|gift_num]'; ?>" type="text" style="width:80px;" <?php if ($v1['type'] == '0' && $v1['give_way'] == '0') echo 'disabled="disabled"';?> value="<?php echo $v1['gift_num'] ?>">
	    	</span>
	    	<input class="is_view" name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|is_contain_delivery_money]'; ?>" type="checkbox"  <?php if($v1['is_contain_delivery_money'] == '1'){ ?> checked    <?php } ?> <?php if ($v1['type'] == '0' && $v1['give_way'] == '0') echo 'disabled="disabled"';?>  />包含运费
	    	
                <div class="btns" style="float:right" >
			<button type="button" class="button button-primary  is_view" value="新增赠品"   onclick="show_select_goods('<?php echo $v1['op_gift_strategy_detail_id'] ?>',1,<?php echo $v1['give_way']?>)"    >  新增赠品 </button>
	    	<button type="button" class="button button-primary is_view" value="赠品导入" onClick="importGoods('<?php echo $v1['strategy_code'] ?>','<?php echo $v1['op_gift_strategy_detail_id'] ?>',1);"  id="btnimport"><i class="icon-plus-sign icon-white"></i> 赠品导入</button>
	    	<button type="button" class="button button-primary is_view" value="导入其他规则赠品"  onClick="importOtherRuleGoods('<?php echo $v1['strategy_code'] ?>','<?php echo $v1['op_gift_strategy_detail_id'] ?>',0);" ><i class="icon-plus-sign icon-white"></i> 导入其他规则赠品</button>
                        </div>
                </td>
	    	</tr>

	    	</table>
	    	<table class='table_panel1' id= "<?php echo 'table_gift_'.$v1['op_gift_strategy_detail_id']; ?>" >
	    	<tr><td> 操作</td> <td> 商品名称</td><td> 商品编码</td><td> 商品规格</td><td  <?php if($v1['give_way'] == '1'){ ?> style="display: none;"  <?php } ?> > 数量</td><td> 商品条形码</td>
	    	</tr>
	    	 <?php foreach($v1['gift'] as $k2=>$v2){ ?>
	    	<tr><td style="width: 150px;">
	    	<a href="javascript:selectGoods('<?php echo $v2['goods_code'] ?>','<?php echo $v2['op_gift_strategy_detail_id'] ?>','<?php echo $v2['op_gift_strategy_goods_id'] ?>');"  class="button button-primary is_view"   >改款 </a> 
	    	<a href="javascript:delGoods('<?php echo $v2['op_gift_strategy_goods_id'] ?>');" class="button button-primary is_view" >删除 </a></td> <td> <?php echo $v2['goods_name'] ?></td><td><?php echo $v2['goods_code'] ?></td><td> 颜色:<?php echo $v2['spec1_name'] ?>;尺码:<?php echo $v2['spec2_name'] ?></td>
	    	<td <?php if($v1['give_way'] == '1'){ ?> style="display: none;"  <?php } ?>>
	    	<input class='is_view' name="<?php echo 'goodsbuy['.$v1['op_gift_strategy_detail_id'].'][gift]['.$v2['op_gift_strategy_goods_id'].'][num]'; ?>" type="text" style="width:80px;"  value="<?php echo $v2['num'] ?>">
	    	</td>
	    	<td> <?php echo $v2['barcode'] ?></td>
	    	 <?php } ?>
	    	</tr>
	    	
	    	
	    	
	    	</table>
                    <div>
                        	<font color="#ec6d3a"> 注：最小值 < 订单金额 <= 最大值 时，赠送礼品。 包含运费时，订单金额=订单应付款。不包含运费时，订单金额=订单应付款-运费</font>
                        
                    </div>
	    	</div>
	    
	  
	 </div>
	  <?php } ?>
	   <?php if($v1['type'] == '1'){ ?>
	   <div class="panel-header">
	        <h3 class="">活动规则<?php echo $k ?>：买赠    &nbsp;&nbsp;&nbsp;    
	          <input type="radio" class="condition is_view radio-select-type" xulie="<?php echo $v1['op_gift_strategy_detail_id']; ?>" <?php if($v1['goods_condition'] == '0'){ ?> checked <?php } ?>  value ='0' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|goods_condition]'; ?>">买固定商品 
	          <input type="radio" class="condition is_view radio-select-type" xulie="<?php echo $v1['op_gift_strategy_detail_id']; ?>" <?php if($v1['goods_condition'] == '1'){ ?> checked <?php } ?>  value ='1' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|goods_condition]'; ?>"> 买随机商品    
	          <input type="radio" class="condition is_view radio-select-type" xulie="<?php echo $v1['op_gift_strategy_detail_id']; ?>" <?php if($v1['goods_condition'] == '2'){ ?> checked <?php } ?>  value ='2' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|goods_condition]'; ?>"> 全场买就送
	          &nbsp;&nbsp;&nbsp;

	          <input type="radio" class="give_way is_view radio-select-type" xulie="<?php echo $v1['op_gift_strategy_detail_id']; ?>" <?php if($v1['give_way'] == '0'){ ?> checked <?php } ?>  value ='0' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|give_way]'; ?>"> 固定送赠品 
	          <input type="radio" class="give_way is_view radio-select-type" xulie="<?php echo $v1['op_gift_strategy_detail_id']; ?>" <?php if($v1['give_way'] == '1'){ ?> checked <?php } ?>  value ='1' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|give_way]'; ?>"> 随机送赠品    
	           &nbsp;&nbsp;&nbsp;   
	           <input class="is_view" type="radio" <?php if($v1['is_mutex'] == '0'){ ?> checked <?php } ?>  value ='0' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|is_mutex]'; ?>"> 互斥 
	          <input class="is_view" type="radio" <?php if($v1['is_mutex'] == '1'){ ?> checked <?php } ?>  value ='1' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|is_mutex]'; ?>"> 互溶    
                     &nbsp;&nbsp;&nbsp;   
                       <input class="is_view" type="checkbox" <?php if($v1['is_repeat'] == '1'){ ?> checked <?php } ?>  value ='1' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|is_repeat]'; ?>">叠加送    
                     &nbsp;&nbsp;&nbsp;      
                     
                     
                     
	           <input type="checkbox" class="is_view"  xulie="<?php echo $v1['op_gift_strategy_detail_id']; ?>" <?php if($v1['is_fixed_customer'] == '1'){ ?> checked <?php } ?>  value ='1' name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|is_fixed_customer]'; ?>" />指定会员
	                                                             <span style="float:right;"  onClick="rule_del('<?php echo $v1['op_gift_strategy_detail_id'] ?>');" class = "delDetail"> 移除规则 </span></h3> 
	    </div>
	    <div class="panel-body">
                <div  <?php if($v1['goods_condition'] == '2'){ ?> style="display: none;"  <?php } ?>    >
	        <div style="height:30px;">商品列表:
                    <div class="btns" style="float:right;">
                                                        <span id="<?php echo 'buy_num_'.$v1['op_gift_strategy_detail_id']; ?>" style=" <?php if($v1['goods_condition'] == '0'){ ?>  display: none;  <?php } ?> ">随机购买商品数量	<input class="int_num is_view" name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|buy_num]'; ?>" type="text" style="width:80px;"  value="<?php echo $v1['buy_num'] ?>"> </span>
                
			<button type="button" class="button button-primary  is_view" value="新增商品"    onclick="show_select_goods('<?php echo $v1['op_gift_strategy_detail_id'] ?>',0,0)"  >  新增商品 </button>
	    	<button type="button" class="button button-primary is_view" value="商品导入" onClick="importGoods('<?php echo $v1['strategy_code'] ?>','<?php echo $v1['op_gift_strategy_detail_id'] ?>',0);"  id="btnimport"><i class="icon-plus-sign icon-white"></i> 商品导入</button>
	    	
	    	</div>
                    
                    

                
                </div>
	    	<table class='table_panel1' id= "<?php echo 'table_goods_'.$v1['op_gift_strategy_detail_id']; ?>">
	    	<tr><td> 操作</td> <td> 商品名称</td><td> 商品编码</td><td> 商品规格</td><td <?php if($v1['goods_condition'] == '1'){ ?> style="display: none;"  <?php } ?> > 数量</td><td> 商品条形码</td>
	    	</tr>
	    	 <?php foreach($v1['goods'] as $k2=>$v2){ ?>
	    	<tr><td style="width: 150px;">
	    	<a href="javascript:selectGoods('<?php echo $v2['goods_code'] ?>','<?php echo $v2['op_gift_strategy_detail_id'] ?>','<?php echo $v2['op_gift_strategy_goods_id'] ?>');"  class="button button-primary is_view"   >改款 </a> 
	    	<a href="javascript:delGoods('<?php echo $v2['op_gift_strategy_goods_id'] ?>');" class="button button-primary is_view" >删除 </a></td> <td> <?php echo $v2['goods_name'] ?></td><td><?php echo $v2['goods_code'] ?></td><td> 颜色:<?php echo $v2['spec1_name'] ?>;尺码:<?php echo $v2['spec2_name'] ?></td><td  <?php if($v1['goods_condition'] == '1'){ ?> style="display: none;"  <?php } ?>>

			<input class="is_view" name="<?php echo 'goodsbuy['.$v1['op_gift_strategy_detail_id'].'][goods]['.$v2['op_gift_strategy_goods_id'].'][num]'; ?>" type="text" style="width:80px;"  value="<?php echo $v2['num'] ?>">
	    	</td><td> <?php echo $v2['barcode'] ?></td>
	    	 <?php } ?>
	    	</tr>   	
	    	</table>
                </div>
                
	
	    	<div style="height:30px;" >赠品列表:
                                    <div class="btns" style="float:right;">
                    <span id="<?php echo 'gift_num_'.$v1['op_gift_strategy_detail_id']; ?>" style=" <?php if($v1['give_way'] == '0'){ ?>  display: none;  <?php } ?> ">随机赠送商品数量	<input class="int_num is_view" name="<?php echo 'gift['.$v1['op_gift_strategy_detail_id'].'|gift_num]'; ?>" type="text" style="width:80px;"  value="<?php echo $v1['gift_num'] ?>"> </span>
                    <button type="button" class="button button-primary  is_view" value="新增赠品" onclick="show_select_goods('<?php echo $v1['op_gift_strategy_detail_id'] ?>',1,<?php echo $v1['give_way'];?>)"    >  新增赠品 </button>
                                  
	    	<button type="button" class="button button-primary is_view" value="赠品导入" onClick="importGoods('<?php echo $v1['strategy_code'] ?>','<?php echo $v1['op_gift_strategy_detail_id'] ?>',1);"  id="btnimport"><i class="icon-plus-sign icon-white"></i> 赠品导入</button>
	    	<button type="button" class="button button-primary is_view" value="导入其他规则赠品"  onClick="importOtherRuleGoods('<?php echo $v1['strategy_code'] ?>','<?php echo $v1['op_gift_strategy_detail_id'] ?>',0);" ><i class="icon-plus-sign icon-white"></i> 导入其他规则赠品</button>
	    	
                 </div>
                </div>
	    	<table class='table_panel1' id= "<?php echo 'table_gift_'.$v1['op_gift_strategy_detail_id']; ?>">
	    	<tr><td> 操作</td> <td> 商品名称</td><td> 商品编码</td><td> 商品规格</td><td  <?php if($v1['give_way'] == '1'){ ?> style="display: none;"  <?php } ?> > 数量</td><td> 商品条形码</td>
	    	</tr>
	    	 <?php foreach($v1['gift'] as $k2=>$v2){ ?>
	    	<tr><td style="width: 150px;">
	    	<a href="javascript:selectGoods('<?php echo $v2['goods_code'] ?>','<?php echo $v2['op_gift_strategy_detail_id'] ?>','<?php echo $v2['op_gift_strategy_goods_id'] ?>');"  class="button button-primary is_view"   >改款 </a> 
	    	<a href="javascript:delGoods('<?php echo $v2['op_gift_strategy_goods_id'] ?>');" class="button button-primary is_view" >删除 </a></td> <td> <?php echo $v2['goods_name'] ?></td><td><?php echo $v2['goods_code'] ?></td><td> 颜色:<?php echo $v2['spec1_name'] ?>;尺码:<?php echo $v2['spec2_name'] ?></td><td <?php if($v1['give_way'] == '1'){ ?> style="display: none;"  <?php } ?>>

	    	 <input class="is_view"  name="<?php echo 'goodsbuy['.$v1['op_gift_strategy_detail_id'].'][gift]['.$v2['op_gift_strategy_goods_id'].'][num]'; ?>" type="text" style="width:80px;"  value="<?php echo $v2['num'] ?>">
	    	 </td><td> <?php echo $v2['barcode'] ?></td>
	    	 <?php } ?>
	    	</tr>   	
	    	</table>

	    </div>
   
   <div style="color:#ec6d3a">
  <?php if($v1['goods_condition'] == '0'):?>    
说明：买固定商品，客户购买如下商品列表中全部商品且达到数量送赠品。
 <?php               elseif ($v1['goods_condition'] == '1'):?>
说明：买随机商品，客户购买如下商品列表中随机一款商品且达到数量送赠品。
<?php else: ?>
说明：全场买就送，客户只要购买当前店铺任何商品都送赠品。
<?php endif?>

   </div>
   
	   <?php } ?>
 </div> 
     <?php } ?>
		    
 <?php } ?>
 </form>
<script type="text/javascript">

if(typeof(show)!='undefined'){
    if(show == '1'){
               $(".is_view").attr("disabled", true);
    }
}
$(".radio-select-type").click(function (){
	$("#btnSaveRule1").trigger('click');
})

</script>