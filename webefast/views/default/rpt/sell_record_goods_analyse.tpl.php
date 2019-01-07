<style>
    #group_bar .group_el_selected{color:red;}
    .record{
        float: left;
    }
    #start_time, #end_time{width: 100px;}
</style>
<?php
    render_control('PageHead', 'head1', array('title' => '销售商品分析', 'ref_table' => 'table'));
?>
<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['combo_barcode'] = '套餐条形码';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['deal_code'] = '交易号';
$keyword_type['buyer_name'] = '会员昵称';
$keyword_type['fenxiao_name'] = '分销商名称';
$keyword_type = array_from_dict($keyword_type);
$pay_time_start = date("Y-m") . '-01 00:00:00';
$time_type = array();
$time_type['record_time'] = '下单时间';
$time_type['pay_time'] = '付款时间';
$time_type['plan_time'] = '发货时间';
$time_type = array_from_dict($time_type);
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
    'show_row' => 3,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
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
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ),
       array(
            'label' => '单据状态',
            'type' => 'select',
            'id' => 'record_type',
            'data' => ds_get_select_by_field('record_type',0),
        ),
//        array(
//            'label' => '付款时间',
//            'type' => 'group',
//            'field' => 'daterange2',
//            'child' => array(
//                array('title' => 'start', 'type' => 'date', 'field' => 'pay_time_start',),
//                array('pre_title' => '~', 'type' => 'date', 'field' => 'pay_time_end', 'remark' => ''),
//            )
//        ),
//        array(
//            'label' => '下单时间',
//            'type' => 'group',
//            'field' => 'daterange1',
//            'child' => array(
//                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
//                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
//            )
//        ),
//        array(
//            'label' => '发货时间',
//            'type' => 'group',
//            'field' => 'daterange3',
//            'child' => array(
//                array('title' => 'start', 'type' => 'date', 'field' => 'send_time_start',),
//                array('pre_title' => '~', 'type' => 'date', 'field' => 'send_time_end', 'remark' => ''),
//            )
//        ),
        array(
            'label' => '分类',
            'type' => 'select_multi',
            'id' => 'category_code',
            'data' => $response['category'],
        ),
        array(
            'label' => '季节',
            'type' => 'select_multi',
            'id' => 'season_code',
            'data' => ds_get_select('season_code'),
        ),
        array(
            'label' => '品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            'data' => $response['brand'],
        ),
        array(
            'label' => '年份',
            'type' => 'select_multi',
            'id' => 'year_code',
            'data' => ds_get_select('year'),
        ),
    )
));
?>

<div class="row">
    <div class="record">
        <select id="result" class="record">
           <option value="1">单级汇总</option>
           <option value="2">多级汇总</option>
            </select>
    </div>
     <div id="b1" class="record"></div>
</div>

<!--<p>销售金额总计：200元     邮费总计：10元       销售数量总计：2件     退货数量总计：0件      退货金额总计：0元       订单总计：1单</p>-->
<div style="padding: 3px;">
    <table style="width: 85%">
        <tr>
            <td style="text-align: right; width: 150px;">销售金额总计：</td>
            <td><span id="paid_money"></span>元</td>

            <td style="text-align: right;">邮费总计：</td>
            <td><span id="express_money"></span>元</td>

            <td style="text-align: right;">销售数量总计：</td>
            <td><span id="goods_num"></span>件</td>

            <td style="text-align: right;">退货数量总计：</td>
            <td><span id="return_num"></span>件</td>

            <td style="text-align: right;">退货金额总计：</td>
            <td><span id="return_money"></span>元</td>

            <td style="text-align: right;">订单总计：</td>
            <td><span id="record_count"></span>单</td>
        </tr>
    </table>
</div>

<div id="data_detail">

