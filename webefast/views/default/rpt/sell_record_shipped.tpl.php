<style>
    #start_time, #end_time{width: 100px;}
</style>
<?php echo load_js('comm_util.js') ?>
<?php render_control('PageHead', 'head1',array('title'=>'订单发货数据分析','ref_table'=>'table'));?>

<?php
$keyword_type = array();
$keyword_type['express_no'] = '物流单号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['combo_barcode'] = '套餐条形码';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['deal_code'] = '交易号';
$keyword_type['buyer_name'] = '会员昵称';
$keyword_type['fenxiao_name'] = '分销商名称';
$keyword_type = array_from_dict($keyword_type);

$pay_time_start = date("Y-m") . '-01 00:00:00';
$time_type = array();
$time_type['plan_time'] = '发货时间';
$time_type['record_time'] = '下单时间';
$time_type['pay_time'] = '付款时间';
$time_type = array_from_dict($time_type);
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
    'show_row'=>3,
    'fields' => array(
        array(
            'label' => array('id' => 'time_type', 'type' => 'select', 'data' => $time_type),
            'type' => 'group',
            'field' => 'time_type',
            'data' => $time_type,
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'start_time', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'end_time', 'class' => 'input-small'),
            )
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('express'),
        ),
        array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',	
            'data'=>$keyword_type, 		
            'id' => 'keyword',	
        ),
        /*array(
            'label' => '发货时间',
            'type' => 'group',
            'field' => 'daterange3',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'send_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'send_time_end', 'remark' => ''),
            )
        ),*/
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ),
        array(
            'label' => ' 订单类型',
            'type'  => 'select',
            'id'    => 'sell_record_attr',
            'data'  => load_model('util/FormSelectSourceModel')->sell_record_fenxiao(),
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        /*array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),*/
        array(
            'label' => '支付方式',
            'type' => 'select_multi',
            'id' => 'pay_code',
            'data' => ds_get_select('pay_code'),
        ),
//        array(
//            'label' => '物流单号',
//            'type' => 'input',
//            'id' => 'express_no',
//            'title'=>'支持模糊查询'
//        ),
        array(
            'label' => '国家',
            'type' => 'select',
            'id' => 'country',
            'data' => ds_get_select("country",2),
        ),
        array(
            'label' => '收货省份',
            'type' => 'select',
            'id' => 'province',
            'data' => array(),
        ),
        array(
            'label' => '城市',
            'type' => 'select',
            'id' => 'city',
            'data' => array(),
        ),
        array(
            'label' => '区域',
            'type' => 'select',
            'id' => 'district',
            'data' =>array(),
        ),
//        array(
//            'label' => '商品编码',
//            'type' => 'input',
//            'id' => 'goods_code'
//        ),
//        array(
//            'label' => '商品条形码',
//            'type' => 'input',
//            'id' => 'barcode',
//            'title'=>'支持模糊查询'
//        ),
    )
));
?>

<div>
    <div class="row">
<!--        <div class="span4">
            <div id="b1"></div>
        </div>-->
        <div class="span18">
            <div id="b2"></div>
        </div>
    </div>
</div>

<div id="data_count">

</div>

<div id="data_detail">

</div>
<input type="button" name="exprot_shipped_sell_record" id="exprot_shipped_sell_record"  style="DISPLAY:none" />
<input type="button" name="exprot_shipped_sale_channel" id="exprot_shipped_sale_channel"  style="DISPLAY:none" />
<input type="button" name="exprot_shipped_shop" id="exprot_shipped_shop"  style="DISPLAY:none" />
<input type="button" name="exprot_shipped_store" id="exprot_shipped_store"  style="DISPLAY:none" />
<input type="button" name="exprot_shipped_express" id="exprot_shipped_express"  style="DISPLAY:none" />
<script type="text/javascript">
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    var tab = "sell_record"
    var tab_list = {'sell_record':0,'sale_channel':0,'shop':0,'store':0,'express':0};
    
    $(document).ready(function() {
        $("#start_time").val("<?php echo $pay_time_start ?>");
        $('#country').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,0,url);
        });
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,2,url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,3,url);
        });
        $('#country').val('0');
        $('#country').change();

        searchFormFormListeners['beforesubmit'].push(function(ev) {
            var obj = searchFormForm.serializeToObject();
            load_count(obj);
         
        });
        
        $('#btn-search').click(function(){
            load_detail(); 
        });

    });
    
    function load_detail() {
        if(tab_list[tab]==0){
            $.post(
                "?app_act=rpt/sell_record/shipped_"+tab,
                {},
                function(data){
                    $("#data_detail").html(data);
       
                      reload_data();
                    /*tableStore.on('beforeload', function(e) {
                        //e.params.contain_express_money = $("#contain_express_money").attr('checked')=='checked'?'1':'0';
                    });*/
                }
            );
            tab_list[tab] = 1;
        }else{
            reload_data();
        }
      
    }
    function reload_data(){
        
 
    	var obj = searchFormForm.serializeToObject();
        clear_nodata();
    	obj.start = 1; //返回第一页
		obj.page = 1; obj.pageIndex = 0;
		$('table_datatable .bui-pb-page').val(1);
    	var _pageSize = $('.bui_page_table').val();
    	obj.limit = _pageSize; obj.page_size = _pageSize; obj.pageSize = _pageSize;
//         {content : '明细',id:'sell_record',selected : true},
//                {content : '销售平台',id:'sale_channel'},
//                {content : '店铺',id:'shop'},
//                {content : '仓库',id:'store'},
//                {content : '配送方式',id:'express'} 
//        
                var tableStore = '';

                switch(tab){
                    case 'sale_channel':
                        tableStore = sale_channel_tableStore;
                        break;
                    case 'shop':
                        tableStore = shop_tableStore;
                        break;
                    case 'store':
                        tableStore = store_tableStore;
                        break;
                    case 'express':
                        tableStore = express_tableStore;
                        break;   
                    default:
                        tableStore = sell_record_tableStore;
                }
        
        
		tableStore.load(obj, function (data,params) {
			$('.bui_page_table').val(_pageSize);
                                     
		});
          
                

        
       //  $('#btn-search').click();
        
    }
    
    
    

    function load_count(obj) {
        $.post("?app_act=rpt/sell_record/shipped_"+tab+"_count",obj, function(data) {
            /*$("#paid_money").html(data.paid_money)
            $("#express_money").html(data.express_money)
            $("#goods_num").html(data.goods_num)
            $("#record_count").html(data.record_count)*/
            $("#data_count").html(data);
        });
    }
