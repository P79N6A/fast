<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
</style>
<?php render_control('PageHead', 'head1',
    array('title' => '订单导入',

        'links' => array(

        ),
        'ref_table' => 'table'
    ));?>
<form action="?app_act=oms/sell_record/import_sell_record" method="post" enctype="application/x-www-form-urlencoded" name="form1" id="form1">    
	<table cellspacing="0" class="table table-bordered">
	   <tr>
	   <td>文件上传：</td>
	   <td><input type="file" name="file" /></td>
	   </tr>
	   
	   <tr>
	   <td>订单状态：</td>
	   <td>
		   <input name="mode" type="radio" value="1" checked="checked" /> 已付款未确认(导入后，系统将占库存)<br/>
		   <input name="mode" type="radio" value="2"/> 已付款已发货(导入后，系统会扣减库存，并生成零售结算单)<br/>
	   </td>
	   </tr>

	   <tr>
	   <td></td>
	   <td>（导入的订单的状态，根据所选择来适配）</td>
	   </tr>

	   <tr>
	   <td><input type="submit" name="Submit" value="上传"  class="button button-primary"/></td>
	   <td><a href="###">下载模板</a></td>
	   </tr>

	</table>
</form>

<div class="result1" style="display: block">

</div>
<script type="text/javascript">

</script>