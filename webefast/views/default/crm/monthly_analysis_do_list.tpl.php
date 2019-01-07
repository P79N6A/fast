<?php echo load_js('comm_util.js') ?>
<?php echo load_js('acharts-min.js', true) ?>
<style type="text/css">
    #J_Month{
        width:100px;
    }
    .border_style1 {
        border:1px #000000 solid;
    }
    .td1-style {font-weight:bold;font-size: 14px;text-align: center;}
    .td2-style {font-weight:600;width:100px;}
    .detail-row [class*="span"]{float:left;margin-left: 1px;}
    .table .span2 {width:80px;}

    .detail-row {text-align: center;padding: auto;}
    .button-common {
        padding: 0 4px;
        font-size: 12px;
        line-height: 17px;
    }
</style>
<div class="row">
    <div>
        <div class="row">
            <div class="span14 doc-content">
                <form class="form-horizontal" style="padding: 10px;">
                    <input type="text" id="J_Month" value="<?php echo date("Y-m") ?>">
                    <select id="shop_code">
                        <option value="">请选择店铺</option>
                        <?php foreach ($response['shop_info'] as $shop) { ?>
                            <option value="<?php echo $shop['shop_code'] ?>"><?php echo $shop['shop_name']; ?></option>
                        <?php } ?>
                    </select>
                    <button type="botton" class="button" id="search_info" onclick ="return false;">搜索</button>
                    <input type="hidden" id="search1" value="<?php echo $request['year_month'];?>">
                    <input type="hidden" id="search2" value="">
                </form>
            </div>
        </div>
    </div>
    <div>
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <td width="320">
                    <div class="row">
                        <div class="td1-style">总体情况</div>
                        <div class="row detail-row">
                            <div class="span2">
                                <div><label class="control-label td2-style">销售金额</label></div>
                                <div><span class="control-label" id ="sale_money"></span></div>
                            </div>
                            <div class="span2 ">
                                <div><label class="control-label td2-style">商品销量</label></div>
                                <div><label class="control-label" id ="sale_goods_num"></label></div>
                            </div>
                            <div class="span2">
                                <div><label class="control-label td2-style">销售单笔数</label></div>
                                <div><label class="control-label" id ="sale_num"></label></div>
                            </div>
                        </div>
                        <div class="row detail-row">
                            <div class="span2">
                                <div><label class="control-label td2-style">退货金额</label></div>
                                <div><label class="control-label" id ="refund_money"></label></div>
                            </div>
                            <div class="span2 ">
                                <div><label class="control-label td2-style">商品退货量</label></div>
                                <div><label class="control-label" id ="refund_goods_num"></label></div>
                            </div>
                            <div class="span2">
                                <div><label class="control-label td2-style">退货单笔数</label></div>
                                <div><label class="control-label" id ="refund_num"></label></div>
                            </div>
                        </div>
                    </div>

                </td>
                <td width="400">
                    <div class="row">
                        <div class="td1-style">月度畅销商品
                            <button class="button button-info button-common " id="sell_well_num">数量</button>
                            <button class="button button-common" id="sell_well_money">金额</button>
                        </div>
                        <table cellspacing="0" class="table table-bordered" style="text-align:center" id="sell_well_report">

                        </table>
                        <div style="position:relative;height:20px"><a style="position:absolute;right:1px;cursor:pointer;" id="well_more">更多</a></div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="td1-style">月度滞销商品</div>
                        <table cellspacing="0" class="table table-bordered" style="text-align:center" id="sell_unsalable_report">

                        </table>
                        <div style="position:relative;height:20px;"><a style="position:absolute;right:1px;cursor:pointer;" id="unsalable_more">更多</a></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="row">
                        <div class="td1-style">活动情况(待开放)</div>
                        <div class="detail-section">
                            <div id="canvas1">

                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="td1-style">月度销售分类占比</div>
                        <div class="detail-section" >
                            <div id="canvas2">
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="td1-style">月度销售品牌占比</div>
                        <div class="detail-section">
                            <div id="canvas3">
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div id="monthly_sales_curve">
        <div class="td1-style">月度销售量曲线
