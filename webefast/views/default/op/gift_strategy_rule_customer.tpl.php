
<style type="text/css">
.table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
.table_panel1{
	width:100%;
	margin-bottom:5px;
 }
 .table_panel td {
    border-top: 0px solid #dddddd;
    line-height: 18px;
	padding:5px 10px;
    text-align: left;
}
.table_panel1 td {
    border:1px solid #dddddd;
    line-height: 20px;
    padding: 5px;
    text-align: left;
}

.table_panel_tt td{ padding:10px 25px;}
.nav-tabs{ padding-top:10px; margin-bottom:10px;}
.btns{ text-align:right; margin-bottom:5px;}
.panel-body { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;}
.panel > .panel-header{background-color: #ecebeb; border-color:#ded6d9; padding:5px 15px;}
.panel > .panel-header h3{ font-size:14px;}
input[type="checkbox"], input[type="radio"] { margin-right:2px; vertical-align: inherit;}

.bui-dialog .bui-stdmod-body {padding: 40px;}
.show_scan_mode{ text-align:center;}
.button-rule{ width:108px; height:108px; line-height: 104px;font-size: 22px;color: #666; background:url(assets/img/ui/add_rules.png) no-repeat; margin:0 8px; background-color:#f5f5f5; border-color:#dddddd; position:relative;}
.button-rule .icon{ display:block; width:37px; height:25px; background:url(assets/img/ui/add_rules.png) no-repeat center; position:absolute; top:-1px; right:-2px; display:none;}
.button-rule:active{ background-image:url(assets/img/ui/add_rules.png); box-shadow:none;}
.button-rule:active .icon{ display:block;}
.button-rule:hover{ background-color:#fff6f3; border-color:#ec6d3a; color:#ec6d3a;}
.button-manz{ background-position:41px 26px;}
.button-maiz{background-position:-208px 25px;}
.button-manz:hover{background-position:41px -214px;}
.button-maiz:hover{background-position:-208px -215px;}
</style>
<?php echo load_js("baison.js,record_table.js",true);?>
<ul class="nav-tabs oms_tabs">
     <li onClick="do_page('base');" ><a href="#"  >规则设置</a></li>
    <li onClick="do_page('gift');"><a href="#" >赠品商品</a></li>
    <li onClick="do_page('goods');"><a href="#" >活动商品</a></li>
    <li class="active"><a href="#" >定向会员</a></li>
   
</ul>
<table class='table_panel table_panel_tt' >
<tr>
  <td>策略名称：<?php echo $response['strategy']['strategy_name']; ?></td>
  <td >活动店铺：<?php echo $response['strategy']['shop_code_name']; ?></td>
</tr>
<tr>
  <td >活动开始时间：<?php echo date('Y-m-d H:i',$response['strategy']['start_time']); ?></td>
  <td >活动结束时间：<?php echo date('Y-m-d H:i',$response['strategy']['end_time']); ?></td>
</tr>
</table>
 <div id="customer_btns" class="btns" >     
        <button type="button" <?php if($response['strategy']['is_check']==1){?>disabled <?php }?> class="button button-primary is_view " onclick="import_customer()"   ><i class="icon-plus-sign icon-white"></i> 会员导入</button>
        <button type="button" <?php if($response['strategy']['is_check']==1){?>disabled <?php }?> class="button button-primary  is_view" onclick="clear_customer_data();"  ><i class="icon-plus-sign icon-white"></i> 一键清空</button>
    </div>
<div id='customer' >
    <div>
	<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (


            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '150',
                'align' => ''
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机号',
                'field' => 'tel',
                'width' => '150',
                'align' => ''
            ),
            
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '导入时间',
            		'field' => 'lastchanged',
            		'width' => '150',
            		'align' => ''
            ),
          )
    ),
    'dataset' => 'op/GiftStrategy2CustomerModel::get_by_page',
    'idField' => 'op_gift_strategy_customer_id',
	   'params' => array(
        'filter' => array('op_gift_strategy_detail_id'=>$response['data']['op_gift_strategy_detail_id']),
    ),

) );
?>
        </div>
    <br />    <br />    <br />   

    <div style="color:red;">说明：赠送规则勾选‘指定会员’，需要在此页面导入会员数据。即只有满足赠送规则且会员存在于此页面才送赠品，否则不送</div>
</div>  	

<script type="text/javascript">
   var strategy_code = "<?php echo $response['strategy']['strategy_code']; ?>";
   var id = "<?php echo $request['_id']; ?>";
   function do_page(type) {
		if (type == 'base'){
			location.href = "?app_act=op/gift_strategy/rule_view&app_scene=edit&_id="+id;
		} else if (type == 'gift'){
			location.href = "?app_act=op/gift_strategy/gift_goods&app_scene=edit&_id="+id;
		} else if (type == 'goods'){
			location.href = "?app_act=op/gift_strategy/rule_goods&app_scene=edit&_id="+id;
		} else if (type == 'customer'){
			location.href = "?app_act=op/gift_strategy/rule_customer&app_scene=edit&_id="+id;
		}
		
		
	}
   function import_customer(){
		var param = {};
		var type = 2;
		var url= '?app_act=op/gift_strategy/customer_import&strategy_code='+strategy_code+"&op_gift_strategy_detail_id="+id;
		    new ESUI.PopWindow(url, {
		            title: '导入会员',
		            width:500,
		            height:380,
		            onBeforeClosed: function() {  tableStore.load(); 
		            }
		        }).show(); 	
		     
	} 
   function clear_customer_data(){
       var url = '?app_act=op/gift_strategy/clear_customer_data&app_fmt=json';
       var data = {'op_gift_strategy_detail_id':id};
       $.post(url,data,function(ret){
            tableStore.load();
       },'json');
   }   
        
</script>
