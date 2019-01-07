<?php
    render_control('PageHead', 'head1', array('title' => '实物锁定详情查询', 'ref_table' => 'table'));
?>

<?php
$options = array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select(),
        ),
        array(
            'label' => '商品编号',
            'type' => 'input',
            'id' => 'goods_code'
        ),
        array(
            'label' => '条码',
            'type' => 'input',
            'id' => 'barcode',
            'title' => '系统SKU码/商品条形码',
        ),
        array(
            'label' => '单据类型',
            'type' => 'select_multi',
            'id' => 'order_type',
            'data' => ds_get_select_by_field('order_type',3)
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            'data' => load_model('base/SaleChannelModel')->get_select(),
            'help' => '仅针对网络订单和换货单'
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_shop(),
            'help' => '仅针对网络订单和换货单'
        ),
    )
);
if($response['lof_status']){
    $options['fields'][] = array('label' => '批次','type' => 'input','id' => 'lof_no');
}
render_control('SearchForm', 'searchForm', $options);

?>
<?php if($response['lof_status']){?>
<div>
    <div class="row">
        <div class="span18">
            <div id="b2"></div>
        </div>
    </div>
</div>
<?php }?>
<div id="data_detail">

</div>
<script type="text/javascript">
    var tab = "normal_view"
    var tab_list = {'normal_view': 0, 'lof_view': 0};
    
    $(document).ready(function() {
        $("#searchForm #store_code").val('<?php echo isset($response['filter']['store_code']) ? $response['filter']['store_code'] : ""; ?>');
        $("#searchForm #barcode").val('<?php echo isset($response['filter']['barcode']) ? $response['filter']['barcode'] : ""; ?>');
        $("#searchForm #goods_code").val('<?php echo isset($response['filter']['goods_code']) ? $response['filter']['goods_code'] : ""; ?>');
        $("#searchForm #lof_no").val('<?php echo isset($response['filter']['lof_no']) ? $response['filter']['lof_no'] : ""; ?>');   
        $("#searchForm #store_code_select_multi .bui-select-input").click();
        $("div[class='bui-list-picker bui-picker bui-overlay bui-ext-position x-align-bl-tl']").css("visibility","hidden");
        $("#searchForm #barcode").blur();
//        $("#btn-search").hide();
        load_detail(); 
    });
    
    function load_detail() {
        if(tab_list[tab]==0){
            $.post("?app_act=prm/inv/lock_detail_" + tab, {}, function(data){
                $("#data_detail").html(data);
                    reload_data();
                }
            );
            tab_list[tab] = 1;
        }else{
            reload_data();
        }
    }
    
    function reload_data(){
        clear_nodata();
    	var obj = searchFormForm.serializeToObject();
    	obj.start = 1; //返回第一页
		obj.page = 1; 
        obj.pageIndex = 0;
		$('table_datatable .bui-pb-page').val(1);
    	var _pageSize = $('.bui_page_table').val();
    	obj.limit = _pageSize; 
        obj.page_size = _pageSize; 
        obj.pageSize = _pageSize;
		tableStore.load(obj, function () {
			$('.bui_page_table').val(_pageSize);
                                     
		});
    }
    
    BUI.use('bui/toolbar',function(Toolbar){
        var g2 = new Toolbar.Bar({
            elCls : 'button-group',
            itemStatusCls  : {
                selected : 'active' //选中时应用的样式
            },
            defaultChildCfg : {
                elCls : 'button button-small',
                selectable : true //允许选中
            },
            children : [
                {content : '普通视图',id:'normal_view',selected : true},
                {content : '批次视图',id:'lof_view'},
            ],
            render : '#b2'
        });
        g2.render();
        g2.on('itemclick',function(ev){
            tab_list[tab] = 0;
            tab = ev.item.get('id');
            load_detail();
        });
    });
</script>

