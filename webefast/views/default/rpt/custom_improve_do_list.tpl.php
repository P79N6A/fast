<?php echo load_js('comm_util.js') ?>
<?php echo load_js('util/echarts.common.min.js'); ?>
<?php
render_control('PageHead', 'head1', array('title' => '会员增长量分析',
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
    'show_row' => 2,
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
<div class="row">
    <div id="b1" class="list"></div>
</div>
<div id="data_detail"></div>

<script type="text/javascript">
    $(function () {
        $('#month_start').val("<?php echo $year . '-01' ?>");
        $('#month_end').val("<?php echo $year . '-12' ?>");
        load_detail();
        $('#btn-search').click(function () {
            load_detail();
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


    /**
     * 选择显示类型
     */
    var g;
    var tab = "statistic_picture";
    var tab_list = {'statistic_list': 0, 'statistic_picture': 0};
    BUI.use('bui/toolbar', function (Toolbar) {
        //可勾选
        g = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: [
                {content: '图表', id: 'statistic_picture'},//,selected:true
                {content: '列表', id: 'statistic_list'},
            ],
            render: '#b1'
        });
        g.render();
        g.on('itemclick', function (ev) {
            tab_list[tab] = 0;
            tab = ev.item.get('id');
            load_detail();
        });
    });

    /**
     * 引入页面
     */
    function load_detail() {
        if (tab_list[tab] == 0) {
            $.post(
                "?app_act=rpt/custom_improve/" + tab, {},
                function (data) {
                    $("#data_detail").html(data);
                    if (tab == 'statistic_list') {
                        reload_data();
                    } else {
                        createChart();
                    }
                }
            )
            tab_list[tab] = 1;
        } else {
            if (tab_list['statistic_list'] == 1) {
                reload_data();
            } else if (tab_list['statistic_picture'] == 1) {
                createChart();
            }
        }
    }

    /**
     * 列表查询加载数据
     */
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
            case 'statistic_list':
                tableStore = statistic_list_tableStore;
                break;
        }

        tableStore.load(obj, function (data, params) {
            $('.bui_page_table').val(_pageSize);
        });
    }

    //导出
    $('#exprot_list').click(function () {
        var url = '?app_act=sys/export_csv/export_show';
        //var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        var params;
        var tab_id = $("#b1 .active").attr('id');
        if (tab_id === '' || tab_id === 'statistic_picture') {
            var month_start = $("#month_start").val();
            var month_end = $("#month_end").val();
            var shop_code = $("#shop_code").val();
            var sale_channel_code = $("#sale_channel_code").val();
                url = "?app_act=rpt/custom_improve/export_csv_list&app_fmt=json&month_start=" + month_start + "&month_end=" + month_end + "&shop_code=" + shop_code+ "&sale_channel_code=" + sale_channel_code;
            window.location.href = url;
        } else if (tab_id === 'statistic_list') {
            params = statistic_list_tableStore.get('params');
            params.ctl_export_conf = 'custom_improve_list';
            params.ctl_export_name = '会员增长量分析';
           <?php echo   create_export_token_js('crm/CustomerModel::get_improve_by_filter');?>
            var obj = searchFormForm.serializeToObject();
            for (var key in obj) {
                params[key] = obj[key];
            }
            params.ctl_type = 'export';
            for (var key in params) {
                url += "&" + key + "=" + params[key];
            }
            window.open(url);
        }
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
        var get_data_url = "<?php echo get_app_url('rpt/custom_improve/get_picture_data') ?>";
        // 基于准备好的dom，初始化echarts实例
        var charts = echarts.init(document.getElementById('charts'));
        $.get(get_data_url, url_params).done(function (data) {
            // 转成json
            data = JSON.parse(data);
            //填充数据，设置属性
            charts.setOption({
                //标题
                title: {
                    text: '',
                },
                tooltip: {},
                legend: {
                    left:'center',
                    data: ['月度新增会员情况'],
                    selectedMode: false
                },
                grid: {
                    left: '50',
                },
                xAxis: {
                    data: data.add_month
                },
                yAxis: {},
                series: [{
                    name: '月度新增会员情况',
                    type: 'bar',
                    data: data.num
                }]
            });
        });
    }

</script>




