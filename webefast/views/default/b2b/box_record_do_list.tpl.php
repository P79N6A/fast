<style>
    #exprot_list {width:120px;}
</style>
<?php
$links[] = array('url' => 'b2b/box_task/do_list', 'title' => '装箱任务列表', 'is_pop' => false, 'pop_size' => '500,400');
render_control('PageHead', 'head1', array('title' => '装箱单列表',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<?php
$fenxiao = ds_get_select('custom', 2);
unset($fenxiao[0]);
$keyword_type = array();
$keyword_type['record_code'] = '箱号';
$keyword_type['task_code'] = '装箱任务号';
$keyword_type['relation_code'] = '关联单号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcord'] = '商品条形码';
$keyword_type = array_from_dict($keyword_type);
$status = array(
    '0' => '装箱单未打印',
    'all' => '全部',
    '1' => '装箱单已打印',
);
$status = array_from_dict($status);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出明细',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '装箱时间',
            'type' => 'group',
            'field' => 'create_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'create_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'create_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '打印状态',
            'type' => 'select',
            'id' => 'is_print',
            'data' => $status,
            'value' => $response['is_print']
        ),
        array(
            'label' => '拣货单号',
            'type' => 'input',
            'id' => 'pick_no',
            'title' => '',
        ),
        array(
            'label' => '唯品会仓库',
            'type' => 'select_multi',
            'id' => 'warehouse',
            'data' => load_model('api/WeipinhuijitWarehouseModel')->get_warehouse_select(),
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => $response['is_jit_execute'] != 1 ? TRUE : FALSE, 'id' => 'tabs_all'), // 默认选中active=true的页签
        array('title' => '唯品会JIT', 'active' => $response['is_jit_execute'] == 1 ? TRUE : FALSE, 'id' => 'tabs_jit'),
        array('title' => '普通', 'active' => FALSE, 'id' => 'tabs_general'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPage1Contents">
    <div>
        <ul class="toolbar frontool" id="ToolBar1">
            <li class="li_btns"><button class="button button-primary btn_print_packing">批量打印装箱单</button></li>
            <li class="front_close">&lt;</li>
        </ul>
        <script>
            $(function () {
                var custom_opts = $.parseJSON('[{"id":"print_packing","custom":"btn_init_print_packing"}]');
                for (var j in custom_opts) {
                    var g = custom_opts[j];
                    $("#ToolBar1 .btn_" + g['id']).click(eval(g['custom']));
                }
            });
        </script>
    </div>
    <div>
        <ul class="toolbar frontool" id="ToolBar2">
            <li class="li_btns"><button class="button button-primary btn_print_packing">批量打印装箱单</button></li>
            <li class="li_btns"><button class="button button-primary btn_print_xiangmai">批量打印唯品会箱唛</button></li>
            <li class="front_close">&lt;</li>
        </ul>
        <script>
            $(function () {
                var custom_opts = $.parseJSON('[{"id":"print_packing","custom":"btn_init_print_packing"},{"id":"print_xiangmai","custom":"btn_init_print_xiangmai"}]');
                for (var j in custom_opts) {
                    var g = custom_opts[j];
                    $("#ToolBar2 .btn_" + g['id']).click(eval(g['custom']));
                }
            });
        </script>
    </div>
    <div>
        <ul class="toolbar frontool" id="ToolBar3">
            <li class="li_btns"><button class="button button-primary btn_print_packing">批量打印装箱单</button></li>
            <li class="li_btns"><button class="button button-primary btn_print_general">批量普通箱唛打印</button></li>
            <li class="front_close">&lt;</li>
        </ul>
        <script>
            $(function () {
                var custom_opts = $.parseJSON('[{"id":"print_packing","custom":"btn_init_print_packing"},{"id":"print_general","custom":"btn_init_print_general"}]');
                for (var j in custom_opts) {
                    var g = custom_opts[j];
                    $("#ToolBar3 .btn_" + g['id']).click(eval(g['custom']));
                }
            });
        </script>
    </div>
</div>
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
    })