</script>

<script type="text/javascript">
    BUI.use('bui/toolbar',function(Toolbar){
        //可勾选
//        var g1 = new Toolbar.Bar({
//            elCls : 'button-group',
//            itemStatusCls  : {
//                selected : 'active' //选中时应用的样式
//            },
//            defaultChildCfg : {
//                elCls : 'button button-small',
//                selectable : true //允许选中
//            },
//            children : [
//                {content : '单级汇总',id:'single',selected : true},
//                {content : '多级汇总',id:'multiple'}
//            ],
//            render : '#b1'
//        });
//
//        g1.render();
//        g1.on('itemclick',function(ev){
//            //$('#l1').text(ev.item.get('id') + ':' + ev.item.get('content'));
//        });

        //可勾选
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
                {content : '明细',id:'sell_record',selected : true},
                {content : '销售平台',id:'sale_channel'},
                {content : '店铺',id:'shop'},
                {content : '仓库',id:'store'},
                {content : '配送方式',id:'express'}
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
    $('#exprot_list').click(function(){
        var tab_id = $("#bar2 .active").attr('id');
        if(tab_id === 'sell_record'){
            $("#exprot_shipped_sell_record").trigger("click");
        } else if (tab_id === 'sale_channel') {
            $("#exprot_shipped_sale_channel").trigger("click");
        } else if (tab_id === 'shop') {
            $("#exprot_shipped_shop").trigger("click");
        } else if (tab_id === 'store') {
            $("#exprot_shipped_store").trigger("click");
        } else if (tab_id === 'express') {
            $("#exprot_shipped_express").trigger("click");
        }
        
    })
    
    
//    $('#exprot_list').click(function(){
////        var url = tableStore.get('url');  
////        var url = '?app_act=sys/export_csv/export_show';
//        var params;
//        var url;
//        var tab_id = $("#bar2 .active").attr('id');
//        if(tab_id === 'sell_record'){
//         url = sell_record_tableStore.get('url');  
//           params =  sell_record_tableStore.get('params');
//           params.ctl_export_conf = 'sell_record_shipped_sell_record';
//        } else if (tab_id === 'sale_channel') {
//         url = sale_channel_tableStore.get('url');  
//                      params =  sale_channel_tableStore.get('params');
//           params.ctl_export_conf = 'sell_record_shipped_sale_channel';
//        } else if (tab_id === 'shop') {
//         url = shop_tableStore.get('url');  
//                    params =  shop_tableStore.get('params');
//           params.ctl_export_conf = 'sell_record_shipped_shop';
//        } else if (tab_id === 'store') {
//         url = store_tableStore.get('url');  
//                params =  store_tableStore.get('params');
//           params.ctl_export_conf = 'sell_record_shipped_store';
//        } else if (tab_id === 'express') {
//         url = express_tableStore.get('url');  
//               params =  express_tableStore.get('params');
//           params.ctl_export_conf = 'sell_record_shipped_express';
//        }
//        params.ctl_export_name =  '订单发货数据分析';
//        var obj = searchFormForm.serializeToObject();
//          for(var key in obj){
//                 params[key] =  obj[key];
//	  } 
//     
//        params.ctl_type = 'export';
//          for(var key in params){
//                url +="&"+key+"="+params[key];
//	  }
//        //  params.ctl_type = 'view';
//          window.open(url); 
//       // window.location.href = url;
//    });
</script>