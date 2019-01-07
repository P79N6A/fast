<?php echo load_js('util/echarts.common.min.js'); ?>
<?php 
    if($response['login_type'] == 2) {// 分销商登录显示分销商信息
?>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js("baison.js,record_table.js",true);?>
<style>
.panel-body{ padding:0;}
.table{ margin-bottom:0;}
.table tr{ padding:5px 0;}
.table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
.table th{ width:8.3%; text-align:center;}
.table td{ width:23%; padding:0 1%;}
#panel_html{
    margin-top:10px; 
}
</style>
<div class="panel record_table" id="panel_html">

</div>
<script>
            var is_edit = true;
            var data = [{
                "name": "custom_code",
                "title": "编号",
                "value": "<?php echo $response['data']['custom_code'] ?>",
                "type": "input",
            },
            {
                "name": "custom_name",
                "title": "名称",
                "value": "<?php echo $response['data']['custom_name'] ?>",
                "type": "input",
            },
            {
                "name": "custom_type",
                "title": "类型",
                "value": "<?php echo $response['data']['custom_type_name'] ?>",
                "type": "input",
            },
            {
                "name": "custom_grade",
                "title": "分类",
                "value": "<?php echo $response['data']['custom_grade'] ?>",
                "type": "input",
            },
            {
                "name": "contact_person",
                "title": "联系人",
                "value": "<?php echo $response['data']['contact_person'] ?>",
                "type": "input",
                "edit": true,
            },
            {
                "name": "mobile",
                "title": "手机号",
                "value": "<?php echo $response['data']['mobile']; ?>",
                "type": "input",
                "edit": true,
            },
            {
                "name": "tel",
                "title": "联系电话",
                "value": "<?php echo $response['data']['tel']; ?>",
                "type": "input",
                "edit": true,
            },
            {
                "name": "address_str",
                "title": "地址",
                "value": "<?php echo $response['data']['address_str'] ?>",
            },
            {
                "name": "address",
                "title": "详细地址",
                "value": "<?php echo $response['data']['address'] ?>",
                "type": "input",
                "edit": true,
            },
            {
                "name": "custom_price_type",
                "title": "结算价格",
                "value": "<?php echo $response['data']['custom_price_type'] ?>",
                "type": "input",
            },
            {
                "name": "custom_rebate",
                "title": "结算折扣",
                "value": "<?php echo $response['data']['custom_rebate'] ?>",
                "type": "input",
            },
            {
                "name": "settlement_method",
                "title": "运费结算方式",
                "value": "<?php echo $response['data']['settlement_method'] ?>",
                "type": "input",
            },
            {
                "name": "fixed_money",
                "title": "固定运费金额",
                "value": "<?php echo $response['data']['fixed_money'] ?>",
                "type": "input",
            },
            {
                "name": "yck_account_capital",
                "title": "账户余额",
                "value": "<?php echo $response['data']['yck_account_capital'] ?>",
                "type": "input",
            },];

        jQuery(function () {
            var r = new record_table();
            r.init({
                "id": "panel_html",
                "data": data,
                "is_edit": is_edit,
                "edit_url": "?app_act=base/custom/custom_do_edit&custom_id="+<?php echo $response['data']['custom_id'];?>,
            });
            $("#panel_html .btnFormEdit").click(function () {
                var html = '';
                html += '<select id="province" name="province"  onChange= "change(this,1);" data-rules="{required : true}">';
                html += '<option value ="">请选择省</option>';
    <?php foreach ($response['area']['province'] as $k => $v) { ?>
                    html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['province']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
    <?php } ?>
                html += '</select>';
                html += '<select id="city" name="city"  onChange= "change(this,2);" data-rules="{required : true}">';
                html += '<option value ="">请选择市</option>';
    <?php foreach ($response['area']['city'] as $k => $v) { ?>
                    html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['city']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
    <?php } ?>
                html += '</select>';
                html += '<select id="district" name="district"   data-rules="{required : true}">';
                html += '<option value ="">请选择区县</option>';
    <?php foreach ($response['area']['district'] as $k => $v) { ?>
                    html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['district']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
    <?php } ?>
                html += '</select>';
                $("#address_str").html(html);
            });
            $("#panel_html .btnFormCancel").click(function () {
                location.reload();
            });
        })
        
        function change(obj, level) {
            var url = '<?php echo get_app_url('base/store/get_area'); ?>';
            var parent_id = $(obj).val();
            areaChange(parent_id, level, url);
        }
    </script>