</script>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '打印', 'callback' => 'do_print'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete','show_cond' => 'obj.is_accept!=1', 'priv' => 'common/record_scan_box/cancel_box_record'),
                    array('id' => 'edit_detail', 'title' => '修改', 'callback' => 'editDetail', 'show_cond' => 'obj.is_accept!=1', 'priv' => 'b2b/box_record/edit'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '箱序号',
                'field' => 'box_order',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '箱号',
                'field' => 'record_code',
                'width' => '130',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({id})">{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '装箱任务号',
                'field' => 'task_code',
                'width' => '130',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href = javascript:view_task("{task_code}")>{task_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '装箱时间',
                'field' => 'create_time',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '关联单号',
                'field' => 'relation_code',
                'width' => '130',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品数量',
                'field' => 'num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '扫描人',
                'field' => 'scan_user',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '拣货单号',
                'field' => 'pick_no',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '送货仓',
                'field' => 'warehouse_name',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'b2b/BoxRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'CheckSelection' => true,
    'idField' => 'id',
    'export' => array('id' => 'exprot_list', 'conf' => 'b2b_box_record_do_list', 'name' => '装箱单列表', 'export_type' => 'file'),
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品名称', 'field' => 'goods_name'),
            array('title' => '商品编码', 'field' => 'goods_code'),
            array('title' => '规格1', 'field' => 'spec1_name'),
            array('title' => '规格2', 'field' => 'spec2_name'),
            array('title' => '商品条形码', 'width' => '150', 'field' => 'barcode'),
            array('title' => '数量', 'width' => '50', 'field' => 'num'),
        ),
        'page_size' => 10,
        'url' => get_app_url('b2b/box_record/detail_list'),
        'params' => 'record_code'
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    var task_code = "<?php echo $response['task_code']; ?>";
    var view_type = <?php echo $response['view_type'] ?>;
    var new_clodop_print = "<?php echo $response['new_clodop_print'];?>";
    if (view_type == 1) {
        $('#searchForm').css('display', 'none');
    }
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        $("input[name='is_normal']").change(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
            if (task_code) {
                e.params.task_code = task_code;
            }
            tableStore.set("params", e.params);
        });
        $('#searchForm #btn-search').trigger('click');
    });
    var opts = ['print_packing', 'print_xiangmai', 'print_general'];

    //批量打印装箱单
    function btn_init_print_packing() {
        get_checked(false, $(this), function (ids) {
            if(new_clodop_print == 1){
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=b2b_box&record_ids="+ids, {
                title: "装箱单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        }else{
            var url = "?app_act=tprint/tprint/do_print&print_templates_code=b2b_box&record_ids=" + ids;
            $("#print_iframe").attr('src', url);
        //window.open(u);
        }
        });
    }

    //批量打印箱唛
    function btn_init_print_xiangmai() {
        get_checked(false, $(this), function (ids) {
            //TODO:打印
            print_express(ids.toString());

        })
    }

    //批量打印普通箱唛
    function btn_init_print_general() {
        get_checked(false, $(this), function (ids) {
            //TODO:打印
            print_general_express(ids.toString());

        })
    }


    var p_time = 0;
    function print_express(w) {
        var id = "print_express" + p_time;
        var iframe = $('<iframe id="' + id + '" width="0" height="0"></iframe>').appendTo('body');
        if(new_clodop_print == 1){
            new ESUI.PopWindow("?app_act=wbm/store_out_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&iframe_id=" + id + "&record_ids=" + w + "&print_templates_code=weipinhuijit_box_print&type=box", {
                        title: "箱唛单打印",
                        width: 500,
                        height: 220,
                        onBeforeClosed: function () {
                        },
                        onClosed: function () {
                        }
                    }).show()
                }else{
                var url = "?app_act=b2b/box_record/print_express&print_templates_code=weipinhuijit_box_print&iframe_id=" + id + "&ids=" + w;
            iframe.attr('src', url);
            }
            p_time++;
        }

    //普通箱唛打印
    var pg_time = 0;
    function print_general_express(w) {
        var id = "print_general" + pg_time;
        var iframe = $('<iframe id="' + id + '" width="0" height="0"></iframe>').appendTo('body');
        if(new_clodop_print == 1){
            new ESUI.PopWindow("?app_act=wbm/store_out_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&iframe_id=" + id + "&record_ids=" + w + "&print_templates_code=general_box_print&type=box", {
                        title: "箱唛单打印",
                        width: 500,
                        height: 220,
                        onBeforeClosed: function () {
                        },
                        onClosed: function () {
                        }
                    }).show()
                }else{
            var url = "?app_act=b2b/box_record/print_express&iframe_id=" + id + "&ids=" + w + "&print_templates_code=general_box_print";
            iframe.attr('src', url);
            }
        pg_time++;
    }


    //读取已选中项
    function get_checked(isConfirm, obj, func) {
        var ids = []
        var selecteds = tableGrid.getSelection();
        for (var i in selecteds) {
            ids.push(selecteds[i].id)
        }

        if (ids.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return
        }

        if (isConfirm) {
            BUI.Message.Show({
                title: '批量打印',
                msg: '是否执行订单' + obj.text() + '?',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            func.apply(null, [ids])
                        }
                    },
                    {
                        text: '否',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        } else {
            func.apply(null, [ids])
        }
    }

    function  do_sure(_index, row) {

        url = '?app_act=b2b/box_task/do_sure';
        data = {id: row.id, type: 'enable'};
        _do_operate(url, data, 'table');
    }

    function  do_change(_index, row) {

        url = '?app_act=b2b/box_task/do_change';
        data = {id: row.id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
    //打印
    function  do_print(_index, row) {
        if(new_clodop_print == 1){
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=b2b_box&record_ids="+row.id, {
                title: "装箱单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        }else{
            var url = "?app_act=tprint/tprint/do_print&print_templates_code=b2b_box&record_ids=" + row.id;
            //$("#print_iframe").attr('src', url);
            var iframe = $('<iframe id="" width="0" height="0"></iframe>').appendTo('body');
            iframe.attr('src', url);
        //window.open(u);
        }
    }

    //批量
// 	function all_print(){

// 			var ids = []
// 	        var selecteds = tableGrid.getSelection();
// 			if (selecteds.length == 0) {
// 	            BUI.Message.Alert("请选择装箱单", 'error');
// 	            return;
// 	        }
// 	        for(var i in selecteds){
// 	            ids.push(selecteds[i].id)
// 	        }

// 	        var u = '?app_act=sys/flash_print/do_print'
// 	         u += '&template_id=20&model=b2b/BoxRecordModel&typ=default&record_ids='+ids
// 	        var window_is_block = window.open(u)
// 	        if (null == window_is_block) {
// 	            alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口")
// 	        }

// 	}
    /**
     * 查看调整单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        location.href = "?app_act=wbm/store_out_record/view&store_out_record_id=" + row.store_out_record_id;
    }


    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        view(row.id, 'view');

    }
    function editDetail(index, row) {
        view(row.id, 'edit');
    }
    function view(id, scene) {
        scene = scene ? scene : 'view';
        var url = '?app_act=b2b/box_record/' + scene + '&app_scene=' + scene + '&id=' + id;
        openPage(window.btoa(url), url, '装箱单详情');
    }

    function view_task(task_code) {
        var url = '?app_act=b2b/box_task/do_list&task_code=' + task_code;
        openPage(window.btoa(url), url, '装箱任务');
    }

    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('common/record_scan_box/cancel_box_record'); ?>',
            data: {record_code: row.record_code},
            success: function (ret) {
                var type = ret.status === 1 ? 'success' : 'error';
                if (type === 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

</script>

<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>