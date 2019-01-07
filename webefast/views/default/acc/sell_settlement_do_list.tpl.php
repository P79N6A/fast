<style type="text/css">
    .well {
        min-height: 0px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '零售结算交易核销查询',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php
$sell_month = date('Y-m-01');
render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(
        
   array(
        'label' => '查询',
        'id' => 'btn-search',
           'type'=>'submit'
    ),
   array(
        'label' => '导出',
        'id' => 'exprot_list',
    ),
         ) ,
    'fields' => array(
		    array(
		    		'label' => '交易号',
		    		'title' => '',
		    		'type' => 'input',
		    		'id' => 'deal_code'
		    ),
		    array(
	    		'label' => '店铺',
	    		'title' => '',
	    		'type' => 'select_multi',
	    		'id' => 'shop_code',
	    		'data' => load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao')
		    ),
		    array(
	    		'label' => '发货日期',
	    		'type' => 'group',
	    		'field' => 'sell_month',
	    		'child' => array(
	    				array('title' => 'start', 'type' => 'date', 'field' => 'sell_month_start','value' => $sell_month),
	    				array('pre_title' => '~', 'type' => 'date', 'field' => 'sell_month_end'),
	    		)
		    ),
		    array(
	    		'label' => '收款日期',
	    		'type' => 'group',
	    		'field' => 'account_month',
	    		'child' => array(
	    				array('title' => 'start', 'type' => 'date', 'field' => 'account_month_start'),
	    				array('pre_title' => '~', 'type' => 'date', 'field' => 'account_month_end'),
	    		)
		    ),
		    array(
	    		'label' => '核销日期',
	    		'type' => 'group',
	    		'field' => 'check_accounts_time',
	    		'child' => array(
	    				array('title' => 'start', 'type' => 'date', 'field' => 'check_accounts_time_start'),
	    				array('pre_title' => '~', 'type' => 'date', 'field' => 'check_accounts_time_end'),
	    		)
		    ),
	        
    )
));
?>


<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
    	array('title' => '全部', 'active' => false, 'id' => 'all'),
		array('title' => '未核销', 'active' => true, 'id' => 'no_check'),
        array('title' => '部分核销', 'active' => false, 'id' => 'part_check'),
        array('title' => '已核销', 'active' => false, 'id' => 'have_check'),
        array('title' => '虚拟核销', 'active' => false, 'id' => 'dummy_check'),
       
        
       
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<span id="total" style="color:red;">应收款合计:<?php echo $response['total_fee']?></span>
<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
	        array (
	        		'type' => 'button',
	        		'show' => 1,
	        		'title' => '操作',
	        		'field' => '_operate',
	        		'width' => '80',
	        		'align' => '',
	        		'buttons' => array (
	        			array('id'=>'check_accounts_status', 'title' => '人工核销', 'callback'=>'check_account','show_cond'=>'obj.check_accounts_status == 0 || obj.check_accounts_status == 20  || obj.check_accounts_status == 50'),
	        		),
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '交易号',
	        		'field' => 'deal_code',
	        		'width' => '140',
	        		'align' => '',
	        		'format_js' => array(
	        				'type' => 'html',
	        				'value' => '<a href="javascript:view(\\\'{deal_code}\\\')">{deal_code}</a>',
	        				//'value' => '<a href="' . get_app_url('acc/sell_settlement/record_list') . '&deal_code={deal_code}">{deal_code}</a>',
	        		),
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => '1',
	        		'title' => '店铺',
	        		'field' => 'shop_code_name',
	        		'width' => '160',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '应收款',
	        		'field' => 'total_fee',
	        		'width' => '80',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '实收款',
	        		'field' => 'ali_total_fee',
	        		'width' => '80',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '支付宝交易收款',
	        		'field' => 'ali_trade_je',
	        		'width' => '100',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '支付宝维权退款',
	        		'field' => 'sale_right_fee',
	        		'width' => '100',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '天猫佣金扣款',
	        		'field' => 'commission_fee',
	        		'width' => '100',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '天猫代扣返点积分',
	        		'field' => 'point_fee',
	        		'width' => '120',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '积分抵用金额',
	        		'field' => 'real_point_fee',
	        		'width' => '120',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '淘宝客佣金代扣款',
	        		'field' => 'commission_fee2',
	        		'width' => '120',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '信用卡支付服务费',
	        		'field' => 'credit_code_fee',
	        		'width' => '120',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '分销退款',
	        		'field' => 'fx_refund_money',
	        		'width' => '120',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '发货时间',
	        		'field' => 'sell_month',
	        		'width' => '90',
	        		'align' => ''
	        ),
	        array(
	        		'type' => 'text',
	        		'show' => 1,
	        		'title' => '收款时间',
	        		'field' => 'account_month',
	        		'width' => '90',
	        		'align' => ''
	        ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销状态',
	            'field' => 'check_accounts_status_txt',
	            'width' => '80',
	            'align' => '',
            
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销备注',
	            'field' => '',
	            'width' => '120',
	            'align' => '',
            
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销时间',
	            'field' => 'check_accounts_time',
	            'width' => '150',
	            'align' => ''
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销操作人',
	            'field' => 'check_accounts_user_code',
	            'width' => '100',
	            'align' => ''
            ),
            
        )
    ),
    'dataset' => 'acc/OmsSellSettlementModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'export' => array('id' => 'exprot_list', 'conf' => 'sell_settlement_search_list', 'name' => '零售结算核销查询', 'export_type' => 'file'),
    //'init' => 'nodata',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
	'params' => array(
		'filter' => array("sell_month_start" => $sell_month)
	),
));
?>

<?php echo load_js("pur.js",true);?>
<script type="text/javascript">
$(function(){
	//TAB选项卡
    $("#TabPage1 a").click(function() {
        tableStore.load();
        total_amount_search();
    });
    tableStore.on('beforeload', function(e) {
        e.params.check_tab = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });
	
})
function check_account(index, row){
	var d = {"deal_code": row.deal_code,'app_fmt': 'json'};
	 $.post('<?php echo get_app_url('acc/sell_settlement/do_check_account');?>', d, function(data){
		 var type = data.status == 1 ? 'success' : 'error';
         BUI.Message.Alert(data.message, type);
         tableStore.load();
	    
	 }, "json");
}

function view(deal_code) {
    var url = '?app_act=acc/sell_settlement/record_list&deal_code=' +deal_code
    openPage(window.btoa(url),url,'零售结算交易核销明细查询');
   }
$("#btn-search").click(function(){
	total_amount_search();
});

function total_amount_search(){
	var url = '?app_act=acc/sell_settlement/total_amount_search',
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
	    $("#total").html('应收款合计:'+data['total_fee']);
	 }, "json");
}

</script>