<?php } else { ?>
<style type="text/css">
    /*    #charts {
            width: 800px;
            height:600px;
            margin: 0 auto;
            display: inline-block;
        }

        #pull-right{
            display: inline-block;
            width: 180px;
        }*/

    .select_it {
        display: inline-block;
        margin-right: 30px;
        float: left;
        width:250px;
    }
    .select_it1 {
        display: inline-block;
        margin-top:3px;
        float: left;
        width:600px
    }
    .container {
        margin: 5px 5px;
        padding: 0px;
    }

    .table_title {
        font-weight: bold;
    }
    .pull_top {
        /*margin-left: 400px;*/
        float: right;
        margin-bottom: 1px;
        font-size: 16px;
    }
</style>
<div id="container_div" class="container">
    <div class="pull_top">
        <span>友情提示：看板数据5分钟更新一次，若需更新请点击<a href="#" onclick="update_data()" style="text-decoration:underline;">立即更新</a></span>&nbsp;&nbsp;&nbsp;&nbsp;
        <button onclick="old_friend()">打开旧版</button>
    </div>
    <div>
        <div class="select_it">
            <div id="shop_code">
                <p style="font-size:15px;font-weight: bold;">今日订单概况</p>
                <label>店铺：</label>
                <input id="shop_code_value" type="hidden" value="">
            </div>
        </div>
        <div class="select_it1">
            <label>日期：</label>
            <input id="time" disabled="disabled" type="text" class="calendar" value="<?php echo date('Y-m-d', strtotime('today')); ?>"/>
            <strong style="color: #E95513; font-size: 16px;">总交易金额为<span id="total_money"></span>元</strong>
        </div>
    </div>
    <table style="width:100%">
        <tr>
            <td id="td_charts" style="width:78%"><div id="charts"></div></td>
<!--            <td id="td_pull-right" style="width:19%">
                <div id="pull-right">
                    <p class="table_title">订单提醒</p>
                    <table style="width:100%; height:50%" border='1'>
                        <tr><th class="table_title">提醒类型</th><th class="table_title">定单数量</th></tr>
                        <tr>
                            <td><a href="" onclick="api_oeder_list()">转单失败</a></td>
                            <td><span id="fail_num"></span></td>
                        </tr>
                        <tr>
                            <td><a href="" onclick="record_list()">审单超时(超过24小时)</a></td>
                            <td><span id="chec_timeout"></span></td>
                        </tr>
                        <tr>
                            <td><a href="" onclick="overtime_list()">发货超时(超过24小时)</a></td>
                            <td><span id="overtime"></span></td>
                        </tr>
                        <tr>
                            <td><a href="" onclick="order_send_list()">回写失败</a></td>
                            <td><span id="write_fail"></span></td>
                        </tr>
                        <tr>
                            <td><a href="" onclick="question_list()">问题单</a></td>
                            <td><span id="problem"></span></td>
                        </tr>
                        <tr>
                            <td><a href="" onclick="short_list()">缺货单</a></td>
                            <td><span id="out_store"></span></td>
                        </tr>
                        <tr>
                            <td><a href="" onclick="pending_list()">挂起单</a></td>
                            <td><span id="pending"></span></td>
                        </tr>
                    </table>
                </div>
            </td>-->
        </tr>
    </table>
</div>
<script type="text/javascript">
    // 计时器
    var t = null; // 每过5分钟刷新数据
    $(function () {
        // 根据页面跳转画布大小
        $('#charts').css({
            height: (parseInt($(window).height()) - 100) + 'px',
            width: parseInt($('#td_charts').width()),
        });
//        $('#pull-right').css({
//            height: (parseInt($(window).height()) - 200) + 'px',
//            width: $('#td_pull-right').width()
//        });

        // 初始化日历
        BUI.use('bui/calendar', function (Calendar) {
            var datepicker = new Calendar.DatePicker({
                trigger: '.calendar',
                disabled: true,
                autoRender: true
            });
            // datepicker.on('selectedchange', createChart);
        });
        // 初始化选择框
        BUI.use('bui/select', function (Select) {
            select = new Select.Select({
                render: '#shop_code',
                valueField: '#shop_code_value',
                width: 200,
                items: <?php echo json_encode($response['shop'], JSON_UNESCAPED_UNICODE); ?>,
                autoRender: true
            });
            select.setSelectedValue('0');
            ;
            select.on('change', function () {
                removeChartTimer();
                setChartTimer();
            });
        });
        setChartTimer();
    });

    /**
     * 设置定时器，每5分钟刷新一次数据,并且运行一次
     */
    function setChartTimer() {
        createChart();
        if (t === null) {
            t = setInterval(createChart, 1000 * 60 * 5);
        }
    }

    /**
     * 清除5分钟定时器
     */
    function removeChartTimer() {
        if (t !== null) {
            clearInterval(t);
            t = null;
        }
    }

    /**
     * 创建数据视图
     */
    function createChart() {
        // 异步跳转参数
        var url_params = {
            time: $('#time').val(),
            shop_code: $('#shop_code_value').val(),
            task_id: <?php echo (int) $response['task_id']; ?>
        };
        // 柱状图对应名字
        var name_map = {
            done_0: "今日交易总数",
            done_1: '今日已转入交易数',
            done_2: '已确认订单数',
            done_3: '已拣货订单数',
            done_4: '今日已发货数',
            done_5: '今日已回写数',
            todo_0: '',
            todo_1: '未转入交易数',
            todo_2: '未确认订单数',
            todo_3: '未拣货订单数',
            todo_4: '未验货订单数',
            todo_5: '未回写订单数'
        };
        // 通过eval把name_map键名对应的键值存入该名字实现动态变量
        var chart_name = '';
        // 点击柱状图跳转的url
        var url_map = {
            done_0: {
                url: '?app_act=oms/sell_record/td_list',
                name: '平台交易列表'
            },
            done_1: {
                url: '?app_act=oms/sell_record/td_list',
                name: '平台交易列表'
            },
            done_2: {
                url: '?app_act=oms/sell_record/ex_list',
                name: '订单列表'
            },
            done_3: {
                url: '?app_act=oms/waves_record/do_list',
                name: '订单波次打印'
            },
            done_4: {
                url: '?app_act=oms/sell_record/shipped_list',
                name: '已发货订单列表'
            },
            done_5: {
                url: '?app_act=api/sys/order_send/index',
                name: '平台网单回写列表'
            },
            todo_0: '',
            todo_1: {
                url: '?app_act=oms/sell_record/td_list',
                name: '平台交易列表'
            },
            todo_2: {
                url: '?app_act=oms/sell_record/ex_list',
                name: '订单列表'
            },
            todo_3: {
                url: '?app_act=oms/sell_record/fh_list',
                name: '订单波次生成'
            },
            todo_4: {
                url: '?app_act=oms/sell_record/wait_shipped_list',
                name: '待验货订单列表'
            },
            todo_5: {
                url: '?app_act=api/sys/order_send/index',
                name: '平台网单回写列表'
            }
        };
        // 通过eval把url_map键名对应的键值存入该名字实现动态变量
        var chart_url = '';
        // 异步获取数据地址
        var get_data_url = "<?php echo get_app_url('sys/echarts/getChartsData') ?>";
        // 基于准备好的dom，初始化echarts实例
        // 网络订单监控
        var charts = echarts.init(document.getElementById('charts'));
        // 柱状图柱子样式
        var itemStyle = {
            // 普通状态样式
            normal: {},
            // 鼠标指向柱子时样式
            emphasis: {
                barBorderWidth: 1,
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
                shadowColor: 'rgba(0,0,0,0.5)'
            }
        };
        // 异步获取数据
        $.get(get_data_url, url_params).done(function (data) {
            // 转成json
            data = JSON.parse(data);
            // 格式化数据
            data = dataFormat(data);
            // 显示总交易金额
            $('#total_money').html(data.total_money);
            // 获取纵坐标刻度数字最大长度
            var all = data['up'].concat(data['down']);
            var maxlen = 0;
            all.forEach(function (item, index, array) {
                array[index] = parseInt(item);
                maxlen = Math.max(maxlen, String(array[index]).length);
            });
            var max = Math.max.apply(null, all);
            // 根据数组中的最大值和最小值来判断特殊情况
            if (max < 2) {
                maxlen = 1;
            }
            // 根据配置项生成柱状图（网络订单监控）
            charts.setOption({
                // 柱状图个类别颜色轮换
                color: ['#1695CA', '#E95513'],
                grid: {
                    // 对纵坐标刻度值进行适应
                    left: maxlen > 1 ? maxlen * 10 : 30,
                    right: 0,
                    bottom: 40
                },
                // 柱状图各颜色柱对应的含义
                legend: {
                    // 指定柱子类别
                    data: ['done', 'todo'],
                    // 不显示该工具
                    show: false
                },
                // 鼠标指向柱子时显示提示框
                tooltip: {
                    // 不显示提示框
                    show: false
                },
                // 横坐标显示样式
                xAxis: {
                    // 刻度下标签名称数组
                    data: [],
                    // 刻度样式
                    axisTick: {
                        // 不显示
                        show: false
                    },
                    // 刻度下标签样式
                    axisLabel: {
                        // 不显示
                        show: false
                    }
                },
                // 纵坐标显示样式
                yAxis: {
                    // 刻度样式
                    axisTick: {
                        // 不显示
                        show: false
                    },
                    // 刻度下标签
                    axisLabel: {
                        // 不显示
                        show: true
                    },
                    // 刻度分割线
                    splitLine: {
                        // 不显示
                        show: false
                    }
                },
                // 柱状图柱子样式
                series: [
                    {
                        // 柱子名称
                        name: 'done',
                        selectedMode: 'single',
                        // 标签内容
                        label: {
                            // 普通状态下样式
                            normal: {
                                textStyle: {
                                    fontSize: 18
                                },
                                // 显示
                                show: true,
                                // 显示在头部
                                position: 'top',
                                // 显示内容
                                formatter: function (params) {
                                    // 根据params.seriesName(done | todo) + '_' + params.dataIndex(0-9),来获取name_map得值
                                    eval('chart_name = name_map.' + params.seriesName + '_' + params.dataIndex);
                                    return chart_name + '\n\n' + params.value;
                                }
                            }
                        },
                        // 各柱子间距
                        barCategoryGap: '50%',
                        // 柱子类型为柱状图
                        type: 'bar',
                        // 将该类型转子堆叠到同一类俩
                        stack: 'one',
                        // 各柱子数据
                        itemStyle: itemStyle,
                        // 各柱子数据
                        data: data.up
                    },
                    {
                        // 柱子名称
                        name: 'todo',
                        selectedMode: 'single',
                        // 标签内容
                        label: {
                            // 普通状态下样式
                            normal: {
                                textStyle: {
                                    fontSize: 18
                                },
                                // 显示
                                show: true,
                                // 显示在底部
                                position: 'bottom',
                                // 显示内容
                                formatter: function (params) {
                                    // 根据params.seriesName(done | todo) + '_' + params.dataIndex(0-9),来获取name_map得值
                                    eval('chart_name = name_map.' + params.seriesName + '_' + params.dataIndex);
                                    // 如果首列，就显示空字符串，值为负数（改变柱子向下），但是值必须显示为正
                                    return params.dataIndex === 0 || params.value === 0 ? '' : chart_name + '\n\n' + (-1 * params.value);
                                }
                            }
                        },
                        // 各柱子间距
                        barCategoryGap: '50%',
                        // 柱子类型为柱状图
                        type: 'bar',
                        // 将该类型转子堆叠到同一类
                        stack: 'one',
                        // 各柱子数据
                        itemStyle: itemStyle,
                        // 各柱子数据
                        data: data.down
                    }
                ]
            });
            // 绑定点击事件
            charts.on('click', function (params) {
                console.log(params);
                // 根据params.seriesName(done | todo) + '_' + params.dataIndex(0-9),来获取url_map的值
                eval('chart_url = url_map.' + params.seriesName + '_' + params.dataIndex);
                // 跳转页面
                openPage(window.btoa(chart_url.url), chart_url.url, chart_url.name);
            });
            $('#fail_num').text(data.right[0]);
            $('#chec_timeout').text(data.right[1]);
            $('#overtime').text(data.right[2]);
            $('#write_fail').text(data.right[3]);
            $('#problem').text(data.right[4]);
            $('#out_store').text(data.right[5]);
            $('#pending').text(data.right[6]);
        });
    }

    function dataFormat(data) {
        data = data.data;
        data.total_money = data.down[0];
        data.down[0] = 0;
        // 向下的数据转为负数
        for (var i = 0, cnt = data.down.length; i < cnt; ++i) {
            data.down[i] *= -1;
        }
        // 转为整形
        data.up.forEach(function (item, index, array) {
            array[index] = parseInt(item);
        });
        data.right.forEach(function (item, index, array) {
            array[index] = parseInt(item);
        });
        return data;
    }

    function api_oeder_list() {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/td_list') ?>', '?app_act=oms/sell_record/td_list', '平台交易列表');
    }
    function record_list() {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/ex_list') ?>', '?app_act=oms/sell_record/ex_list', '订单列表');
    }
    function overtime_list() {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/record_deliver_overtime_list') ?>', '?app_act=oms/sell_record/record_deliver_overtime_list', '发货超时订单');
    }
    function order_send_list() {
        openPage('<?php echo base64_encode('?app_act=api/sys/order_send/index') ?>', '?app_act=api/sys/order_send/index', '网单回写列表');
    }
    function question_list() {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/question_list') ?>', '?app_act=oms/sell_record/question_list', '问题订单列表');
    }
    function short_list() {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/short_list') ?>', '?app_act=oms/sell_record/short_list', '缺货订单列表');
    }
    function pending_list() {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/pending_list') ?>', '?app_act=oms/sell_record/pending_list', '挂起订单列表');
    }
    function update_data() {
        var url = '?app_act=sys/echarts/saveChartsDataByTask&app_fmt=json';
        $.post(url, {}, function (json) {
            location.reload();
        }, 'json');
    }
    function old_friend() {
        openPage('<?php echo base64_encode('?app_act=sys/order_scan/view') ?>', '?app_act=sys/order_scan/view', '首页看板（旧版）');
//        window.location.href = '?app_act=sys/order_scan/view';
    }

</script>
<?php } ?>