</div>
<script>
    var g1, g2;
    var tab1 = "sale_channel";
    var tab_list1 = {'sale_channel': 0, 'shop': 0, 'goods_code': 0,'sale_channel_shop':0,'sale_channel_goods_code':0,'sale_channel_shop_goods_code':0,'shop_goods_code':0};
    var tab2 = "sell_record";
    var tab_list2 = {'sell_record': 0, 'sale_channel': 0, 'shop': 0, 'goods_code': 0, 'barcode': 0, 'brand': 0, 'season': 0, 'category': 0,'years': 0};
    $(document).ready(function () {
         find_result(); 
        // load_detail_new();
        $('#result').on('change',function(){   
              var b = $("#result").val();
              if(b==1){
              $("#b1").text('');
               $("#data_detail").text('');
              single();
              //tab2 = "sell_record"
             // load_detail_new_copy();
           }else{
              $("#b1").text('');
              multiply(); 
              $("#data_detail").text('');
             // tab1 = "sale_channel"
              //load_detail_copy();
           }
        }); 

       $('#btn-search').click(function () {
          var b = $("#result").val();
              if(b==1){
                load_detail_new();
              }else{
                load_detail();   
              }       
        });
 });

     function find_result() {
      var b = $("#result").val();
           if(b==1){
              $("#b1").text('');
              single();
           }else{
              $("#b1").text('');
              multiply(); 
           }
     }
    
    
 function multiply() {
    BUI.use('bui/toolbar', function (Toolbar) {
        //可勾选
        g1 = new Toolbar.Bar({
            elCls: 'button-group',
            multipleSelect: true,
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: [
                {content: '销售平台', id: 'sale_channel'}, //,selected:true
                {content: '店铺', id: 'shop'},
                {content: '商品编码', id: 'goods_code'},
            ],
            render: '#b1'
        });

        g1.render();
        g1.on('selectedchange', function () {
            var str = '',
            selection = g1.getSelection();
            BUI.each(selection, function (item) {
                str += item.get('id') + '_';
            });
           tab_list1[tab1] = 0;
           tab1 = str.substring(0, str.length - 1);
            if (tab1 == '') {
                tab1 = "sale_channel";
            }
            load_detail();
        });

    });
 }
