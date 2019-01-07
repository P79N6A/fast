jQuery(function () {
$('#btn-search').click(function(){
    if($("#highcharts_change_id").length>0) {
    $("#highcharts_change_id").attr('searched','y');
    $("#highcharts_change_id").text('hadclick');
    }
});
});


// 当满足条件时，才会执行变换图表
function check_highcharts(high_obj) {
    var curr_data = JSON.stringify(high_obj);
    var last_data = $("#highcharts_change_id").text();

    if (last_data != curr_data && high_obj.cate.length > 0 && $("#highcharts_change_id").attr('searched') == 'y') {
        $("#highcharts_change_id").text(curr_data);
        change_highcharts(high_obj.title, high_obj.cate, high_obj.data);
    }
}

function get_start_end_time() {
    start_time = $("#record_time_start").attr("value");
    end_time = $("#record_time_end").attr("value");
    time_obj = {'start': start_time, 'end': end_time};
    return time_obj;
}

/**
 [{'name':'汉字','data':'date'},{'name':'日期','data';'cuosme'}]
 * 获取ajax返回到页面的html，
 * 再获取其html ，整合图表所需要数据
 * 形式对象并返回
 * @returns {highcharts_obj} 
 */
function highcharts_data(title,cate, data_array) {
    var tr = $("tr.bui-grid-row");
    var tr_len = tr.length;
    var time_obj = get_start_end_time();
    var cate_obj = new Array();

    var data_len = data_array.length;
    var arr = new Array();


    //    构造一个与长度相等的json 对象 
    for (var m = 0; m < data_len; m++) {
        //name 的值 还得获取html
        arr[m] = {'name': data_array[m].name, 'data': ''};
        arr[m].data = new Array();
    }


    // 有数据则进行循环，循环则形式对象，无数据则返回空对象
    for (var i = 0; i < tr_len; i++) {
        month_cate = tr.eq(i).children('td[data-column-field="' + cate + '"]').text();
        if (cate == 'date')
            month_cate = month_cate;
        cate_obj[i] = month_cate;

        for (var j = 0; j < data_len; j++) {
            // alert(data_array[j]);
            var month_current = tr.eq(i).children('td[data-column-field="' + data_array[j].data + '"]').text();

            if (month_current == '')
                month_current = 0;
            else
                month_current = parseInt(month_current);

            arr[j].data[i] = month_current;

        }
    }

    

    return {'title':title +'('+ time_obj.start + "~"+ time_obj.end+')', 'cate': cate_obj, 'data': arr};
}

/**
 * 改变图表的数据，从而改变图表显示
 * @param {string} start  标题中的起始时间
 * @param {string} end    标题中的结束时间
 * @param {array of string} cate   查询的月份
 * @param {array of num} current   今年的增加数量
 * @param {array of number} yester  去年的增加数量
 * @returns viod
 */
function change_highcharts(title, cate, data) {
    $("#highcharts_change_id").attr('searched','n');
    $('#container_chart').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text:title
        },
        /*subtitle: {
         text: 'Source: WorldClimate.com'
         },*/
        xAxis: {
            categories: cate
        },
        yAxis: {
            min: 0,
            title: {
                text: '会员数量'
            }
        },
        tooltip: {
            headerFormat: '{point.key}<br>',
            pointFormat: '<span style="color:{series.color};padding:0">{series.name}: {point.y}</span><br>',
            footerFormat: '',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: data
    });
}