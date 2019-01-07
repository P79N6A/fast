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
<form action="?app_act=prm/goods/" id="form2" method="post">
<table class='table_panel1' id='p2'><input type="hidden" id="goods_code" name="goods_code" value="<?php echo $response['goods_code'];?>">
  <input type="hidden" id="spec1_code"  value="" name="spec1_code" />
  <input type="hidden" id="spec1_name"  value="" name="spec1_name" />
  <tr>
  <td>商品名称 </td>
  <td >规格1(别名)</td>
  <td >规格2(别名)</td>
  <td >系统SKU码</td>
  <td > 商品条形码 </td>
  <td> 吊牌价(元) </td>
  <td> 操作 </td>
  </tr>
  <?php foreach($response['barcord'] as $k=>$v){ ?>
  <tr>
 <td style="width:20px;" ><span onClick="show_detail('<?php echo $v['sku'] ?>');">ss</span> <?php echo $v['goods_code'] ?></td>
  <td><?php echo $v['spec1_code_name'] ?> </td>
  <td ><?php echo $v['spec2_code_name'] ?></td>
  <td ><?php echo $v['sku'] ?></td>
  <td ><input  name="<?php echo 'barcode_barcode['.$v['barcode_id'].']'; ?>" type="text"  value="<?php echo $v['barcode'] ?>"></td>
  <td ><?php echo $v['sell_price'] ?> </td>
  <td > 新增商品  删除</td>
  </tr>
   <tr class="<?php echo "show_tr_".$v['sku'] ?>" <?php if($response['sku'] == $v['sku']){ ?>  <?php }else{ ?> style= 'display:none;' <?php } ?>>
   <td></td>
   <td colspan=7 class="<?php echo "show_".$v['sku'] ?>">
    <?php if($response['sku'] == $v['sku']){ ?>
        <!--    <table class='table_panel1' >
           <tr><td style="width:15%;">商品名称</td><td style="width:15%;">商品编码</td><td style="width:10%;">规格1</td><td style="width:10%;">规格2</td><td style="width:80px;">商品条形码</td><td style="width:80px;;">吊牌价</td><td>数量</td></tr>
           <?php if(isset($v['diy'])){ ?>
            <?php foreach($v['diy'] as $k1=>$v1){ ?>
		  
		  <tbody id="tiaoma">
		  
		     <tr><td ><?php echo isset($v1['goods_code'])?$v1['goods_code']:'' ?></td><td ><?php echo isset($v1['goods_code'])?$v1['goods_code']:'' ?></td><td > </td><td >
		
		</td><td >(元)</td><td >(克)</td><td><input  name="<?php echo 'diy['.$v1['goods_diy_id'].']'; ?>" type="text"  value=""></td></tr>
		   
		    <?php } ?>
		    
		    <?php } ?>
		  </tbody >
		  </table>-->
     <?php } ?>
  </td></tr>
 <?php } ?>
  
  <tr><td style="width:80px;"></td><td colspan=7><input type="button" class="button button-primary" id="btn_save"  value = "保存">&nbsp;&nbsp;&nbsp;<button type="reset" class="button button-primary">重置</button>&nbsp;&nbsp;&nbsp;<input type="button" class="button button-primary"  value="返回" onclick="history.go(-1)">&nbsp;&nbsp;&nbsp;<input type="hidden" name="msg" id="msg"></td></tr>
</table>
</form>
<script type="text/javascript">
$(document).ready(function(){
	$("#btn_save").click(function(){
        
       	var data = $('#form2').serialize();
       
       	$.post('<?php echo get_app_url('prm/goods/td_js_save');?>', data, function(data){
            var type = data.status == 1 ? 'success' : 'error';
    		if (data.status == 1) {
    			
   			   //BUI.Message.Alert('修改成功：', type);
      			//window.location.reload();
    		} else {
    			//BUI.Message.Alert(data.message, function() { }, type);
    		}
        }, "json");
          
			
       });
});
function show_detail(sku) {
	if($(".show_tr_"+sku).is(":hidden")) 
	{  
		$(".show_tr_"+sku).show();
	}else{
		$(".show_tr_"+sku).hide();
	}
		  
      var len = $(".show_"+sku).children(".table_panel1").length;
      if($(".show_tr_"+sku).is(":hidden")){
     // if(len == 0){
 	     alert("dong");
        var data = {
            'p_sku':sku,
            'p_goods_code':"<?php echo $response['goods_code'];?>",
           // 'app_tpl':'oms/deliver_record_detail',
            'app_page':'NULL'
        };
        $.ajax({
            type : "get",  
            url : "?app_act=prm/goods/diy_show_detail",  
            data : data,
            async : false,
            success : function(data){
                //ret = data; 
                $(".show_"+sku).html(data);
            }
        });
        //return ret;
     // }
      } //hidden 
        
    }
</script>