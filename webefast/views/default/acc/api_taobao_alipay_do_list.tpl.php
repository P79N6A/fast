<style type="text/css">
    .well {
        min-height: 0px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '支付宝收支流水',
    'links' => array(
       // array('url' => 'acc/api_taobao_alipay/import&app_scene=add', 'title' => '导入', 'is_pop' => true, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
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
		    		'label' => '店铺',
		    		'title' => '',
		    		'type' => 'select_multi',
		    		'id' => 'shop_code',
		    		'data' => load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao')
		    ),
	      array(
		    		'label' => '交易号',
		    		'title' => '',
		    		'type' => 'input',
		    		'id' => 'deal_code'
		    ),
        array(
            'label' => '创建日期',
            'type' => 'group',
            'field' => 'create_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'create_time_start', 'value' => date('Y-m-01')),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'create_time_end', 'value' => date('Y-m-d')),
            )
        ),
    )
));
?>
<span id="total_in" style="color:red;">收入合计:<?php echo $response['total']['in_je']?></span>
<span id="total_out" style="color:red;">支出合计:<?php echo $response['total']['out_je']?></span>
<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
		
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
            		'title' => '交易号',
            		'field' => 'deal_code',
            		'width' => '140',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '支付宝订单号',
            		'field' => 'alipay_order_no',
            		'width' => '150',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '业务类型',
            		'field' => 'type',
            		'width' => '100',
            		'align' => '',
            ),
            
            
            array(
                'type' => 'text',
                'show' => 0,
                'title' => '账户余额',
                'field' => 'balance',
                'width' => '120',
                'align' => '',
                
            ),
            /*
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '应收金额',
            		'field' => '',
            		'width' => '100',
            		'align' => ''
            ),*/
            
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收入金额',
                'field' => 'in_amount',
                'width' => '100',
                'align' => ''
            ),
            
            
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '支出金额',
            		'field' => 'out_amount',
            		'width' => '100',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '创建时间',
            		'field' => 'create_time',
            		'width' => '150',
            		'align' => ''
            ),
            
            
            array(
            		'type' => 'text',
            		'show' => 0,
            		'title' => '商家支付ID',
            		'field' => 'self_user_id',
            		'width' => '100',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 0,
            		'title' => '买家支付ID',
            		'field' => 'opt_user_id',
            		'width' => '100',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 0,
            		'title' => '商户订单号',
            		'field' => 'merchant_order_no',
            		'width' => '100',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '备注',
            		'field' => 'memo',
            		'width' => '100',
            		'align' => ''
            ),
            
        )
    ),
    'dataset' => 'acc/ApiTaobaoAlipayModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'aid',
    'export'=> array('id'=>'exprot_list','conf'=>'api_taobao_alipay_list','name'=>'支付宝对账','export_type' => 'file'),
    'customFieldTable'=>'api_taobao_alipay_do_list/table',
    'init' => 'nodata',
    'events' => array(
        //'rowdblclick' => 'showDetail',
    ),
));
?>
<div style="clear:both"></div>
<div style="color: #F00">
温馨提示：<br>
支付宝流水下载仅支持系统自动下载，暂不支持手工导入，开启服务菜单路径“系统管理”->“系统管理”->“系统自动服务设置”,点击选项卡“高级应用服务”
</div>


<?php echo load_js("pur.js",true);?>
<script type="text/javascript">
$("#btn-search").click(function(){
	var start_time = $("#create_time_start").val();
	var end_time = $("#create_time_end").val();
	
	var start = new Date(start_time);
	if (end_time){
		var end = new Date(end_time);
	} else {
		var end = new Date();
	}
	var diff = (end.getTime() - start.getTime())/(24 * 60 * 60 * 1000);
	if (diff > 31){
		BUI.Message.Alert('检索条件创建时间跨度不能超过一个月，即31天', 'error');
		return false;
	}
	total_amount_search();
});
function total_amount_search(){
	var url = '?app_act=acc/api_taobao_alipay/alipay_total_amount_search',
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
	    $("#total_in").html('收入合计:'+data['in_je']);
	    $("#total_out").html('支出合计:'+data['out_je']);
	 }, "json");
}
$(document).ready(function(){
    //初始化按钮
	$("#tools").animate({left:'0px'},1000);
    $("#close_tools").click(function(){
        if($(this).html()=="&lt;"){
            $("#tools").animate({left:'-100%'},1000);
            $(this).html(">");
			$(this).addClass("tools_02").animate({right:'-10px'},1000);
        }else{
            $("#tools").animate({left:'0px'},1000);
            $(this).html("<");
			$(this).removeClass("tools_02").animate({right:'0'},1000);
        }
    });
});

$("#btn_opt_order_alipay_service").click(function(){
	//var url = '?app_act=base/shop/do_list&sell_record_code=' +sell_record_code
	var url = '?app_act=base/shop/do_list&shop_channel_code=B';
	openPage(window.btoa(url),url,'店铺列表');
});
  
    /**
     * 查看调整单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        location.href = "?app_act=wbm/store_out_record/view&store_out_record_id=" + row.store_out_record_id;
    }
    
    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=wbm/store_out_record/view&store_out_record_id') ?>'+row.store_out_record_id,'?app_act=wbm/store_out_record/view&store_out_record_id='+row.store_out_record_id,'批发销货单');
    }
</script>

