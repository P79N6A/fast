<style>
    #group_bar .group_el_selected{color:red;}
    #b1{display:inline;}
    #start_time, #end_time{width: 100px;}
</style>
<?php render_control('PageHead', 'head1', array('title' => '销售订单分析','ref_table' => 'table')); ?>
<?php
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['deal_code'] = '交易号';
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
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ), 
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
         array(
            'label' => '单据状态',
            'type' => 'select',
            'id' => 'record_type',
            'data' => ds_get_select_by_field('record_type',0),
        ),
    )
));
?>
<div>
    <div class="row">
        <div class="span18">
               <select id="result">
               <option value="1">单级汇总</option>
               <option value="2">多级汇总</option>
               </select>
            <div id="b1"></div>
        </div>
    </div>
</div>

<!--<p>销售金额总计：200元     邮费总计：10元       销售数量总计：2件     退货数量总计：0件      退货金额总计：0元       订单总计：1单</p>-->
<div style="padding: 3px; ">
    <table style="width: 85% ">
        <tr>
            <td style="text-align: right; width: 150px;">销售金额总计：</td>
            <td><span id="paid_money"></span>元</td>

            <td style="text-align: right;">邮费总计：</td>
            <td><span id="express_money"></span>元</td>

            <td style="text-align: right;">销售数量总计：</td>
            <td><span id="goods_num"></span>件</td>

            <td style="text-align: right;">订单总计：</td>
            <td><span id="record_count"></span>单</td>
        </tr>
    </table>
</div>

<div id="data_detail">

