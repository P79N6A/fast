<style type="text/css">
    .container {
        margin: 5px 5px;
        padding: 0px;
    }
    .pull_top {
        /*margin-left: 400px;*/
        float: right;
        margin-bottom: 1px;
        font-size: 16px;
    }
</style>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js('util/echarts.js'); ?>
<?php echo load_js('util/china.js'); ?>
<?php
render_control('PageHead', 'head1', array('title' => '会员消费金额分析',
    'ref_table' => 'table'
));
?>
<?php
$year = date(Y);
render_control('SearchForm', 'searchForm', array(
    'buttons' =>
    array(
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
    // 'show_row' => 2,
    'fields' =>
    array(
        array(
            'label' => '日期',
            'type' => 'group',
            'field' => 'month',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'month_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'month_end', 'remark' => '', 'class' => 'input-small'),
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
            'data' => load_model('base/SaleChannelModel')->get_select()
        ),
    ),
));
?>
<div id="container_div" class="container">
    <div class="pull_top">
    </div>
    <table style="width:100%">
        <tr>
            <td id="td_charts" style="width:78%">
                <div id="charts"></div>
            </td>
            <td id="td_pull-right" style="width:19%;vertical-align:top;">
                <div id="pull-right">
                    <table id="list_data" style="width:100%; height:50%" border='1'>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

<script type="text/javascript">
    $(function () {
        // 根据页面跳转画布大小
        $('#charts').css({
            height: (parseInt($(window).height())) + 'px',
            width: parseInt($('#td_charts').width()),
        });

        $('#month_start').val("<?php echo $year . '-01' ?>");
        $('#month_end').val("<?php echo $year . '-12' ?>");
        createChart();
        $('#btn-search').click(function () {
            createChart();
        });
    })

    //加载月份
    BUI.use('bui/calendar', function (Calendar) {
        var dt = new Date();
        m = dt.getMonth();
        y = dt.getFullYear();
        var inputEl = $('#month_start'), monthpicker = new BUI.Calendar.MonthPicker({
            trigger: inputEl,
            autoHide: true,
            align: {
                points: ['bl', 'tl']
            },
            year: y,
            month: m,
            success: function () {
                var month = String(this.get('month') + 1),
                        year = this.get('year');
                var date = year + '-' + month;
                if (month.length < 2) {
                    date = year + '-0' + month;
                }
                inputEl.val(date);
                this.hide();
            }
        });
        monthpicker.render();
        monthpicker.on('show', function (ev) {
            var val = inputEl.val(),
                    arr, month, year;
            if (val) {
                arr = val.split('-');
                year = parseInt(arr[0]);
                month = parseInt(arr[1]);
                monthpicker.set('year', year);
                monthpicker.set('month', month - 1);
            }
        });
    });

    BUI.use('bui/calendar', function (Calendar) {
        var dt = new Date();
        m = dt.getMonth();
        y = dt.getFullYear();
        var inputEl = $('#month_end'), monthpicker = new BUI.Calendar.MonthPicker({
            trigger: inputEl,
            autoHide: true,
            align: {
                points: ['bl', 'tl']
            },
            year: y,
            month: m,
            success: function () {
                var month = String(this.get('month') + 1),
                        year = this.get('year');
                var date = year + '-' + month;
                if (month.length < 2) {
                    date = year + '-0' + month;
                }
                inputEl.val(date);
                this.hide();
            }
        });
        monthpicker.render();
        monthpicker.on('show', function (ev) {
            var val = inputEl.val(),
                    arr, month, year;
            if (val) {
                arr = val.split('-');
                year = parseInt(arr[0]);
                month = parseInt(arr[1]);
                monthpicker.set('year', year);
                monthpicker.set('month', month - 1);
            }
        });
    });

    //导出
    $('#exprot_list').click(function () {
        var month_start = $("#month_start").val();
        var month_end = $("#month_end").val();
        var shop_code = $("#shop_code").val();
        var sale_channel_code = $("#sale_channel_code").val();
        var url = "?app_act=rpt/custom_consume/export_csv_list&app_fmt=json&month_start=" + month_start + "&month_end=" + month_end + "&shop_code=" + shop_code + "&sale_channel_code=" + sale_channel_code;
        window.location.href = url;
    });


    /**
     * 创建数据视图
     */
    function createChart() {
        // 异步跳转参数
        var url_params = {
            month_start: $("#month_start").val(),
            month_end: $("#month_end").val(),
            shop_code: $("#shop_code").val(),
            sale_channel_code: $("#sale_channel_code").val(),
        };
        // 异步获取数据地址
        var get_data_url = "<?php echo get_app_url('rpt/custom_consume/get_consume_data') ?>";
        // 基于准备好的dom，初始化echarts实例
        var chart = echarts.init(document.getElementById('charts'));
        $.get(get_data_url, url_params).done(function (data) {
            //list_data_append(data.list_data);
            data = JSON.parse(data);
            var list_data = data.list_data;
            var map_data = data.map_data;
            chart.setOption({
                //标题
                title: {
                    text: '',
                    subtext: '',
                    x: 'center'
                },
                //提示框组件
                tooltip: {
                    trigger: 'item'
                },
                //图例组件展现了不同系列的标记(symbol)，颜色和名字。可以通过点击图例控制哪些系列不显示
                legend: {
                    orient: 'vertical',
                    x: 'left',
                    data: [''],
                    selectedMode: false
                },
                dataRange: {
                    x: 'left',
                    y: 'bottom',
                    splitList: [
                        {start: 0, label: '有金额', color: 'green'},
                      //  {start: 51, end: 203, label: '平均（51-203）'},
//                        {start: 10, end: 200, label: '10 到 200（自定义label）'},
//                        {start: 5, end: 5, label: '5（自定义特殊颜色）', color: 'black'},
                      //  {end:0, label: '无',label: '',}
                    ],
                    //color: ['#E0022B', '#E09107', '#A3E00B']
                },
                toolbox: {
                    show: true,
                    orient: 'vertical',
                    x: 'right',
                    y: 'center',
                    feature: {
                        mark: {show: true},
                      //  dataView: {show: true, readOnly: false},
                      //  restore: {show: true},
                        saveAsImage: {show: true}
                    }
                },
                roamController: {
                    show: true,
                    x: 'right',
                    mapTypeControl: {
                        'china': true
                    }
                },
                series: [
                    {
                        name: '会员消费金额分析（元）',
                        type: 'map',
                        mapType: 'china',
                        roam: false,
                        itemStyle: {
                            normal: {
                                label: {
                                    show: true,
                                    textStyle: {
                                        color: "rgb(249, 249, 249)"
                                    }
                                }
                            },
                            emphasis: {label: {show: true}}
                        },
                        data: map_data
                    }
                ]
            });
            list_data_append(list_data);
        });
    }

    /**
     * 添加列表数据
     * @param {type} list_data
     * @returns {undefined}
     */
    function list_data_append(list_data) {
        var html = "<tr><th class='table_title'>排名</th><th class='table_title'>省份</th><th class='table_title'>消费金额</th></tr>";
        $.each(list_data, function (i, val) {
            html += "<tr><td>" + val.order + "</td><td>" + val.name + "</td><td>" + val.value + "</td></tr>";
        });
        $("#list_data").html(html);
    }
</script>