<!--            <button class="button button-info button-common button-danger" id="month_sell_curves_num">数量</button>
            <button class="button button-info button-common" id="month_sell_curves_money">金额</button>-->

        </div>
        <div class="detail-section">
            <div id="canvas4">

            </div>
        </div>
    </div>

    <div id="monthly_return_curve">
        <div class="td1-style">月度销售金额曲线
<!--            <button class="button button-info button-common button-danger" id="month_return_curves_num">数量</button>
            <button class="button button-info button-common" id="month_return_curves_money">金额</button>-->

        </div>
        <div class="detail-section">
            <div id="canvas5">

            </div>
        </div>
    </div>
</div>
<input type="hidden" name="order_by" value="money">

<script type="text/javascript">

    var all_data = {};
    $(document).ready(function() {
        $("#search_info").click(function() {
            var month = $("#J_Month").val();
            var shop_code = $("#shop_code").val();

            if (month.length == 0 || shop_code.length == 0) {
                BUI.Message.Alert('请添写月份以及店铺', 'error');
                return;
            }
            $('#search1').val(month);
            $('#search2').val(shop_code);
            var params = {year_month: month, shop_code: shop_code};
            $.post("?app_act=rpt/oms_month_analysis/get_month_all&app_fmt=json", params, function(data) {
                all_data = data;
                set_sale_all();
                sell_well_report(0);
                sell_unsalable_report();
                category(0);
                brand(0);
                get_month_sell_report(0);
                get_month_return_report(0);

            }, "json");
            $('#well_more').show();
            $('#unsalable_more').show();
        });
        $('#well_more').hide();
        $('#unsalable_more').hide();
    });

    function set_sale_all() {
        var data = all_data['sale_all'];
        for (var key in data) {
            $('#' + key).html(data[key]);
        }

    }

    $("#sell_well_money").click(function() {
        $('input[name="order_by"]').val('money');
        $(this).addClass('button-info');
        $('#sell_well_num').removeClass('button-info');
        sell_well_report(1);
    });
    $("#sell_well_num").click(function() {
        $(this).addClass('button-info');
        $('input[name="order_by"]').val('num');
        $('#sell_well_money').removeClass('button-info');
        sell_well_report(0);    
    });
    $("#month_sell_curves_money").click(function() {
        $(this).addClass('button-danger');
        $('#month_sell_curves_num').removeClass('button-danger');
        get_month_sell_report(1);
    });
    $("#month_sell_curves_num").click(function() {
        $(this).addClass('button-danger');
        $('#month_sell_curves_money').removeClass('button-danger');
        get_month_sell_report(0);
    });
    $("#month_return_curves_money").click(function() {
        $(this).addClass('button-danger');
        $('#month_return_curves_num').removeClass('button-danger');
        get_month_return_report(1);
    });
    $("#month_return_curves_num").click(function() {
        $(this).addClass('button-danger');
        $('#month_return_curves_num').removeClass('button-danger');
        get_month_return_report(0);
    });


    var chart5 = '';
    function get_month_return_report(type) {
        if (chart5 != '') {
            chart5.clear();
            $('#canvas5').children().remove();
        }
        var data = {};
        var data2 ={};
      
            data = all_data['sale_data']['refund_money_data'];
             data2 = all_data['sale_data']['sale_money_data'];
 
        categories = all_data['sale_data']['month_data'];
        
        chart5 = new AChart({
            heme: AChart.Theme.Smooth2,
            id: 'canvas5',
            width: 1100,
            height: 450,
            plotCfg: {
                margin: [50, 50, 80] //画板的边距
            },
            xAxis: {
                categories: categories
            },
            seriesOptions: {//设置多个序列共同的属性
                lineCfg: {//不同类型的图对应不同的共用属性，lineCfg,areaCfg,columnCfg等，type + Cfg 标示
                    smooth: true,
                    labels: {//标示显示文本
                        label: {//文本样式
                            y: -15
                        },
                        //渲染文本
                        renderer: function(value, item) { //通过item修改属性
                            item.fill = 'red';
                            item['font-weight'] = 'normal';
                            item['font-size'] = 14;
                            return value;
                        }
                    }
                }
            },
            tooltip: {
                valueSuffix: 'Point',
                shared: true, //是否多个数据序列共同显示信息
                custom: true, //自定义tooltip
                crosshairs: true, //是否出现基准线
                itemTpl: '{value}'
            },
            series: [
                {
                    name: '销售金额',
                    data: data2
                }
                ,{
                    name: '退货金额',
                    data: data
                }]
        });
        chart5.render();
    }
    var chart4 = '';
    function get_month_sell_report(type) {
        if (chart4 != '') {
            chart4.clear();
            $('#canvas4').children().remove();
        }
        var data = {};
        var data2 = {};
  
            data = all_data['sale_data']['sale_data'];
            data2 = all_data['sale_data']['refund_data'];
   
           
   
        categories = all_data['sale_data']['month_data'];


        
        $("#canvas4").html("");
        chart4 = new AChart({
            heme: AChart.Theme.Smooth2,
            id: 'canvas4',
            width: 1100,
            height: 450,
            plotCfg: {
                margin: [50, 50, 80] //画板的边距
            },
            xAxis: {
                categories: categories
            },
            seriesOptions: {//设置多个序列共同的属性
                lineCfg: {//不同类型的图对应不同的共用属性，lineCfg,areaCfg,columnCfg等，type + Cfg 标示
                    smooth: true,
                    labels: {//标示显示文本
                        label: {//文本样式
                            y: -15
                        },
                        //渲染文本
                        renderer: function(value, item) { //通过item修改属性
                            item.fill = 'red';
                            item['font-weight'] = 'normal';
                            item['font-size'] = 12;
                            return value;
                        }
                    }
                }
            },
            tooltip: {
                valueSuffix: 'Point',
                shared: true, //是否多个数据序列共同显示信息
                custom: true, //自定义tooltip
                crosshairs: true, //是否出现基准线
                itemTpl: '{value}'
            },
            series: [{
                    name: '销售数量',
                    data: data
                },
                {
                    name: '退货数量',
                    data: data2
                }]
        });
        chart4.render();

    }


    var chart2 = '';
    function category(type) {
        if (chart2 != '') {
            chart2.clear();
            $('#canvas2').children().remove();
        }
        var data = {};
        if (type == 0) {
            data = all_data['cat_data']['num'];
        } else {
            data = all_data['cat_data']['money'];
        }
        var data2 = new Array();
        $.each(data, function(key, val) {
            var arr = new Array();
            arr.push(val.name);
            arr.push(val.num);
            data2.push(arr);
        });




        chart2 = new AChart({
            theme: AChart.Theme.SmoothBase,
            id: 'canvas2',
            width: 400,
            height: 400,
            legend: null, //不显示图例
            tooltip: {
                pointRenderer: function(point) {
                    return (point.percent * 100).toFixed(2) + '%';
                }
            },
            series: [{
                    type: 'pie',
                    name: '月度销售分类占比',
                    allowPointSelect: true,
                    radius: 130,
                    labels: {
                        distance: 10,
                        label: {
                            //   fill: '#F00'
                        },
                        renderer: function(value, item) { //格式化文本
                            return value + ' ' + (item.point.percent * 100).toFixed(2) + '%';
                        }
                    },
                    data: data2
                }]
        });
        chart2.render();
    }


    var chart3 = '';
    function brand(type) {
        if (chart3 != '') {
            chart3.clear();
            $('#canvas3').children().remove();
        }
        var data = {};
        if (type == 0) {
            data = all_data['brand_data']['num'];
        } else {

            data = all_data['brand_data']['money'];
        }
        var data2 = new Array();
        $.each(data, function(key, val) {
            var arr = new Array();
            arr.push(val.name);
            arr.push(val.num);
            data2.push(arr);
        });

        chart3 = new AChart({
            theme: AChart.Theme.SmoothBase,
            id: 'canvas3',
            width: 400,
            height: 400,
            legend: null, //不显示图例
            tooltip: {
                pointRenderer: function(point) {
                    return (point.percent * 100).toFixed(2) + '%';
                }
            },
            series: [{
                    type: 'pie',
                    name: '月度销售品牌占比',
                    allowPointSelect: true,
                    radius: 130,
                    labels: {
                        distance: 10,
                        label: {
                            // fill: '#F00'
                        },
                        renderer: function(value, item) { //格式化文本
                            return value + ' ' + (item.point.percent * 100).toFixed(2) + '%';
                        }
                    },
                    data: data2
                }]
        });
        chart3.render();
    }


    function sell_well_report(type) {
        var content = "<thead><tr><td>商品名称</td><td>条码</td><td>数量</td></tr></thead>";
        var data = all_data['goods_num_data'];
        if (type == 1) {
            content = "<thead><tr><td>商品名称</td><td>条码</td><td>金额</td></tr></thead>";
            data = all_data['gooods_money_data'];

        }

        var i = 1;
        for (var key in data) {
            content += '<tbody><tr>';
            content += '<td>' + data[key].goods_name + '</td>';
            content += '<td>' + data[key].barcode + '</td>';
            content += '<td>' + data[key].sku_value + '</td>';
            content += '</tr></tbody>';
            i++;
        }
        $("#sell_well_report").html(content);
    }

    function sell_unsalable_report() {
        var content = "<thead><tr><td>商品名称</td><td>条码</td><td>库存数</td></tr></thead>";
        var i = 1;
        var data = all_data['gooods_unsalable_data'];
        for (var key in data) {
            content += '<tbody><tr>';
            content += '<td>' + data[key].goods_name + '</td>';
            content += '<td>' + data[key].barcode + '</td>';
            content += '<td>' + data[key].sku_value + '</td>';
            content += '</tr></tbody>';
            i++;
        }
        $("#sell_unsalable_report").html(content);
    }

    BUI.use('bui/calendar', function(Calendar) {
        var inputEl = $('#J_Month'),
                monthpicker = new BUI.Calendar.MonthPicker({
                    trigger: inputEl,
                    // month:1, //月份从0开始，11结束
                    autoHide: true,
                    align: {
                        points: ['bl', 'tl']
                    },
                    //year:2000,
                    success: function() {
                        var month = this.get('month'),
                                year = this.get('year');
                        inputEl.val(year + '-' + (month + 1));//月份从0开始，11结束
                        this.hide();
                    }
                });
        monthpicker.render();
        monthpicker.on('show', function(ev) {
            var val = inputEl.val(),
                    arr, month, year;
            if (val) {
                arr = val.split('-'); //分割年月
                year = parseInt(arr[0]);
                month = parseInt(arr[1]);
                monthpicker.set('year', year);
                monthpicker.set('month', month - 1);
            }
        });
    });
    $('#well_more').click(function(){
        more_info(1);
    })
    $('#unsalable_more').click(function(){
        more_info(2);
    })
    function more_info(type){
        var fullurl = '';
        var _url = '';
        var title = '';
        var year_month = $('#search1').val();
        var shop_code = $('#search2').val();
        var order_by = $('input[name="order_by"]').val();
        if(type == 1){
            fullurl = '<?php echo $response["well_more"]["fullurl"];?>';
            _url = '<?php echo $response["well_more"]["_url"];?>';
            title = '月度畅销商品排行报表';
        }else if(type == 2){
            fullurl = '<?php echo $response["unsalable_more"]["fullurl"];?>';
            _url = '<?php echo $response["unsalable_more"]["_url"];?>';
            title = '月度滞销商品排行报表';
        }
        if(_url != ''){
            fullurl += '&year_month='+year_month+'&shop_code='+shop_code+'&order_by='+order_by;
        }
        openPage(_url,fullurl,title);
    }
</script>