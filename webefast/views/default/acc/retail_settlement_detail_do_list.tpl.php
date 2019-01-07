<style type="text/css">
    #check_accounts_time_start,#check_accounts_time_end{
        width: 110px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '网络订单应收明细',
    'links' => array(
    array('type'=>'js','js'=>"openPage(window.btoa('?app_act=acc/retail_settlement_detail/add&app_scene=add'),'?app_act=acc/retail_settlement_detail/add&app_scene=add','添加网络订单应收明细');", 'title'=>'添加网络订单应收明细', 'is_pop'=>false, 'pop_size'=>'500,400'),
    array('url' => 'acc/retail_settlement_detail/dz_import', 'title' => '导入核销', 'is_pop' => true, 'pop_size' => '500,350'),
),
    'ref_table' => 'table'
));
$keyword_type = array();
$keyword_type['deal_code'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['sell_return_code'] = '退单号';
$keyword_type = array_from_dict($keyword_type);
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
            'label' => array('id' => 'keyword_type','type' => 'select','data' => $keyword_type),
            'type' => 'input',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
//        array(
//            'label' => '交易号',
//            'type' => 'input',
//            'id' => 'deal_code'
//        ),
//        array(
//            'label' => '订|退单号',
//            'type' => 'input',
//            'id' => 'sell_record_code'
//        ),
        // array(
            // 'label' => '退单号',
            // 'type' => 'input',
            // 'id' => 'sell_return_code'
        // ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'source',
            'data' => load_model('base/SaleChannelModel')->get_select()
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '单据性质',
            'type' => 'select_multi',
            'id' => 'order_attr',
            'data' => ds_get_select_by_field("order_attr",0)
        ),
        array(
            'label' => '支付方式',
            'type' => 'select_multi',
            'id' => 'pay_type',
            'data' => ds_get_select('pay_type'),
        ),
        array(
        		'label' => '创建时间',
        		'type' => 'group',
        		'field' => 'daterange1',
        		'child' => array(
        				array('title' => 'start', 'type' => 'date', 'field' => 'create_time_start','value' => date('Y-m-01')),
        				array('pre_title' => '~', 'type' => 'date', 'field' => 'create_time_end', 'remark' => ''),
        		)
        ),
        array(
        		'label' => '核销时间',
        		'type' => 'group',
        		'field' => 'daterange2',
        		'child' => array(
        				array('title' => 'start', 'type' => 'time', 'field' => 'check_accounts_time_start'),
        				array('pre_title' => '~', 'type' => 'time', 'field' => 'check_accounts_time_end', 'remark' => ''),
        		)
        ),
        /*array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'create_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'create_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '付款时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '核销状态',
            'type' => 'select_multi',
            'id' => 'check_accounts_status',
            'data' => array(array('no_check','未核销'),array('part_check','部分核销'),array('have_check','已核销'),),
        ),*/
    )
));
if(isset($request['type']) && !empty($request['type']) && $request['type'] == 'view'){
    $all = true;
    $no_check = false;
}else{
     $all = false;
     $no_check = true;
}
render_control('TabPage', 'TabPage1', array(
		'tabs' => array(
				array('title' => '全部', 'active' => $all, 'id' => 'all'),
				array('title' => '未核销', 'active' => $no_check, 'id' => 'no_check'),
				array('title' => '部分核销', 'active' => false, 'id' => 'part_check'),
				array('title' => '已核销', 'active' => false, 'id' => 'have_check'),
				array('title' => '虚拟核销', 'active' => false, 'id' => 'dummy_check'),
				 

				 
		),
		'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
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
	        				array('id'=>'cancel_accounts_status', 'title' => '取消核销', 'callback'=>'cancel_account','show_cond'=>'obj.check_accounts_status == 40'),
	        		),
	        ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'sell_settlement_code',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据性质',
                'field' => 'order_attr_name',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据类型',
                'field' => 'order_type_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '结算类别',
                'field' => 'settle_type_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订（退）单号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'create_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '支付宝交易号',
                'field' => 'alipay_no',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '积分抵扣金额',
                'field' => 'point_fee',
                'width' => '100',
                'align' => ''
            ),
            /*array(
                'type' => 'text',
                'show' => 1,
                'title' => '金额',
                'field' => '',
                'width' => '100',
                'align' => ''
            ),*/
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '应收（退）金额',
                'field' => 'je',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '支付方式',
                'field' => 'pay_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人手机',
                'field' => 'receiver_mobile',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人地址',
                'field' => 'receiver_address',
                'width' => '250',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '物流单号',
                'field' => 'express_no',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '核销状态',
                'field' => 'check_accounts_status_name',
                'width' => '100',
                'align' => '',
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '收款时间',
            		'field' => 'account_month',
            		'width' => '100',
            		'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '核销时间',
                'field' => 'check_accounts_time',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '核销人',
                'field' => 'check_accounts_user_code',
                'width' => '100',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/SellSettlementModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'init' => 'nodata',
    'export'=> array('id'=>'exprot_list','conf'=>'retail_settlement_detail','name'=>'零售结算明细','export_type'=>'file'),
    'CellEditing' => true,
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
            array('title' => '系统规格', 'type' => 'text', 'width' => '200', 'field' => 'spec',),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
            array('title' => '商品数量', 'type' => 'text', 'width' => '100', 'field' => 'num'),
            array('title' => '商品均摊金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
        ),
        'page_size' => 10,
        'url' => get_app_url('acc/retail_settlement_detail/get_detail_list_by_deal_code&app_fmt=json'),
        'params' => 'deal_code,order_attr,settle_type,sell_record_code'
    ),
