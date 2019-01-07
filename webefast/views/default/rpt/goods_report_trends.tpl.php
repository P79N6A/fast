<style>
    #pay_time_start{width:100px;}
    #pay_time_end{width:100px;}
    #record_time_start{width:100px;}
    #record_time_end{width:100px;}
    #send_time_start{width: 100px;}
    #send_time_end{width: 100px;}
</style>
<?php echo load_js('comm_util.js') ?>
<?php render_control('PageHead', 'head1',
    array('title'=>'商品销售排行分析',
        'links'=>array(
        ),
        'ref_table'=>'table'
    ));?>

<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['combo_sku'] = '套餐条形码';
$keyword_type = array_from_dict($keyword_type);
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
    'show_row' => 2,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
//            'help' => '',
        ),
        array(
            'label' => '发货状态',
            'type' => 'select',
            'id' => 'shipping_flag',
            'data' => ds_get_select_by_field('record_type_trends',0),
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
            'field' => 'daterange1',
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
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
    )
));
?>

<div>
    <div class="row">
        <div class="span4 hide">
            <div id="b1"></div>
        </div>
        <div class="span18" style="margin-bottom:4px;">
            <div id="b2"></div>
        </div>
    </div>
</div>

<div id="data_count"></div>

<div id="data_detail"></div>

<div id="data_count_goods_barcode" style="display: none"></div>

<div id="data_detail_goods_barcode" style="display: none"></div>


<script type="text/javascript">
    var g2;
    var tab = "goods_code";
    var tab_list = {'goods_code': 0, 'goods_barcode': 0};
    load_detail(0);
    $(document).ready(function () {
        $('#btn-search').click(function () {
            load_detail(1);
        });
    });

//    $(document).ready(function() {
//        $("#searchForm").find(".row").eq(0).before($("#searchAdv").html())
//        $("#searchAdv").remove()
//
////        BUI.use('bui/toolbar', function(Toolbar) {
////            //可勾选
////            var b1 = [
////                {content: '全部', id: 'all'},
////                {content: '未发货', id: '0'},
////                {content: '已发货', id: '1', selected: true},
////            ];
////            toolbarmaker(Toolbar, b1, 'b1');
////        });
//    });
//        load_detail()
      //  load_count({})

//        searchFormFormListeners['beforesubmit'].push(function(ev) {
//            var obj = searchFormForm.serializeToObject();
//            load_count(obj)
//        });
		/*
        tableStore.on('beforeload', function(e) {
            e.params.shipping_flag = $("#b1").find(".active").attr("id");
        });
		*/

//    function load_detail() {
//        var id = 'data_detail';
//
//        if(typeof tableGrid != "undefined"){
//            tableGrid.remove();
//             tableStore.remove();
//        }
//
//        $.post(
//            "?app_act=rpt/goods_report/trends_"+tab,
//            {},
//            function(data){
//                $('#'+id).html(data);
//                /*tableStore.on('beforeload', function(e) {
//                 //e.params.contain_express_money = $("#contain_express_money").attr('checked')=='checked'?'1':'0';
//                 });*/
//
//
//                tableStore.on('beforeload', function(e) {
//                    e.params.shipping_flag = $("#b1").find(".active").attr("id");
//                    tableStore.set("params",e.params);
//                });
//                //tableStore.load();
//
//                $("#loading").hide();
//            }
//
//        reload_data();
//        );
//
//    }

    function load_detail(type) {
        if (tab_list[tab] == 0) {
            $.post(
                "?app_act=rpt/goods_report/trends_"+tab,
                {},
                function (data) {
                    $("#data_detail").html(data);
                    if(type==1){
                        reload_data();
                    }
                }
            )
            tab_list[tab] = 1;
        } else {
            if(type==1){
                reload_data();
            }
        }
    }

    BUI.use('bui/toolbar',function(Toolbar){

        //可勾选
         g2 = new Toolbar.Bar({
            elCls : 'button-group',
            itemStatusCls  : {
                selected : 'active' //选中时应用的样式
            },
            defaultChildCfg : {
                elCls : 'button button-small',
                selectable : true //允许选中
            },
            children : [
                {content : '商品编码',id:'goods_code',selected : true},//
                {content : '商品条形码',id:'goods_barcode'}
            ],
            render : '#b2'
        });
        g2.render();
        g2.on('itemclick',function(ev){
            tab_list[tab] = 0;
            tab = ev.item.get('id');
            load_detail(1);
        });
    });

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

        switch (tab) {
            case 'goods_code':
                tableStore = goods_code_tableStore;
                break;
            case 'goods_barcode':
                tableStore = goods_barcode_tableStore;
                break;
            default:
                tableStore = goods_code_tableStore;
        }
//        tableStore.on('beforeload', function(e) {
//            e.params.shipping_flag = $("#b1").find(".active").attr("id");
//            tableStore.set("params",e.params);
//        });
        tableStore.load(obj, function (data, params) {
            $('.bui_page_table').val(_pageSize);
        });
    }

    $('#exprot_list').click(function(){
        var url = '?app_act=sys/export_csv/export_show';
     //    var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        var params;
        var tab_id = $("#b2 .active").attr('id');
        if(tab_id === ''||tab_id === 'goods_code'||tab_id === undefined){
            params =  goods_code_tableStore.get('params');
            params.ctl_export_conf = 'rpt_trends_goods_code_list';
        <?php echo   create_export_token_js('rpt/SellGoodsReportModel::trends_goods_code');?>
        }
        else if(tab_id === 'goods_barcode'){
            params =  goods_barcode_tableStore.get('params');
            params.ctl_export_conf = 'rpt_trends_goods_barcode_list';
            <?php echo   create_export_token_js('rpt/SellGoodsReportModel::trends_goods_barcode');?>

        }
        params.ctl_export_name =  '商品销售排行分析';
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