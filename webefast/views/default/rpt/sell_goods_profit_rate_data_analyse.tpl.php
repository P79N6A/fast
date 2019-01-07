<style>
    #group_bar .group_el_selected{color:red;}
    #explain1{    
        position:absolute;
        top:180px;
        margin-left:200px;
    }
    #explain2{
        position:absolute;
        top:180px;
        margin-left:200px;
    }
    #delivery_date_start,#delivery_date_end,#record_time_start,#record_time_end,#pay_time_start,#pay_time_end,#time_start,#time_end{ width:100px; }
</style>
<?php render_control('PageHead', 'head1', array('title' => '销售商品毛利分析', 'ref_table' => 'table')); ?>
<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['sell_record_code'] = '订/退单号';
$keyword_type['deal_code'] = '交易号';
$keyword_type = array_from_dict($keyword_type);
$time_start = date("Y-m") . '-01 00:00:00';
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
// 	'show_row'=>3,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '发货时间',
            'type' => 'group',
            'field' => 'daterange3',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange4',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '付款时间',
            'type' => 'group',
            'field' => 'daterange5',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('shipping'),
        ),
        array(
            'label' => '订单性质',
            'type' => 'select_multi',
            'id' => 'sell_record_attr',
            'data' => load_model('util/FormSelectSourceModel')->sell_goods_profit(),
        ),
    )
));
?>
<div>
    <div class="row">
        <div class="span18">
            <div id="b1"></div>
<!--            <span id="explain1" class="explain" style="display:none; color:red">说明：商品销售额 = 订单商品均摊金额</span>
            <span id="explain2" class="explain" style="display:none; color:red">说明：商品销售额 = 订单商品均摊金额 - 退单商品均摊金额</span>-->
        </div>
    </div>
</div>

<!--<p>销售金额总计：200元     邮费总计：10元       销售数量总计：2件     退货数量总计：0件      退货金额总计：0元       订单总计：1单</p>-->
<div style="padding: 3px;">
    <table style="width: 85%">
        <tr>
            <td style="text-align: right; width: 150px;">商品销售总额：</td>
            <td><span id="all_avg_money"></span>元</td>

            <td style="text-align: right;">商品总成本：</td>
            <td><span id="all_goods_cost_price"></span>元</td>

            <td style="text-align: right;">商品总毛利：</td>
            <td><span id="all_goods_gross_profit"></span>元</td>
        </tr>
    </table>
</div>
<div id="data_detail">

</div>
<input type="button" name="exprot_sell_record" id="exprot_sell_record"  style="display:none" />
<input type="button" name="exprot_sell_record_and_return" id="exprot_sell_record_and_return"  style="display:none" />
<script>
    var g1;
    var tab1 = "sell_record";
    var tab_list1 = {'sell_record': 0, 'sell_record_and_return': 0, };
    $(document).ready(function () {
        $("#time_start").val("<?php echo $time_start ?>");
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });

        $('#btn-search').click(function () {
            load_detail();
        });
    });

    function count_all(obj) {
        var id = $("#b1 .active").attr('id');
        if (id == 'sell_record' || id == '') {
            $('#time_start').parent().prev().html('发货时间');
            $("#record_time_start").removeAttr("disabled");
            $("#record_time_end").removeAttr("disabled");
            $("#pay_time_start").removeAttr("disabled");
            $("#pay_time_end").removeAttr("disabled");
            $("#sell_record_attr_select_multi").find("input[type='text']").removeAttr("disabled");
            sell_record_attr_select.enable();//控件可用
            $.post("?app_act=rpt/sell_goods_profit_rate/report_count", obj, function (data) {
                $("#all_avg_money").html(data.all_avg_money);
                $("#all_goods_cost_price").html(data.all_cost_price);
                $("#all_goods_gross_profit").html(data.all_goods_gross_profit);
            }, "json");
        } else {
            $('#time_start').parent().prev().html('退货时间');
            $("#record_time_start").val("");
            $("#record_time_end").val("");
            $("#pay_time_start").val("");
            $("#pay_time_end").val("");
            $("#sell_record_attr").val('');
            $("#sell_record_attr_select_multi").find("input[type='text']").val('');
            $("#record_time_start").attr("disabled", "disabled");
            $("#record_time_end").attr("disabled", "disabled");
            $("#pay_time_start").attr("disabled", "disabled");
            $("#pay_time_end").attr("disabled", "disabled");
            $("#sell_record_attr_select_multi").find("input[type='text']").attr("disabled", "disabled");
            sell_record_attr_select.disable();//控件不可用
            $.post("?app_act=rpt/sell_goods_profit_rate/report_count_and_return", obj, function (data) {
                $("#all_avg_money").html(data.all_avg_money);
                $("#all_goods_cost_price").html(data.all_cost_price);
                $("#all_goods_gross_profit").html(data.all_goods_gross_profit);
            }, "json");
        }

    }

    BUI.use('bui/toolbar', function (Toolbar) {
        //可勾选
        g1 = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: [
                {content: '销售毛利', id: 'sell_record',selected:true}, //,selected : true
                {content: '退货毛利', id: 'sell_record_and_return', width: 100},
            ],
            render: '#b1'
        });
        g1.render();
        g1.on('itemclick', function (ev) {
            tab_list1[tab1] = 0;
            tab1 = ev.item.get('id');
            explain(tab1);
            load_detail();
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });
    });