//     'events' => array(
//         'rowdblclick' => 'showDetail',
//     ),
));
?>
<script>
$(function(){
	//TAB选项卡
    $("#TabPage1 a").click(function() {
        tableStore.load();
    });
    tableStore.on('beforeload', function(e) {
        e.params.check_tab = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });
    tableStore.on('load', function(e) {
        $("td[data-column-field='settle_type']").each(function(){
            var settle_type = $(this).find("span").html();
            if(settle_type=='1'){
                $(this).parents("tr").find("i").hide();
            }
        });
    })
})
function check_account(index, row){
	var d = {"deal_code": row.deal_code,'app_fmt': 'json'};
	 $.post('<?php echo get_app_url('acc/sell_settlement/do_check_account');?>', d, function(data){
		 var type = data.status == 1 ? 'success' : 'error';
         BUI.Message.Alert(data.message, type);
         tableStore.load();
	    
	 }, "json");
}
function cancel_account(index, row){
    var d = {"deal_code": row.deal_code,'app_fmt': 'json'};
    $.post('<?php echo get_app_url('acc/sell_settlement/do_cancel_account');?>', d, function(data){
        var type = data.status == 1 ? 'success' : 'error';
        BUI.Message.Alert(data.message, type);
        tableStore.load();
    }, "json");
}
$(function(){
    var source = '<?php echo (isset($request['source']) && !empty($request['source'])) ? $request['source'] : ""; ?>';
    var shop_code = '<?php echo (isset($request['shop_code']) && !empty($request['source'])) ? $request['shop_code'] : ""; ?>';
    var month = '<?php echo (isset($request['month']) && !empty($request['month'])) ? $request['month'] : ""; ?>';

    if(source!='' || shop_code!=''){
        $("#searchForm #source").val(source);
        $("#searchForm #shop_code").val(shop_code);
        if(month != ''){
            $("#searchForm #create_time_start").val('<?php $create_time_start = date('Y-m-01', strtotime($request['month'])); echo $create_time_start;?>');
            $("#searchForm #create_time_end").val('<?php  $create_time_end = date('Y-m-d', strtotime("$create_time_start +1 month -1 day"));echo $create_time_end;?>');
        }else{
            $("#searchForm #create_time_start").val('');
        }
        $("#searchForm #source_select_multi .bui-select-input").click();
        $("#searchForm #shop_code_select_multi .bui-select-input").click();
        $("div[class='bui-list-picker bui-picker bui-overlay bui-ext-position x-align-bl-tl']").css("visibility","hidden");
        $("#searchForm #btn-search").click();
    }   
});
</script>
