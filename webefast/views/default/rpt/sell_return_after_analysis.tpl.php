<style>
    #receive_time_start,#receive_time_end,#create_time_start,#create_time_end{width:100px;}
</style>
<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '售后退货数据分析',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['sell_return_code'] = '退单号';
$keyword_type['return_express_no'] = '退单物流单号';
$keyword_type['sell_record_code'] = '原单号';
$keyword_type['buyer_name'] = '昵称';
$keyword_type['return_name'] = '退货人';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['barcode'] = '条形码';
$keyword_type['deal_code'] = '原单交易号';
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
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'data' => $keyword_type,
            'id' => 'keyword',
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
        array(
            'label' => '入库时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'receive_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'receive_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => ' 订单类型',
            'type'  => 'select',
            'id'    => 'sell_record_attr',
            'data'  => load_model('util/FormSelectSourceModel')->sell_record_fenxiao(),
        ),
        array(
            'label' => '退单类型',
            'type' => 'select',
            'id' => 'return_type',
            'data' => ds_get_select_by_field('return_type', 1),
        ),
        array(
            'label' => '退单说明',
            'title' => '退单说明',
            'type' => 'input',
            'id' => 'return_buyer_memo',
        ),
        array(
            'label' => '退单原因',
            'type' => 'select_multi',
            'id' => 'return_reason_code',
            'data' => ds_get_select('return_reason'),
        ),
        array(
            'label' => '退货仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '退单创建时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'create_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'create_time_end', 'remark' => ''),
            )
        ),
    )
));
?>

<div>
    <div class="row">
        <div class="span18">
            <div id="tabs"></div>
        </div>
    </div>
</div>

<div id="data_count">

</div>

<div id="data_detail">

</div>
<script type="text/javascript">
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    var tab = "sell_return";
    var tab_list = {'sell_return': 0, 'sale_channel': 0, 'shop': 0, 'return_reasons': 0};

    $(document).ready(function () {
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            var obj = searchFormForm.serializeToObject();
            load_count(obj);
        });

        $('#btn-search').click(function () {
            load_detail();
        });

    });

    function load_detail() {
        if (tab_list[tab] == 0) {
            $.post("?app_act=rpt/sell_return/after_" + tab, {}, function (data) {
                $("#data_detail").html(data);
                reload_data();
            });
            tab_list[tab] = 1;
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

        switch (tab) {
            case 'sale_channel':
                tableStore = sale_channel_tableStore;
                break;
            case 'shop':
                tableStore = shop_tableStore;
                break;
            case 'return_reasons':
                tableStore = return_reasons_tableStore;
                break;
            default:
                tableStore = sell_return_tableStore;
        }

        tableStore.load(obj, function (data, params) {
            $('.bui_page_table').val(_pageSize);
        });
    }




    function load_count(obj) {
        $.post("?app_act=rpt/sell_return/after_" + tab + "_count", obj, function (data) {
            $("#data_count").html(data);
        });
    }
</script>

<script type="text/javascript">
    BUI.use('bui/toolbar', function (Toolbar) {
        var g2 = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: [
                {content: '明细', id: 'sell_return', selected: true},
                {content: '销售平台', id: 'sale_channel'},
                {content: '店铺', id: 'shop'},
                {content: '退货原因', id: 'return_reasons'}
            ],
            render: '#tabs'
        });

        g2.render();
        g2.on('itemclick', function (ev) {

            tab_list[tab] = 0;
            tab = ev.item.get('id');
            load_detail();
        });
    });



    $('#exprot_list').click(function () {
        var url = '?app_act=sys/export_csv/export_show';
        var params;

        var tab_id = $("#bar2 .active").attr('id');
        if (tab_id === 'sell_return') {
            params = sell_return_tableStore.get('params');
            params.ctl_export_conf = 'sell_return_after_sell_return';
            params.ctl_export_name = '售后退货数据分析_明细';
            <?php echo   create_export_token_js('rpt/SellReturnReportModel::get_sell_return_analysis');?>
        } else if (tab_id === 'sale_channel') {
            params = sale_channel_tableStore.get('params');
            params.ctl_export_conf = 'sell_return_after_sale_channel';
            params.ctl_export_name = '售后退货数据分析_平台';
            <?php echo   create_export_token_js('rpt/SellReturnReportModel::get_sale_channel_analysis');?>
        } else if (tab_id === 'shop') {
            params = shop_tableStore.get('params');
            params.ctl_export_conf = 'sell_return_after_shop';
            params.ctl_export_name = '售后退货数据分析_店铺';
           <?php echo   create_export_token_js('rpt/SellReturnReportModel::get_shop_analusis');?>
        } else if (tab_id === 'return_reasons') {
            params = return_reasons_tableStore.get('params');
            params.ctl_export_conf = 'sell_return_after_return_reasons';
            params.ctl_export_name = '售后退货数据分析_退货原因';
           <?php echo   create_export_token_js('rpt/SellReturnReportModel::get_return_reasons_analysis');?>
        }
        
        var obj = searchFormForm.serializeToObject();
        for (var key in obj) {
            params[key] = obj[key];
        }

        params.ctl_type = 'export';
        for (var key in params) {
            url += "&" + key + "=" + params[key];
        }
        window.open(url);
    });
</script>