//说明文字
    function explain(tab1) {
        if (tab1 == 'sell_record') {
            $("#explain2").hide();
            $("#explain1").show();
        } else if (tab1 == 'sell_record_and_return') {
            $("#explain1").hide();
            $("#explain2").show();
        } else {
            $("#explain1").hide();
            $("#explain2").hide();
        }

    }
    function load_detail() {
        if (tab_list1[tab1] == 0) {
            $('#exprot_sell_record_and_return').unbind('click');
            $('#exprot_sell_record').unbind('click');
            $.post(
                    "?app_act=rpt/sell_goods_profit_rate/" + tab1,
                    {},
                    function (data) {
                        $("#data_detail").html(data);
                        reload_data();
                    }
            )
            tab_list1[tab1] = 1;
        } else {
            reload_data();
        }
    }

    function reload_data() {
        var obj = searchFormForm.serializeToObject();
        clear_nodata();
        obj.start = 1; //返回第一页
        obj.page = 1;
        obj.pageIndex = 0;
        $('table_datatable .bui-pb-page').val(1);
        var _pageSize = $('.bui_page_table').val();
        obj.limit = _pageSize;
        obj.page_size = _pageSize;
        obj.pageSize = _pageSize;

        var tableStore = '';

        switch (tab1) {
            case 'sell_record':
                tableStore = sell_record_tableStore;
                break;
            case 'sell_record_and_return':
                tableStore = sell_record_and_return_tableStore;
                break;
            default:
                tableStore = sell_record_tableStore;
        }

        tableStore.load(obj, function (data, params) {
            $('.bui_page_table').val(_pageSize);
        });
    }
    $('#exprot_list').click(function(){
        var tab_id = $("#b1 .active").attr('id');
        if(tab_id === 'sell_record'){
            $("#exprot_sell_record").trigger("click");
        } else if (tab_id === 'sell_record_and_return') {
            $("#exprot_sell_record_and_return").trigger("click");
        }
    })



//    $('#exprot_list').click(function(){
//      var url = '?app_act=sys/export_csv/export_show';
//   //   var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
//      var params={};
//      var tab_id = $("#b1 .active").attr('id');
//
//    if(tab_id === undefined){
//		
//           params =  sell_record_tableStore.get('params');
//           params.ctl_export_conf = 'sell_goods_profit_rate_sell_record'; 
//    }
//       else if(tab_id === 'sell_record'){
//           params =  sell_record_tableStore.get('params');
//           params.ctl_export_conf = 'sell_goods_profit_rate_sell_record';
//        } else if (tab_id === 'sell_record_and_return') {
//           params =  sell_record_and_return_tableStore.get('params');
//           params.ctl_export_conf = 'sell_goods_profit_rate_sell_record_and_return';
//        } 
//        params.ctl_export_name =  '销售商品毛利分析';
//	//	console.log(tab_id,params);
//        var obj = searchFormForm.serializeToObject();
//          for(var key in obj){
//                 params[key] =  obj[key];
//	  } 
//     	  	
//        params.ctl_type = 'export';
//          for(var key in params){
//                url +="&"+key+"="+params[key];
//	  }
//	
//      window.open(url); 
//    });




</script>