<style type="text/css">
    .well {
        min-height: 35px;
    }
    .span18 {
        width: 100% ;
    }  
</style>
<?php
render_control('PageHead', 'head1', array('title' => '采购统计分析',
    'links' => array(
    // array('url' => 'pur/purchase_record/detail&app_scene=add', 'title' => '添加采购入库单', 'is_pop' => true, 'pop_size' => '500,550'),
    // array('url' => 'pur/purchase_record/import&app_scene=add', 'title' => '导出', 'is_pop' => true, 'pop_size' => '500,300'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
//$keyword_type['record_code'] = '单据编号';
$keyword_type = array_from_dict($keyword_type);//dump($keyword_type);exit;
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
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '供应商',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'supplier_code',
            'data' => load_model('base/SupplierModel')->get_purview_supplier(),
            'value'=>$request['supplier_code'],
        ),
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '仓库',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
            'value'=>$request['store_code'],
        ),
        array(
            'label' => '品牌',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'goods_brand',
            'data' => $response['brand']
        ),
         array(
            'label' => '备注',
            'type' => 'input',
            'id' => 'remark',
            'help' => '支持模糊查询'
        ),
        array(
            'label' => '单据类型',
            'type' => 'select',
            'id' => 'record_type',
            'data' =>ds_get_select_by_field('purchase_record_type'),
            'help' => '该查询条件只在按单据维度页签起效'
        ),
    )
));
?>
<div style="padding: 3px;">
    <table style="width: 85%">
        <tr>
            <td style="text-align: right; width: 150px;">采购商品数量总计：</td>
            <td><span id="detail_num_all"></span>件</td>

            <td style="text-align: right;">采购商品金额总计：</td>
            <td><span id="detail_money_all"></span>元</td>

            <td style="text-align: right;">退回商品数量总计：</td>
            <td><span id="return_num_all"></span>件</td>

            <td style="text-align: right;">退货商品金额总计：</td>
            <td><span id="return_money_all"></span>元</td>
        </tr>
    </table>
</div>
<div class="row">
    <div class="span18">
        <div id="b1" style="float: left;"></div>       
        <div style="float: right;;height: 26px; margin-right: 30px ">
            <ul id="tool2" class="toolbar">        
                <li>
                    <label>排序类型：</label>
                    <select id="sort" name="sort" onchange="sort()" >
                        <option value="" >请选择</option>
                        <option value="record_time_desc">按业务日期倒序</option>
                    </select>
                </li>
            </ul>
        </div>
        
    </div>
</div>
<div id="data_detail"></div>
<script type="text/javascript">
    $(function() {
        $("#record_time_start").val("<?php echo $request['record_time_start']; ?>");
        $("#record_time_end").val("<?php echo $request['record_time_end']; ?>");
        load_detail();
        $('#btn-search').click(function () {
            load_detail();
        });
    });
    $("#remark").attr('disabled',true);//默认是备注筛选置灰
    $("#record_type").attr('disabled',true);//默认是单据类型筛选置灰
    var g1;
    var tab1 = "sku";
    var tab_list1 = {'sku': 0, 'record': 0};

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
                {content: '按商品维度', id: 'sku', 'width': 100},
                {content: '按订单维度', id: 'record', 'width': 100},
            ],
            render: '#b1'
        });
        g1.render();
        g1.on('itemclick', function (ev) {
            tab_list1[tab1] = 0;
            tab1 = ev.item.get('id');
            if (tab1 == 'record') {
                $('#keyword_type').append("<option value='record_code'>单据编号</option>");
                $("#remark").attr('disabled',false);
                $("#record_type").attr('disabled',false);
            }else{
                //$("#keyword_type option:last").remove();
                $("#keyword_type option[value='record_code']").remove();
                $("#remark").attr('disabled',true);
                $("#record_type").attr('disabled',true);
                $("#record_type").val('全部');
                var filter = searchFormForm.serializeToObject();
                count_all(filter);

            }
            load_detail();
        });
    });

    function load_detail() {
        var is_sort = '';
        var sort_e = $("#sort  option:selected");
        if (sort_e.length > 0) {
            is_sort = $("#sort  option:selected").val();
        }
        if (tab_list1[tab1] == 0) {
            $.post(
                "?app_act=pur/purchase_analyse/" + tab1, {is_sort:is_sort}, function (data) {
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
            case 'record':
                tableStore = record_tableStore;
                break;
            case 'sku':
                tableStore = sku_tableStore;
                break;
            default:
                tableStore = sku_tableStore;
        }

        tableStore.load(obj, function (data, params) {
            $('.bui_page_table').val(_pageSize);
        });
    }
    //排序
    function sort() {
        if (tab_list1['sku'] === 1) {
            page_select = 'sku';
        } else {
            page_select = 'record';
        }
        var is_sort = '';
        var sort_e = $("#sort  option:selected");
        if (sort_e.length > 0) {
            is_sort = $("#sort  option:selected").val();
        }
        $.post(
            "?app_act=pur/purchase_analyse/"+page_select ,{is_sort:is_sort}, function (data) {
                $("#data_detail").html(data);
                reload_data();
            }
        );
    }

    $('#exprot_list').click(function () {
        var url = '?app_act=sys/export_csv/export_show';
        //   var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        var params = {};
        var tab_id = $("#b1 .active").attr('id');
        if (tab_id === '' || tab_id === 'sku' || tab_id == undefined) {
            params = sku_tableStore.get('params');
            params.ctl_export_conf = 'purchase_analyse_view_list';
            <?php echo   create_export_token_js('pur/PurReportModel::get_report_detail_by_page');?>
        } else if (tab_id === 'record') {
            params = record_tableStore.get('params');
            params.ctl_export_conf = 'purchase_analyse_record_list';
            <?php echo   create_export_token_js('pur/PurReportModel::get_record_report_detail_by_page');?>
        }
     
        params.ctl_export_name = '采购统计分析';
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
    //汇总
    $(function () {
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            var obj = searchFormForm.serializeToObject();
            count_all(obj);
        });
        var filter = searchFormForm.serializeToObject();
        count_all(filter);
    });
    function count_all(obj) {
        $.post("?app_act=pur/purchase_analyse/detail_count", obj, function (data) {
            $("#detail_num_all").html(data.pur_num);
            $("#detail_money_all").html(data.pur_money);
            $("#return_num_all").html(data.return_num);
            $("#return_money_all").html(data.return_money);
        }, "json");
    }

</script>

