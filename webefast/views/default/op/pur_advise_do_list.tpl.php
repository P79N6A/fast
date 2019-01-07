<style type="text/css">
    .well {
        min-height: 100px;
    }
    #stock_num_min,#stock_num_max,#sale_week_num_min,#sale_week_num_max,#sale_month_num_min,#sale_month_num_max,#pur_num_min,#pur_num_max{
        width:60px;
    }
    #start_date,#end_date{width: 90px;}
    .pur_num{width:60px;}
    div.rate{display: inline; margin-left: 10px;margin-right: 10px;}
    div.rate span{color:red; margin-left: 5px;margin-right: 5px;}
    #dynamic_pin_rate,#sell_through_rate{margin-left: 10px;}
    #table_datatable{margin-top: 5px;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '商品补货建议',
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_code'] = '商品编码';
$keyword_type = array_from_dict($keyword_type);

$date_goods_num = array();
$data_goods_num['sale_week_num'] = '7天均销售';
$data_goods_num['sale_month_num'] = '30天均销售';
$data_goods_num['sale_two_month_num'] = '60天均销售';
$data_goods_num['sale_three_month_num'] = '90天均销售';
$data_goods_num = array_from_dict($data_goods_num);

$date_goods_total = array();
$date_goods_total['sale_week_num_all'] = '7天总销售';
$date_goods_total['sale_month_num_all'] = '30天总销售';
$date_goods_total['sale_two_month_num_all'] = '60天总销售';
$date_goods_total['sale_three_month_num_all'] = '90天总销售';
$date_goods_total = array_from_dict($date_goods_total);

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
            'label' => '品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            'data' => ds_get_select('brand_code'),
        //'data'=>$response['brand'],
        ),
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
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'id' => 'keyword',
            'data' => $keyword_type
        ),
        array(
            'label' => '在库总库存',
            'type' => 'group',
            'field' => 'num',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'stock_num_min'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'stock_num_max'),
            )
        ),
        array(
            'label' => array('id' => 'num_total', 'type' => 'select', 'data' => $date_goods_total),
            'type' => 'group',
            'field' => 'num_1',
            'data' => $date_goods_total,
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'total_num_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'total_num_end', 'remark' => '', 'class' => 'input-small'),
            )
        ),
        array(
            'label' => array('id' => 'is_num', 'type' => 'select', 'data' => $data_goods_num),
            'type' => 'group',
            'field' => 'num',
            'data' => $data_goods_num,
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'num_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'num_end', 'remark' => '', 'class' => 'input-small'),
            )
        ),
        array(
            'label' => '补货数',
            'type' => 'group',
            'field' => 'num',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'pur_num_min'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'pur_num_max'),
            )
        )
    )
));
?>
<div>
    仓库
    <select id="store_code">
        <?php foreach ($response['store'] as $val): ?>
            <?php echo '<option value="' . $val['store_code'] . '">' . $val['store_name'] . '</option> ' ?>
        <?php endforeach; ?>
    </select>
    <div class="rate">统计日期:
        <input type="text" id="count_date_start" class="input-normal calendar" value=""  />~
        <input type="text" id="count_date_end" class="input-normal calendar" value=""  />
        <span>动销率:</span><span id="dynamic_pin_rate">0 %</span>
        <span>售罄率:</span><span id="sell_through_rate">0 %</span>
        <img src="assets/images/tip.png" class="tip" style="height:23px;width:23px;cursor: pointer;" title="动销率 : 存在销售商品种类数/有库存记录的商品种类数;<br>
             售罄率 : 销售商品总数/已验收采购入库商品总数;<br>
             统计日期 : 仅对动销率和售罄率计算生效;<br>
             <span style='color:red;'>注意 : 仅仓库和统计日期影响动销率和售罄率计算</span>" />
    </div>
    列表数据生成时间:<?php
    if (isset($response['record']['end_time']) && !empty($response['record']['end_time'])) {
        echo date('Y-m-d H:i:i', $response['record']['end_time']);
    }
    ?>
</div>



<ul class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="create_pur()">生成采购订单</button></li>
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="advise_param_set()">补货公式说明及配置</button></li>
    <div class="front_close">&lt;</div>
</ul>
<script>
    $(function () {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() == "&lt;") {
                    $(".frontool").animate({left: '-100%'}, 1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                } else {
                    $(".frontool").animate({left: '0px'}, 1000);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                }
            });
        }

        tools();
    });
