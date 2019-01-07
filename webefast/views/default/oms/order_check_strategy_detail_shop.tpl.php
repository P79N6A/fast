<style type="text/css">

.span6 .control-label {
    display: inline-block;
    float: left;
    line-height: 25px;
    text-align: left;
    width: 180px;
}
</style>
  <div class="demo-content">
 
  <div class="row">
    <div class="span24 doc-content">
      <form class="form-horizontal" onsubmit="return false;">
       <?php foreach ($response['shop'] as $key => $shop) {?>
       
        <div class="row show-grid">
       
          <div class="span3">
            <label class="control-label"><?php echo $key;?></label>
          </div>
          <div class="span20">
          	<?php foreach ($shop as $k => $v){?>
          	
          	<div class="span6">
	          	<label class="control-label">
	            <input type="checkbox" name="shop" <?php foreach ($response['shoped'] as $k => $s) {?> <?php if ($v['shop_code'] == $s['content']) echo "checked='checked'"?><?php }?> value="<?php echo $v['shop_code'];?>">
	            <?php echo $v['shop_name'];?>
	            </label>
          	</div>
          	
            <?php }?>
          </div>
        </div>
         
        <?php }?>
       	<div class="form-actions" style="text-align:center">
			<button id="shop_code_submit"  class="button button-primary">保存</button>
			<button type="reset" class="button">重置</button>
		</div>	
      </form>
    </div>
    
  </div>
 
 	<div>
	<span style="color:red;">
	提示：选择店铺后，该店铺订单都不会自动确认。
	</span>
	</div>
</div>
<script type="text/javascript">
$("#shop_code_submit").click(function(){
	var shop_code_value =[];    
	$('input[name="shop"]:checked').each(function(){    
		shop_code_value.push($(this).val());    
	});
	$.ajax({ type: 'POST', dataType: 'json',
		url: '<?php echo get_app_url('oms/order_check_strategy/shop_do_add');?>', data: {shop_code: shop_code_value},
		success: function(ret) {
		   var type = ret.status == 1 ? 'success' : 'error';
           if (type == 'success') {
                BUI.Message.Alert('设置成功', type);
                location.reload();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
     	}
   });
});
  

</script>