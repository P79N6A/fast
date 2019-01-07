<style type="text/css">
    #export_list{
        border: none;
        color: #FFF;
        font-size: 18px;
        padding: 3px 2px 3px 2px;
        background-color: #1695ca;
    }
    #export_detail{
        border: none;
        color: #FFF;
        font-size: 18px;
        padding: 3px 2px 3px 2px;
        background-color: #1695ca;
    }




</style>

<?php echo load_js('comm_util.js') ?>
<?php echo load_js('acharts-min.js', true) ?>

<?php
render_control('PageHead', 'head1', array('title' => '包裹快递交接统计',
    'links' => array(
        array('url'=>'oms/package_delivery_receive/do_list', 'title'=>'包裹快递交接', 'is_pop'=>false),
    ),
    'ref_table' => 'table'
));
?>

<?php
$deliver_date = date("Y-m-d");
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type'=>'submit'
    ),
    array(
        'label' => '导出未交接包裹',
        'id' => 'export_list',
    ),
    array(
        'label' => '导出已交接包裹',
        'id' => 'export_detail',
    )
);

render_control('SearchForm', 'searchForm', array(
    'buttons' =>$buttons,
    'show_row'=>3,
    'fields' => array(
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '发货日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'deliver_date_start', 'value' => $deliver_date),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'deliver_date_end', 'remark' => ''),
            )
        ),
    )
));
?>
<div class="row">
    <div id="base_tab" class="list"></div>
</div>
<div id="base_data"></div>

<script type="text/javascript">
    $(function () {
        load_data();
        $('#btn-search').click(function () {
            load_data();
        });

        $('#export_detail').click(function(){
            export_action();
        })

    })

    /**
     * 选择显示类型
     */
    var g;
    var tab = "base_list";
    var tab_list = {'base_list': 0, 'base_picture': 0};
    BUI.use('bui/toolbar', function (Toolbar) {
        g = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active'  //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true    //允许选中
            },
            children: [
                {content: '列表', id: 'base_list'},    //selected:true
                {content: '图表', id: 'base_picture'},
            ],
            render: '#base_tab'
        });
        g.render();
        g.on('itemclick', function (ev) {
            tab_list[tab] = 0;
            tab = ev.item.get('id');
            load_data();
        });
    });

    /**
     * 引入页面
     */
    function load_data() {
        if (tab_list[tab] == 0) {
            $.post(
                "?app_act=oms/package_delivery_receive/" + tab, {},
                function (data) {
                    $("#base_data").html(data);
                    if (tab == 'base_list') {
                        reload_list();
                    } else {
                        create_chart();
                    }
                }
            )
            tab_list[tab] = 1;
        } else {
            if (tab_list['base_list'] == 1) {
                reload_list();
            } else if (tab_list['base_picture'] == 1) {
                create_chart();
            }
        }
    }
    /**
     * 列表加载数据
     */
    function reload_list() {
        var deliver_date_start = $("#deliver_date_start").val();
        var deliver_date_end = $("#deliver_date_end").val();
        var store_code = $("#store_code").val();
        base_list_tableStore.load({'deliver_date_start': deliver_date_start,'deliver_date_end': deliver_date_end,'store_code': store_code});

    }
   function export_action(){
       var url = '?app_act=sys/export_csv/export_show';
        //var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
       params = base_list_tableStore.get('params');
       params.ctl_type = 'export';
       params.ctl_export_conf = 'package_delivery_receive_list';
       params.ctl_export_name =  '已交接包裹明细';
       <?php echo   create_export_token_js('oms/PackageDeliveryReceivedModel::get_count_data');?>
       var obj = searchFormForm.serializeToObject();
       for(var key in obj){
           params[key] =  obj[key];
       }

       for(var key in params){
           url +="&"+key+"="+params[key];
       }
       params.ctl_type = 'view';
       window.open(url);
   }



    /**
     * 创建数据视图
     */
    function create_chart() {
        var deliver_date_start = $("#deliver_date_start").val();
        var deliver_date_end = $("#deliver_date_end").val();
        var store_code = $("#store_code").val();
        var params = {'deliver_date_start': deliver_date_start,'deliver_date_end': deliver_date_end,'store_code': store_code};
        var url = "?app_act=oms/package_delivery_receive/get_picture_data&app_fmt=json";
        var chart = '';
        $.post(url, params, function(data) {
            //chart.clear();
            $('#base_picture_table').children().remove();
            var json = data.data;
            var data_arr = new Array();
            $.each(json, function(key, val) {
                var arr = new Array();
                arr.push(val.name);
                arr.push(val.num);
                data_arr.push(arr);
            });
            chart = new AChart({
                theme: AChart.Theme.SmoothBase,
                id: 'base_picture_table',
                width: 550,
                height: 500,
                legend: null, //不显示图例
                tooltip: {
                    pointRenderer: function(point) {
                        return (point.percent * 100).toFixed(2) + '%';
                    }
                },
                series: [{
                    type: 'pie',
                    name: '包裹快递交接占比',
                    allowPointSelect: true,
                    radius: 120,
                    labels: {
                        distance: 8,
                        label: {
                            //   fill: '#F00'
                        },
                        renderer: function(value, item) { //格式化文本
                            return value + ' ' + (item.point.percent * 100).toFixed(2) + '%';
                        }
                    },
                    data: data_arr
                }]
            });
            chart.render();
        }, "json");
    }

    //导出未交接

</script>
