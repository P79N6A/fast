<style type="text/css">
.table_panel1{
	width:100%;
	margin-bottom:5px;
 }
.table_panel1 td {
    border:1px solid #dddddd;
    line-height: 20px;
    padding: 5px;
    text-align: left;
}
</style>
<div class="panel">
	    
	    <div class="panel-body">
	    	<div class="row" >
	    	<div>当前需修改的商品:<?php echo $response['cur_goods']['goods_name'] ?>[<?php echo $response['cur_goods']['goods_code'] ?>] &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;  系统规格:规格1:<?php echo $response['cur_goods']['spec1_name']; ?>;规格2:<?php echo $response['cur_goods']['spec2_name'] ?>;</div>
	    	<div>&nbsp;&nbsp;</div>
	    	<table class='table_panel1' >
	    	<tr> 
	    	<td> <?php echo $response['goods_spec1_rename'] ?></td>
	    	<td> <?php echo $response['goods_spec2_rename'] ?></td>
	    	<td> 商品条形码</td>
	    	<td> 数量</td>
	    	<td> </td>
	    	</tr>
	    	 <?php foreach($response['goods'] as $k2=>$v2){ ?>
	    	<tr> 
	    	<td> <?php echo $v2['spec1_name'] ?>[<?php echo $v2['spec1_code'] ?>]</td>
	    	<td> <?php echo $v2['spec2_name'] ?>[<?php echo $v2['spec2_code'] ?>]</td>
	    	<td> <?php echo $v2['barcode'] ?></td>
	    	<td> <input type="input" size="3"  id = "num_<?php echo $v2['sku'] ?>"  name="num_<?php echo $v2['sku'] ?>" value="<?php echo $response['num'] ?>" /></td>
	    	<td><input type="radio" name="aa" value="<?php echo $v2['sku'] ?>" /> </td>
	    	 <?php } ?>
	    	</tr>
	    	
	    	</tr>
	    	
	    	</table>
	    	
	    	</div>
	 </div>
 </div> 
<div>
<font color="red">说明：请选择需要替换现有规格的数据，并设置数量</font></div>
<script type="text/javascript">
var sell_return_detail_id = '<?php echo $response['sell_return_detail_id'] ?>';
var deal_code = '<?php echo $response['deal_code'] ?>';
var avg_money = '<?php echo $response['avg_money'] ?>';
var is_gift = '<?php echo $response['is_gift'] ?>';
</script>
<?php //echo_selectwindow_js($request, 'table', array('sku'=>'sku','op_gift_strategy_detail_id'=>'4', 'goods_code'=>'goods_code' ),'sss') ?>
<script type="text/javascript">
//判断浏览器类型
var mb=myBrowser();

function ES_getSelection() {
    //tableGrid.clearSelection();
    var sku =  $("input[name='aa']:checked").val();
    new_sku = sku.replace(".","\\.");
     new_sku = new_sku.replace("#","\\#");
    var num = $("#num_"+new_sku).val();
    var num = $("#num_"+sku).val();
    //var data = 'dongfang';
    var data = {
        'sku':sku,
        'num':num,
        'sell_return_detail_id':sell_return_detail_id,
        'deal_code':deal_code,
        'avg_money':avg_money,
        'is_gift':is_gift
    };
    <?php if ($request['callback']):?>
    if(window.frames.length+1 != parent.frames.length){
        <?php echo "getTopFrameWindowByName('{$request['ES_pFrmId']}').{$request['callback']}(data, '', '', '')";?>
    }else{
        <?php echo  "parent.{$request['callback']}(data, '', '', '')";?>
    }
    <?php endif;?>
}
</script>



