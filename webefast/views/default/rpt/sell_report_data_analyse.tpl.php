<style>
    #group_bar .group_el_selected{color:red;}
    #exprot_list {width:120px;}
     #start_time, #end_time{width: 100px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '销售数据分析',
    'links' => array(
    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$pay_time_start = date("Y-m") . '-01 00:00:00';
$keyword_type = array();
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['deal_code'] = '交易号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['combo_barcode'] = '套餐条形码';
$keyword_type['buyer_name'] = '会员';
$keyword_type['fenxiao_name'] = '分销商名称';
$keyword_type = array_from_dict($keyword_type);

$time_type = array();
$time_type['plan_time'] = '发货时间';
$time_type['record_time'] = '下单时间';
$time_type['pay_time'] = '付款时间';
$time_type = array_from_dict($time_type);

render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出明细',
            'id' => 'exprot_list',
        ),
    ),
    'show_row' => 3,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
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
            'label' => '订单性质',
            'type'  => 'select_multi',
            'id'    => 'sell_record_attr',
            'data'  => load_model('util/FormSelectSourceModel')->sell_record_attr_xs(),
        ),
        array(
            'label' => '支付方式',
            'type' => 'select_multi',
            'id' => 'pay_type',
            'data' => ds_get_select('pay_type'),
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
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
        ),
        array(
            'label' => '付款时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '发货时间',
            'type' => 'group',
            'field' => 'daterange3',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'send_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'send_time_end', 'remark' => ''),
            )
        ),*/
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            'data' => $response['brand'],
        ),
        array(
            'label' => '季节',
            'type' => 'select_multi',
            'id' => 'season_code',
            'data' => ds_get_select('season_code'),
        ),
        array(
            'label' => '分类',
            'type' => 'select_multi',
            'id' => 'category_code',
            'data' => $response['category'],
        ),
    )
));
?>
<div>
    <div class="row">
        <div class="span18">
            <div id="b1"></div>
        </div>
    </div>
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
    
    var tab = "sell_record"
    $(document).ready(function() {
        load_detail();
    });
    
    
    BUI.use('bui/toolbar',function(Toolbar){
        //可勾选
        var g1 = new Toolbar.Bar({
            elCls : 'button-group',
            itemStatusCls  : {
                selected : 'active' //选中时应用的样式
            },
            defaultChildCfg : {
                elCls : 'button button-small',
                selectable : true //允许选中
            },
            children : [
                {content : '明细数据',id:'sell_record',selected : true},
                {content : '销售平台',id:'sale_channel'},
                {content : '店铺',id:'shop'},
                {content : '仓库',id:'store'},
                {content : '品牌',id:'brand'},
                {content : '季节',id:'season'},
                {content : '分类',id:'category'}
            ],
            render : '#b1'
        });

        g1.render();
        g1.on('itemclick',function(ev){
            tab = ev.item.get('id');
            load_detail();
        });
    });
    
    function load_detail() {
        $.post(
            "?app_act=rpt/sell_report/"+tab,
            {},
            function(data){
                $("#data_detail").html(data);
            }
        )
    }



    $(document).ready(function() {
        $("#start_time").val("<?php echo $pay_time_start?>");
        searchFormFormListeners['beforesubmit'].push(function(ev) {
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });

    });

    function count_all(obj) {
        $.post("?app_act=rpt/sell_report/data_report_count", obj, function(data) {
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
        params = tableStore.get('params');
        params.ctl_type = 'export';
        params.ctl_export_conf = 'rpt_sell_report_data_analyse_list';
        <?php echo   create_export_token_js('oms/SellReportModel::get_by_page');?>
        var tab_id = $("#b1 .active").attr('id');
        if(tab_id === 'sell_record'){
               <?php echo   create_export_token_js('oms/SellReportModel::get_by_page');?>
                  //    params.ctl_export_conf = 'rpt_sell_report_data_analyse_list';
        } else if (tab_id === 'sale_channel') {
                 <?php echo   create_export_token_js('oms/SellReportModel::get_sale_channel');?>
                   params.ctl_export_conf = 'sell_report_sale_channel';
        } else if (tab_id === 'shop') {
      <?php echo   create_export_token_js('oms/SellReportModel::get_shop_data');?>
                  params.ctl_export_conf = 'sell_report_shop';
        } else if (tab_id === 'store') {
                 <?php echo   create_export_token_js('oms/SellReportModel::get_store_data');?>
              params.ctl_export_conf = 'sell_report_store';
        } else if (tab_id === 'brand') {
                 params = brand_tableStore.get('params');
                params.ctl_type = 'export';
                params.ctl_export_conf = 'sell_report_brand';
                 <?php echo   create_export_token_js('oms/SellReportModel::get_brand_data');?>
            
        } else if (tab_id === 'season') {
             params = season_tableStore.get('params');
             params.ctl_type = 'export';
             params.ctl_export_conf = 'sell_report_season';
                 <?php echo   create_export_token_js('oms/SellReportModel::get_season_data');?>
         //  params.ctl_export_conf = 'sell_record_shipped_express';
        } else if (tab_id === 'category') {
                params = category_tableStore.get('params');
                params.ctl_type = 'export';
                params.ctl_export_conf = 'sell_report_category';
                <?php echo   create_export_token_js('oms/SellReportModel::get_category_data');?>
         //  params.ctl_export_conf = 'sell_record_shipped_express';
        }
        params.ctl_export_name =  '销售数据分析明细导出';
        var obj = searchFormForm.serializeToObject();
          for(var key in obj){
                 params[key] =  obj[key];
	  } 
     
          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
          params.ctl_type = 'view';
          window.open(url); 
      });
</script>