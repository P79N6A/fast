<style type="text/css">
    #delivery_time_start, #delivery_time_end{width: 100px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '积分报表统计分析',
    'links' => array(
        // array('url' => 'pur/purchase_record/detail&app_scene=add', 'title' => '添加采购入库单', 'is_pop' => true, 'pop_size' => '500,550'),
        // array('url' => 'pur/purchase_record/import&app_scene=add', 'title' => '导出', 'is_pop' => true, 'pop_size' => '500,300'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['deal_code_list'] = '交易号';
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
                    'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' =>$keyword_type),
                    'type' => 'input',
                    'title' => '',
                    'data' => $keyword_type,
                    'id' => 'keyword',	
        ),
        array(
            'label' => '交易结束日期',
            'type' => 'group',
            'field' => 'delivery_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'delivery_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'delivery_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' =>  $response['purview_shop'],
        ),
    )
));
?>
<div class="row">
    <div class="span18">
        <div id="b1"></div>
    </div>
</div>
<div id="data_detail"></div>
<script type="text/javascript">
    $(function () {
        $("#delivery_time_start").val('<?php echo date('Y-m') . '-01 00:00:00'; ?>');
        load_detail();
        $('#btn-search').click(function () {
            load_detail();
        });
    });
    var g1;
    var tab1 = "taobao";
    var tab_list1 = {'taobao': 0, 'jingdong': 0};

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
                {content: '淘宝', id: 'taobao'},// 'width': 100
                {content: '京东', id: 'jingdong'},// 'width': 100
            ],
            render: '#b1'
        });
        g1.render();
        g1.on('itemclick', function (ev) {
            tab_list1[tab1] = 0;
            tab1 = ev.item.get('id');
            load_detail();
        });
    });

    function load_detail() {
        if (tab_list1[tab1] == 0) {
            $.post("?app_act=rpt/integral_report/" + tab1, {}, function (data) {
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
            case 'taobao':
                tableStore = taobao_tableStore;
                break;
            case 'jingdong':
                tableStore = jingdong_tableStore;
                break;
            default:
                tableStore = taobao_tableStore;
        }

        tableStore.load(obj, function (data, params) {
            $('.bui_page_table').val(_pageSize);
        });
    }


    $('#exprot_list').click(function () {
        var url = '?app_act=sys/export_csv/export_show';
        //var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        var params = {};
        var tab_id = $("#b1 .active").attr('id');
        if (tab_id === '' || tab_id === 'taobao' || tab_id == undefined) {
            params = taobao_tableStore.get('params');
            params.ctl_export_conf = 'integral_taobao_list';
            <?php echo create_export_token_js('oms/SellReportModel::get_taobao_integral_data');?>
        } else if (tab_id === 'jingdong') {
            params = jingdong_tableStore.get('params');
            params.ctl_export_conf = 'integral_jingdong_list';
            <?php echo create_export_token_js('oms/SellReportModel::get_jingdong_integral_data');?>
        }

        params.ctl_export_name = '积分报表统计分析';
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

