<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>双十一看板</title>
        <script src="board/js/jquery.min.js"></script>
        <script src="board/js/highcharts.js"></script>
        <script src="board/js/jquery.handsontable.full.js"></script>
        <link rel="stylesheet" media="screen" href="board/css/jquery.handsontable.full.css">
        <style>
            body{
                margin: 2px 12px;
                padding: 0;
                font-size: 14px;
                font-family: tahoma, arial;
                background: #fff;
                min-height: 540px;
                min-width: 960px;
            }
            table{
                border-collapse: collapse;
            }
            th, td{
                padding: 4px;
            }

            fieldset{
                border: 1px solid #aaa;
            }
            .board .banner{width:100%; height:100%; position:fixed; left:0; top:0;}
            .board .banner .logo{display:block; float:top; position:absolute; left:60px; top:-135px;}
            .board .banner .pay_money{
                position: absolute;
                left: 56%;
                top: -35%;
                font-size: 60px;
                /*letter-spacing: 10px;*/
                color: yellow;
                font-weight: 700;
                font-family: 	STHeiti;
            }
            .board .banner .pay_money .head_td{font-size:15px;font-weight: 700;}
            .pay_money .head_td td{text-align: center;padding-bottom: 20px}
            .board .banner .suc_ratio{
                position: absolute;
                top: -9%;
                font-size: 20px;
                left: 130%;
                color: yellow;
                width: 100px;
                font-weight: 600;
            }
            .board .banner .suc_money{
                position:absolute;
                left:128%;
                top:23%;
                letter-spacing:8px;
                color: yellow;
                font-size: 2.3em
            }
            .board .banner .content{width:50%; height:55%; position:absolute; left:5%; top:30%;}
            .board .banner .update_time{
                color: white;
                position: absolute;
                left: 12%;
                top: 117.5%;
            }
            .board .banner .content #sku_sort{
                position: absolute;
                top: 48%;
                color: white;
                left: 108%;
                font-size: medium;
                width:70%;
            }
            #sku_sort table td{padding: 4px;}
            #sku_sort table .sku_rank{width: 8%;font-size: 1.2em;font-style: italic; }
            #sku_sort table .sku_name{width: 55%}
            #sku_money_name{
                position: absolute;
                top: 38%;
                color: #cdae00;
                left: 134%;
                font-size: medium;
                width: 100px;
                font-size: 1em;
                font-weight: lighter;
            }
        </style>
    </head>
    <bgsound id="mic" loop="0" src="">
        <audio controls="controls" id="scan_ok" style="display:none;">
            <source src="board/jb.wav" >
        </audio>
        <audio controls="controls" id="scan_error" style="display:none;">
            <source src="board/jb.wav" >
        </audio>
    </bgsound>
    <body>
        <div class="board">
            <div id="data" class="handsontable" style="display: none">
                <div class="handsontableInputHolder htHidden" style="top: 0px; left: 0px; overflow: hidden;">
                    <textarea class="handsontableInput" style="height: 0px; width: 0px; resize: none; overflow-y: hidden;"></textarea>
                </div>
            </div>
            <div class="banner">
                <img src="board/images/back_photo.jpg" width="100%" height="100%">
                <div class="content">
                    <img class="logo" src="board/images/logo.png">
                    <div class="pay_money">
                        <table>
                            <tr class="head_td"><td>亿</td><td>千万</td><td>百万</td><td></td><td>十万</td></tr>
                            <tr id="pay_money"></tr>
                        </table></div>
                    <div class="suc_ratio"><span id="suc_ratio">100%</span></div>
                    <div class="suc_money"><span id="suc_money"></span></div>
                    <div id="charts" style=" margin-left: -25px; margin-top: 60px; padding: 0px;" data-highcharts-chart="1"></div>
                    <div id="sku_money_name">(金额)</div>
                    <div id="sku_sort">
                        <table style="width:100%"></table>
                    </div>
                    <div class="update_time"><span id="update_time">0000-00-00 00:00:00</span>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            if (/firefox\/([\d.]+)/.test(navigator.userAgent.toLowerCase())) {
                HTMLElement.prototype.click = function () {
                    var evt = this.ownerDocument.createEvent('MouseEvents');
                    evt.initMouseEvent('click', true, true, this.ownerDocument.defaultView, 1, 0, 0, 0, 0, false, false, false, false, 0, null);
                    this.dispatchEvent(evt);
                };
            }

            var chart_data = [], chart_time = null;
            $(function () {
                $("#suc_money").text("<?php echo $response['sales_target'] ?>");
                setChartTimer();
            });
            function get_data_show() {
                $('#charts').height("99%");
                $('#charts').width("105%");
                var datagrid = get_tjt_data();
                var type = "column";
                var title = "各平台销售总金额";
                var ytitle = "订单金额";
                show_chart(type, title, ytitle, datagrid);
                return false;
            }
            function  get_tjt_data() {
                chart_data = chart_data.length == 0 ? [['暂无数据', '0']] : chart_data;
                chart_data.splice(0, 0, ["销售平台", '销售金额']);
                var table = $('#data').handsontable({
                    minRows: 1,
                    minCols: 1,
                    minSpareRows: 1,
                    minSpareCols: 1,
                    rowHeaders: true,
                    colHeaders: true,
                    data: chart_data
                }).data('handsontable');

                return table.getData();
            }
            function show_chart(type, title, ytitle, datagrid) {
                var series = [];
                var xlabels = [];
                var xtitle = datagrid[0][0];

                for (i = 1; i < datagrid[0].length - 1; i++) {
                    var y = datagrid[0][i];
                    if (y == '' || y == null) {
                        break;
                    }
                    series.push({
                        name: y,
                        data: []
                    });
                }
                for (i = 1; i < datagrid.length - 1; i++) {
                    var x = datagrid[i][0];
                    if (x == '' || x == null) {
                        continue;
                    }
                    xlabels.push(x);
                    for (j = 1; j < datagrid[i].length - 1; j++) {
                        if (datagrid[0][j].length == 0) {
                            continue;
                        }
                        var y = parseFloat(datagrid[i][j]);
                        if (!isNaN(y)) {
                            series[j - 1].data.push([x, y]);
                        } else {
                            series[j - 1].data.push([x, null]);
                        }
                    }
                }
                var tmp = [];
                for (i = 0; i < series.length; i++) {
                    if (series[i].data.length > 0) {
                        tmp.push(series[i]);
                        //	alert(series[i].data);
                    }
                }
                series = tmp;
                //alert(xlabels);
                //alert(series[0].data);

                $('#charts').html('');
                var charts = new Highcharts.Chart({
                    chart: {
                        renderTo: 'charts',
                        type: type
                    },
                    title: {
                        text: title
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: {
                        title: {
                            text: xtitle,
                            style: {
                                color: '#ffffff',
                                "font-size": '120%'
                            },
                            margin: 20
                        },
                        categories: xlabels,
                        min: 0, //
                        //minRange: 1,
                        minPadding: 1, //
                        labels: {
                            formatter: function () {
                                return this.value + '';
                            }
                        }
                    },
                    yAxis: {
                        title: {
                            text: ytitle,
                            style: {
                                color: '#ffffff',
                                "font-size": '120%'
                            },
                            margin: 20
                        },
                        labels: {
                            formatter: function () {
                                return this.value;
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                        formatter: function () {
                            return this.series.name + ': ' + this.y + ''; //
                        }
                    },
                    plotOptions: {
                        series: {
                            connectNulls: true
                        },
                        line: {
                            dataLabels: {
                                enabled: true
                            },
                            enableMouseTracking: true
                        },
                        spline: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                        bar: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                        column: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                        area: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                        pie: {
                            allowPointSelect: true,
                            showInLegend: true,
                            dataLabels: {
                                enabled: true,
                                formatter: function () {
                                    var p = this.percentage + '';
                                    var pos = p.indexOf('.');
                                    if (pos != -1) {
                                        p = p.substr(0, pos + 2);
                                    }
                                    return '<b>' + this.point.name + '</b>: ' + p + ' %';
                                }
                            }
                        }
                    },
                    colors: ['#dc2758', '#354287'],
                    series: series
                });
            }

            //单品排行榜刷新
            function refresh_goods_rank(goods_rank_data) {
                var goods_rank_html = '';
                $.each(goods_rank_data, function (key, val) {
                    goods_rank_html += '<tr><td class="sku_rank">' + (key + 1) + '.</td><td class="sku_name">' + val.goods_name + '</td><td class="sku_money">' + val.goods_money + '</td></tr>';
                });
                $("#sku_sort table").html(goods_rank_html);
            }

            //统计数据刷新
            function refresh_data() {
                $.post("?app_act=sys/census/get_board_data", {}, function (ret) {
                    //柱状图刷新
                    chart_data = ret.chart_data;
                    get_data_show();
                    //单品排行榜刷新
                    refresh_goods_rank(ret.goods_rank_data);
                    //统计总额刷新
                    var money_arr = ret.pay_money.split('');
                    var money_html = '';
                    $.each(money_arr, function (key, val) {
                        if (key != 0 && key % 3 == 0) {
                            money_html += '<td>,</td>';
                        }
                        money_html += '<td>' + val + '</td>';
                    });
                    $("#pay_money").html(money_html);
                    $("#suc_ratio").text(ret.suc_ratio);
                    $("#suc_money").text(ret.suc_money);
                    $("#update_time").text(ret.update_time);
                    $(".highcharts-legend").hide();

                    play_mid(0);
                }, "json");
            }

            //设置定时器
            function setChartTimer() {
                refresh_data();
                if (chart_time === null) {
                    chart_time = setInterval(refresh_data, 1000 * 60 * 5);
                }
            }

            //清除定时器
            function removeChartTimer() {
                if (chart_time !== null) {
                    clearInterval(chart_time);
                    chart_time = null;
                }
            }

            //提示音	status 0 成功  1失败
            function play_mid(status) {
                if (navigator.userAgent.indexOf('Firefox') >= 0) {//火狐
                    if (!status) {
                        scan_ok = document.getElementById('scan_ok');
                        scan_ok.play();
                    } else {
                        scan_error = document.getElementById('scan_error');
                        scan_error.play();
                    }
                } else {//IE
                    var _s = document.getElementById('mic');
                    if (!status) {
                        //	成功
                        _s.src = 'board/jb.wav';
                    } else {
                        //	失败
                        _s.src = 'board/jb.wav';
                    }
                }
            }
        </script>
    </body>
</html>


