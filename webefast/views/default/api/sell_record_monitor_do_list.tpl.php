<style type="text/css">
    .well {
        min-height: 0px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '交易监控',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php 
$tabs = array();
$tabs[] = array('title' => date('Y-m-d'), 'active' => true, 'id' => date('Y-m-d'));
for($i=1;$i<3;$i++){
	$monitor_time = date('Y-m-d',strtotime('-'.$i.'day'));
	$tab = array('title' => $monitor_time, 'active' => false, 'id' => $monitor_time);
	$tabs[] = $tab;
}
?>
<?php

render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(
        
   array(
        'label' => '查询',
        'id' => 'btn-search',
           'type'=>'submit'
    ),
         ) ,
    'fields' => array(
		    array (
	    		'label' => '销售平台',
	    		'type' => 'select',
	    		'id' => 'source',
	    		'data'=>array(
	    					array('taobao','淘宝'),
	    				)
	    	),
		    array(
	    		'label' => '店铺',
	    		'title' => '',
	    		'type' => 'select_multi',
	    		'id' => 'shop_code',
	    		'data' => load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao')
		    ),
	        
    )
));
?>


<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<span style="color:red;">说明：系统按照监控时间段长度进行24小时拆分监测&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<span id="total" style="color:green;">最后更新时间：<?php echo $response['insert_time']?>  平台总订单数：<?php echo $response['taobao_order_total']?>系统总订单数：<?php echo $response['base_order_total']?></span>
<div id="TabPage1Contents">
   
</div>
<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '下单开始时间',
	        		'field' => 'monitor_start_time',
	        		'width' => '200',
	        		'align' => ''
	        ),
	        
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '下单结束时间',
	        		'field' => 'monitor_end_time',
	        		'width' => '200',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '平台总订单数',
	        		'field' => 'taobao_order_total',
	        		'width' => '200',
	        		'align' => ''
	        ),
            array(
            		'type' => 'text',
            		'show' => '1',
            		'title' => '系统总订单数',
            		'field' => 'base_order_total',
            		'width' => '200',
            		'align' => ''
            ),
            
            
        )
    ),
    'dataset' => 'api/OrderMonitorModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>

<?php echo load_js("pur.js",true);?>
<script type="text/javascript">
$(function(){
	$("#bar3").hide();
	//TAB选项卡
    $("#TabPage1 a").click(function() {
        tableStore.load();
        total_amount_search();
    });
    tableStore.on('beforeload', function(e) {
        e.params.monitor_date = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });
	
})
$("#btn-search").click(function(){
	total_amount_search();
});
function total_amount_search(){
	var url = '?app_act=api/sell_record_monitor/total_amount_search',
	params = tableStore.get('params');
	var obj = searchFormForm.serializeToObject();
    for(var key in obj){
      params[key] =  obj[key];
	}    
  
    for(var key in params){
        url +="&"+key+"="+params[key];
	}
    var d = {'app_fmt': 'json'};
    $.post(url, d, function(data){
	    $("#total").html('最后更新时间:'+data['insert_time']+' 平台总订单数：'+data['taobao_order_total']+'系统总订单数：'+data['base_order_total']);
	 }, "json");
}

</script>