function single(){
    BUI.use('bui/toolbar', function (Toolbar) {
        //可勾选
        g2 = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: [
                {content: '商品明细', id: 'sell_record'},
                {content: '销售平台', id: 'sale_channel'}, //,selected:true
                {content: '店铺', id: 'shop'},
                {content: '商品编码', id: 'goods_code'},
                {content: '商品条形码', id: 'barcode'},
                {content: '品牌', id: 'brand'},
                {content: '季节', id: 'season'},
                {content: '分类', id: 'category'},
                {content: '年份', id: 'years'}
            ],
            render: '#b1'
        });
        g2.render();
        g2.on('itemclick', function (ev) {
            tab_list2[tab2] = 0;
            tab2 = ev.item.get('id');
            load_detail_new();
        });
    });
}
    function load_detail() {
    if (tab_list1[tab1] == 0) {
        $.post(
                "?app_act=rpt/sell_record_goods/" + tab1,
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

    function load_detail_new() {
         if (tab_list2[tab2] == 0) {
        $.post(
                "?app_act=rpt/sell_record_goods/" + tab2,
                {},
                function (data) {
                    $("#data_detail").html(data);
                     reload_data_new();
                }
        )
        tab_list2[tab2] = 1;
        } else {
            reload_data_new();
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
                        case 'sale_channel':
                        tableStore = sale_channel_tableStore;
                        break;
                        case 'shop':
                        tableStore = shop_tableStore;
                        break;
                        case 'goods_code':
                        tableStore = goods_code_tableStore;
                        break;
                        case 'sale_channel_shop':
                        tableStore = sale_channel_shop_tableStore;
                        break;
                    case 'sale_channel_goods_code':
                        tableStore = sale_channel_goods_code_tableStore;
                        break;
                    case 'sale_channel_shop_goods_code':
                        tableStore = sale_channel_shop_goods_code_tableStore;
                        break;   
                     case 'shop_goods_code':
                        tableStore = shop_goods_code_tableStore;
                        break;  
                    default:
                        tableStore = sale_channel_tableStore;
                }

        tableStore.load(obj, function (data, params) {
            $('.bui_page_table').val(_pageSize);
        });
    }

function reload_data_new() {
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

        switch (tab2) {
                      case 'sell_record':
                        tableStore = sell_record_tableStore;
                        break;
                      case 'sale_channel':
                        tableStore = sale_channel_tableStore;
                        break;
                        case 'shop':
                        tableStore = shop_tableStore;
                        break;
                        case 'goods_code':
                        tableStore = goods_code_tableStore;
                        break;
                        case 'barcode':
                        tableStore = barcode_tableStore;
                        break;
                    case 'brand':
                        tableStore = brand_tableStore;
                        break;
                    case 'season':
                        tableStore = season_tableStore;
                        break;   
                    case 'category':
                        tableStore = category_tableStore;
                        break; 
                     case 'years':
                        tableStore = years_tableStore;
                        break; 
                    default:
                        tableStore = sell_record_tableStore;
                }

        tableStore.load(obj, function (data, params) {
            $('.bui_page_table').val(_pageSize);
        });
    }





    $(document).ready(function () {
        $("#start_time").val("<?php echo $pay_time_start ?>");
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });

    });

    function count_all(obj) {
        $.post("?app_act=rpt/sell_report/report_count", obj, function (data) {
            $("#paid_money").html(data.paid_money);
            $("#express_money").html(data.express_money);
            $("#goods_num").html(data.goods_num);
            $("#record_count").html(data.record_count);
            $("#return_money").html(data.return_money);
            $("#return_num").html(data.return_num);
        }, "json");
    }

    
        $('#exprot_list').click(function(){
        var url = '?app_act=sys/export_csv/export_show';
       // var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        var params;
        
        //var tab_id = $("#bar2 .active").attr('id');
      var id=[];
     $('#b1 ul').children('.active').each(function(i,e){
         id[i]=$(this).attr('id');
     });

    var tab_id = id.join("_");
    if(tab_id === ''){
           params =  sell_record_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_sell_record'; 
          <?php echo   create_export_token_js('oms/SellReportModel::get_goods_sell_record');?>
    }
       else if(tab_id === 'sell_record'){
           params =  sell_record_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_sell_record';
          <?php echo   create_export_token_js('oms/SellReportModel::get_goods_sell_record');?>
        } else if (tab_id === 'sale_channel') {
           params =  sale_channel_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_sale_channel';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_sale_channel');?>
        } else if (tab_id === 'shop') {
           params =  shop_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_shop';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_shop_data');?>
        } else if (tab_id === 'goods_code') {
           params =  goods_code_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_goods_code';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_goods_code_data');?>
        }else if (tab_id === 'barcode') {
           params =  barcode_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_barcode';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_barcode_data');?>
        } else if (tab_id === 'sale_channel_shop') {
           params =  sale_channel_shop_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_sale_channel_shop';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_sale_channel_shop_data');?>
        } else if (tab_id === 'sale_channel_goods_code') {
           params =  sale_channel_goods_code_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_sale_channel_goods_code';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_sale_channel_goods_code_data');?>
        } else if (tab_id === 'sale_channel_shop_goods_code') {
           params =  sale_channel_shop_goods_code_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_sale_channel_shop_goods_code';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_sale_channel_shop_goods_code_data');?>
        }else if (tab_id === 'shop_goods_code') {
           params = shop_goods_code_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_shop_goods_code';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_shop_goods_code_data');?>
        }else if (tab_id === 'brand') {
           params = brand_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_brand';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_brand_data');?>
        }else if (tab_id === 'category') {
           params = category_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_category';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_category_data');?>
        }else if (tab_id === 'season') {
           params = season_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_season';
            <?php echo   create_export_token_js('oms/SellReportModel::get_goods_season_data');?>
        }else if (tab_id === 'years') {
           params = years_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_goods_years';
         <?php echo   create_export_token_js('oms/SellReportModel::get_goods_years_data');?>
                            
        }
        params.ctl_export_name =  '销售商品分析';
        var obj = searchFormForm.serializeToObject();
          for(var key in obj){
                 params[key] =  obj[key];
	  } 
     
        params.ctl_type = 'export';
          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
          window.open(url); 
    });
    
</script>