</script>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '补货数',
                'field' => 'pur_num',
                'width' => '100',
                'align' => '',
                'format_js' => array('type' => 'html', 'value' => '<input style="width: 60px;" type="text" id="{detail_id}_pur_num" name="" value="{pur_num}"  />'),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['goods_spec1_rename'],
                'field' => 'spec1_name',
                'width' => '75',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['goods_spec2_rename'],
                'field' => 'spec2_name',
                'width' => '75',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '近7天日均销量',
                'field' => 'sale_week_num',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '近7天销售总量',
                'field' => 'sale_week_num_all',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '近30天日均销量',
                'field' => 'sale_month_num',
                'width' => '120',
                'align' => ''
            ), array(
                'type' => 'text',
                'show' => 1,
                'title' => '近30天销售总量',
                'field' => 'sale_month_num_all',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '近60天日均销量',
                'field' => 'sale_two_month_num',
                'width' => '120',
                'align' => ''
            ), array(
                'type' => 'text',
                'show' => 1,
                'title' => '近60天销售总量',
                'field' => 'sale_two_month_num_all',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '近90天日均销量',
                'field' => 'sale_three_month_num',
                'width' => '120',
                'align' => ''
            ), array(
                'type' => 'text',
                'show' => 1,
                'title' => '近90天销售总量',
                'field' => 'sale_three_month_num_all',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '在库库存',
                'field' => 'stock_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '在途库存',
                'field' => 'road_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '可用库存',
                'field' => 'inv_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付款未发货数',
                'field' => 'wait_deliver_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '品牌',
                'field' => 'brand_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '吊牌价',
                'field' => 'sell_price',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分类',
                'field' => 'category_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '季节',
                'field' => 'season_name',
                'width' => '100',
                'align' => ''
            )
        )
    ),
    'dataset' => 'op/PurAdviseModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'detail_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'pur_record_list', 'name' => '补货建议'),
    'CheckSelection' => true,
    'init' => 'nodata',
));
?>

<script type="text/javascript">
    $(function () {
        $('#btn-search').click(function () {
            tableStore.on('beforeload', function (e) {
//                e.params = tableStore.get('params');
                e.params.store_code = $('#store_code').val();
                tableStore.set('params', e.params);
            });
        });

        refresh_rate();
    });

    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            autoRender: true,
            showTime: false
        });
    });


    function create_pur() {
        var detail = {};
        var select_data = tableGrid.getSelection();
        if (select_data.length == 0) {
            BUI.Message.Alert("请选择商品", 'error');
            return;
        }
        var i = 0;
        for (var k in select_data) {
            if ($("#" + select_data[k]['detail_id'] + "_pur_num").val() != '') {
                detail[i] = {'num': $("#" + select_data[k]['detail_id'] + "_pur_num").val(), 'sku': select_data[k]['sku']};
                i++;
            }
        }
        var url = '?app_act=pur/planned_record/detail&app_scene=add&app_show_mode=pop&&detail_data=' + encodeURIComponent(JSON.stringify(detail));
        PageHead_show_dialog(url, '添加采购订单', {w: 500, h: 550});

    }

    //参数配置
    function advise_param_set() {
        new ESUI.PopWindow("?app_act=op/pur_advise/get_param", {
            title: "参数配置",
            width: 800,
            height: 480,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                component("all", "view");
                //刷新按钮权限
                //btn_check()
            }
        }).show();
    }

    $("#store_code,#count_date_start,#count_date_end").on("change", function () {
        refresh_rate();
    });

    //刷新动销率和售罄率
    function refresh_rate() {
        var param = {};
        param.store_code = $('#store_code').val();
        param.count_date_tart = $('#count_date_start').val();
        param.count_date_end = $('#count_date_end').val();
        $.post("?app_act=op/pur_advise/get_rate", {param: param}, function (ret) {
            var data = ret.data;
            $("#dynamic_pin_rate").text(data.dynamic_pin_rate);
            $("#sell_through_rate").text(data.sell_through_rate);
        }, "json");
    }

    BUI.use('bui/tooltip', function (Tooltip) {
        var tips = new Tooltip.Tips({
            tip: {
                trigger: '.tip', //出现此样式的元素显示tip
                alignType: 'bottom-left', //默认方向
                elCls: 'tips tips-info',
                titleTpl: '<div class="tips-content" style="margin-left: 0px;">{title}</div>',
                offset: 10 //距离左边的距离
            }
        });
        tips.render();
    });
</script>




