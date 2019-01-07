<?php require_lib('util/oms_util', true);?>
<script type="text/javascript" src="../../webpub/js/jquery-1.8.1.min.js"></script>
<style>
.calendar-time{width:100px;}
</style>
<div><button class="button">参数设置</button></div>
<br/>
<br/>
<div class="span25">
<form method ="post" action="?app_act=sys/auto_create/do_param">
<table class="table table-bordered">
<thead>
<tr><th style="text-align: center">设置项</th><th style="text-align: center">说明</th></tr>
</thead>
<tbody>
<tr><td>

商品属性管理：<br/> <br/>

规格1别名<input name="goods_spec1" value="颜色"/><br/><br/>

规格2别名<input name="goods_spec2" value="尺码"/>
</td>
<td>设定商品的规格属性，如服装行业的 颜色、尺码</td></tr>


<tr><td>

批次、生产日期管理  <input type="radio" name="lof_status" value="1" onclick="change()"/>开启<input type="radio" name="lof_status" value="0" checked="checked" onclick="change()"/>关闭<br/>
<div id="order" style="display:none;">
<br/>发货顺序  <input type="radio" name="delivery_lof_sort" value="0"  checked="checked"/>系统默认批次 优先 发货<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="delivery_lof_sort" value="1"/>商家自定义批次 优先 发货
</div>

</td>
<td>

按到期日、批次等实现商品的先进先出。适用行业：食品、医药、保健品、化妆品等。<br/><br/>

a、开启：采购入库可录入商品批次号、生产日期，发货出库时可按批次优先级实现先进先出。建议选择‘系统默认批次 优先 发货’。<br/><br/>

b、关闭：系统无生产日期、批次概念，所有商品无区别。<br/><br/>

c、系统默认批次：系统初始化商品库存时，为所有商品设定一个默认的批次号、生产日期，即为系统默认批次。<br/><br/>

d、商家自定义批次：采购入库时，录入的批次号、生产日期，即为商家自定义批次。
</td></tr>


<tr><td>
请设置网店的默认发货快递公司： <br/> <br/>
<select name="express_code">
<option value="">请选择</option>
<?php $list = oms_tb_all('base_express_company', array()); 
foreach($list as $k=>$v){ ?>
<option value="<?php echo $v['company_code']?>"><?php echo $v['company_name']?></option>
 <?php } ?>
</select>
</td>
<td>
设置网店的默认发货快递公司，转单时以此为默认发货快递，后期可在店铺档案中修改默认值
</td></tr>


<tr><td>
<br/>
上线日期：<input type="text" name="online_date" class="calendar" value="<?php echo date("Y-m-d",time())?>"/>
<br/>
</td>
<td>
平台订单下载，只下载下单日期大于上线日期的网络订单，请慎重填写此日期
</td></tr>
</tbody>
</table>

<input  type="submit" style="float:right;margin: 20px 0 0 0;width:100px;height:40px;" class="button button-primary" value="下一步"/>
</form>
</div>
<script>

BUI.use('bui/calendar',function(Calendar){
	var datepicker = new Calendar.DatePicker({
	trigger:'.calendar',
	autoRender : true
	});
	});
function change(){

	if($("input[name='lof_status']:checked").val()== 1){
		$("#order").css("display","block");
	}else{
		$("#order").css("display","none");
	}
	
 }
</script>