</div>
<script>
 
    var g1, g2;
    var tab1 = "record";
    var tab_list1 = {'record': 0, 'sale_channel': 0, 'shop': 0,'store':0};
    var tab2 = "sale_channel";
    var tab_list2 = {'sale_channel': 0, 'shop': 0, 'store': 0, 'sale_channel_shop': 0, 'sale_channel_store': 0, 'sale_channel_shop_store': 0, 'shop_store': 0};
    $(document).ready(function () {
         find_result(); 
        $('#result').on('change',function(){   
              var b = $("#result").val();
              if(b==1){
              $("#b1").text('');
               $("#data_detail").text('');
              single();
           }else{
              $("#b1").text('');
              multiply(); 
              $("#data_detail").text('');
           }
        }); 

       $('#btn-search').click(function () {
          var b = $("#result").val();
              if(b==1){
                load_detail();      
              }else{
                load_detail_new();
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

    
    
    
    
   function single(){
    BUI.use('bui/toolbar',function(Toolbar){
        //可勾选
         g1 = new Toolbar.Bar({
            elCls : 'button-group',
            itemStatusCls  : {
                selected : 'active' //选中时应用的样式
            },
            defaultChildCfg : {
                elCls : 'button button-small',
                selectable : true //允许选中
            },
            children : [
                {content : '订单',id:'record'},
                {content : '销售平台',id:'sale_channel'},
                {content : '店铺',id:'shop'},
                {content : '仓库',id:'store'},
            ],
            render : '#b1'
        });
        g1.render();
        g1.on('itemclick',function(ev){
            tab_list1[tab1] = 0;
            tab1= ev.item.get('id');
            load_detail();
        });
    });
    }
    
    
    function multiply() {
    BUI.use('bui/toolbar', function (Toolbar) {
        //可勾选
        g2 = new Toolbar.Bar({
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
                {content: '仓库', id: 'store'},
            ],
            render: '#b1'
        });

        g2.render();
        g2.on('selectedchange', function () {
            var str = '',
            selection = g2.getSelection();
            BUI.each(selection, function (item) {
                str += item.get('id') + '_';
            });
           tab_list2[tab2] = 0;
           tab2 = str.substring(0, str.length - 1);
            if (tab2 == '') {
                tab2 = "sale_channel";
            }
            load_detail_new();
        });

    });
 }
  
    function load_detail() {
     if (tab_list1[tab1] == 0) {
        $.post(
            "?app_act=rpt/sell_record_statistic/"+tab1,
            {},
            function(data){
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
                "?app_act=rpt/sell_record_statistic/" + tab2,
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
                       case 'record':
                        tableStore = record_tableStore;
                        break;
                       case 'sale_channel':
                        tableStore = sale_channel_tableStore;
                        break;
                        case 'shop':
                        tableStore = shop_tableStore;
                        break;
                        case 'store':
                        tableStore = store_tableStore;
                        break;
                        default:
                        tableStore = record_tableStore;
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
                       case 'sale_channel':
                        tableStore = sale_channel_tableStore;
                        break;
                        case 'shop':
                        tableStore = shop_tableStore;
                        break;
                        case 'store':
                        tableStore = store_tableStore;
                        break;
                        case 'sale_channel_shop':
                        tableStore = sale_channel_shop_tableStore;
                        break;
                        case 'sale_channel_store':
                        tableStore = sale_channel_store_tableStore;
                        break;
                        case 'sale_channel_shop_store':
                        tableStore = sale_channel_shop_store_tableStore;
                        break;
                        case 'shop_store':
                        tableStore = shop_store_tableStore;
                        break;              
                        default:
                        tableStore = sale_channel_tableStore;
                }
        tableStore.load(obj, function (data, params) {
            $('.bui_page_table').val(_pageSize);
        });
    }


    $(document).ready(function() {
       $("#start_time").val("<?php echo $pay_time_start ?>");
        searchFormFormListeners['beforesubmit'].push(function(ev) {
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });

    });

    function count_all(obj) {
        $.post("?app_act=rpt/sell_report/report_count", obj, function(data) {
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
  //  var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
    var params;
     var id=[];
     $('#b1 ul').children('.active').each(function(i,e){
         id[i]=$(this).attr('id');
     });

    var tab_id = id.join("_");
          if(tab_id === ''){
           params =  record_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_statistic_record';
           <?php echo   create_export_token_js('oms/SellReportModel::get_statistic_record_data');?>
        }
       else if(tab_id === 'record'){
           params =  record_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_statistic_record';
          <?php echo   create_export_token_js('oms/SellReportModel::get_statistic_record_data');?>
        } else if (tab_id === 'sale_channel') {
           params =  sale_channel_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_statistic_sale_channel';
            <?php echo   create_export_token_js('oms/SellReportModel::get_statistic_sale_channel');?>
        } else if (tab_id === 'shop') {
           params =  shop_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_statistic_shop';
          <?php echo   create_export_token_js('oms/SellReportModel::get_statistic_shop_data');?>
        } else if (tab_id === 'store') {
           params =  store_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_statistic_store';
            <?php echo   create_export_token_js('oms/SellReportModel::get_statistic_sale_channel_store_data');?>
        } else if (tab_id=== 'sale_channel_shop') {
           params =  sale_channel_shop_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_statistic_sale_channel_shop';
          <?php echo   create_export_token_js('oms/SellReportModel::get_statistic_sale_channel_shop_data');?>
        } else if (tab_id === 'sale_channel_store') {
           params =  sale_channel_store_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_statistic_sale_channel_store';
          <?php echo   create_export_token_js('oms/SellReportModel::get_statistic_sale_channel_store_data');?>
        } else if (tab_id === 'sale_channel_shop_store') {
           params =  sale_channel_shop_store_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_statistic_sale_channel_shop_store';
          <?php echo   create_export_token_js('oms/SellReportModel::get_statistic_sale_channel_shop_store_data');?>                
        }else if (tab_id === 'shop_store') {
           params = shop_store_tableStore.get('params');
           params.ctl_export_conf = 'sell_record_statistic_shop_store';
           <?php echo   create_export_token_js('oms/SellReportModel::get_statistic_shop_store_data');?>                       
        }
        
        params.ctl_export_name =  '销售订单分析';
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