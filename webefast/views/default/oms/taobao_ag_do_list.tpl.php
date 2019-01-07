<?php echo load_js("baison.js", true); ?>
<?php echo load_js("pur.js", true); ?>
    <style>
        #order_first_start, #order_first_end {
            width: 100px;
        }
    </style>
<?php
$links = array();
render_control('PageHead', 'head1', array('title' => '退单处理AG对接',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['tid'] = '交易号';
$keyword_type['refund_id'] = '退单编号';
$keyword_type['buyer_nick'] = '买家昵称';
$keyword_type = array_from_dict($keyword_type);
$is_buyer_remark = array();
$order_first_start = date("Y-m-d", strtotime('-3 day')) . ' 00:00:00';
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
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
//        array(
//            'label' => '销售平台',
//            'type' => 'select_multi',
//            'id' => 'source',
//            'data' => load_model('base/SaleChannelModel')->get_select()
//        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['purview_shop'],
        ),
        array(
            'label' => '申请时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'order_first_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'order_first_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '单据状态',
            'type' => 'select',
            'id' => 'ag_status',
            'data' => ds_get_select_by_field('ag_status'),
        ),
    )
));
?>
<?php
if ($response['sys_params']['aligenius_refunds_check'] == 1) {//开启审核参数
    $tabs = array(
        array('title' => '全部', 'active' => true, 'id' => 'all'),
        array('title' => '待处理', 'active' => false, 'id' => 'wait_process'),
        array('title' => '待同步取消/入库状态', 'active' => false, 'id' => 'wait_sync'),
        array('title' => '待同步退款审核状态', 'active' => false, 'id' => 'wait_check'),
        array('title' => '完成', 'active' => false, 'id' => 'completed'),
    );
} else {
    $tabs = array(
        array('title' => '全部', 'active' => true, 'id' => 'all'),
        array('title' => '待处理', 'active' => false, 'id' => 'wait_process'),
        array('title' => '待同步取消/入库状态', 'active' => false, 'id' => 'wait_sync'),
        array('title' => '完成', 'active' => false, 'id' => 'completed'),
    );
}

render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'deal', 'title' => '处理', 'callback' => 'do_deal', 'show_name' => '处理', 'show_cond' => 'obj.ag_status==1'),
                    array('id' => 'complete', 'title' => '强制完成', 'callback' => 'enforce_complete', 'show_name' => '强制完成', 'confirm' => '确认要强制完成吗？', 'show_cond' => 'obj.ag_status<5'),
                    array('id' => 'handle', 'title' => '设为已处理', 'callback' => 'set_process', 'show_cond' => 'obj.ag_status==2', 'priv' => ''),
                    array('id' => 'sync', 'title' => '同步', 'callback' => 'do_sync', 'show_cond' => 'obj.ag_status==3', 'priv' => '', 'confirm' => '确定同步？'),
                    array('id' => 'check', 'title' => '审核', 'callback' => 'do_check', 'show_cond' => 'obj.ag_status==4', 'priv' => '', 'confirm' => '确定审核？'),
                    array('id' => 'log', 'title' => '处理日志', 'callback' => 'push_log_view', 'show_cond' => '', 'priv' => '',),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据状态',
                'field' => 'ag_status_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '申请时间',
                'field' => 'order_first_insert_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单编号',
                'field' => 'refund_id',
                'width' => '120',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'tid',
                'width' => '120',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_nick',
                'width' => '100',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退款金额',
                'field' => 'refund_fee',
                'width' => '100',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统退单号',
                'field' => 'refund_record_code',
                'width' => '120',
                'align' => 'center',
//                'format_js' => array(
//                    'type' => 'html',
//                    'value' => '<a href="javascript:view({refund_record_code})">{refund_record_code}</a>',
//                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '关联订单',
                'field' => 'sell_record_code',
                'width' => '100',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '处理状态',
                'field' => 'process_status',
                'width' => '100',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '推送值',
                'field' => 'push_val_name',
                'width' => '100',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '推送状态',
                'field' => 'push_status',
                'width' => '100',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '最新推送日志',
                'field' => 'push_log',
                'width' => '100',
                'align' => 'center',
            ),
        )
    ),
    'dataset' => 'oms/TaobaoAgModel::get_by_page',
    'export' => array('id' => 'exprot_list', 'conf' => 'ag_refund_list', 'name' => '退单处理ag','export_type' => 'file'),//
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'init' => 'nodata',
    'CheckSelection' => true,
));
?>

    <div id="TabPage1Contents">
        <div>
            <ul class="toolbar frontool" id="ToolBar1">
                <li class="li_btns"><button class="button button-primary btn_sync" onclick="do_sync_multi()">批量同步</button></li>
                <?php if($response['sys_params']['aligenius_refunds_check'] == 1){?>
                <li class="li_btns"><button class="button button-primary btn_check" onclick="do_check_multi()">批量审核</button></li>
                <?php }?>
                <div class="front_close">&lt;</div>
            </ul>
        </div>
        <div>
            <ul class="toolbar frontool" id="ToolBar2">

            </ul>
        </div>
        <div>
            <ul class="toolbar frontool" id="ToolBar3">

            </ul>
        </div>
        <div>
            <ul class="toolbar frontool" id="ToolBar4">
                <li class="li_btns">
                    <button class="button button-primary btn_sync" onclick="do_sync_multi()">批量同步</button>
                </li>
            </ul>
        </div>
        <?php if($response['sys_params']['aligenius_refunds_check'] == 1){?>
        <div>
            <ul class="toolbar frontool" id="ToolBar5">
                <li class="li_btns">
                    <button class="button button-primary btn_check" onclick="do_check_multi()">批量审核</button>
                </li>
            </ul>
        </div>
        <?php }?>
    </div>

    <script>
        $(function () {
            $("#order_first_start").val("<?php echo $order_first_start ?>");
            //Tab页签数据加载
            $("#TabPage1 a").click(function () {
                //tableStore.load();
                $("#btn-search").click();
            });
        });

        tableStore.on('beforeload', function (e) {
            e.params.ag_status_tab = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);
        });

        $(function () {
            function tools() {
                $(".frontool").css({left: '0px'});
                $(".front_close").click(function () {
                    if ($(this).html() == "&lt;") {
                        $(".frontool").animate({left: '-100%'}, 1000);
                        $(this).html(">");
                        $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                    } else {
                        $(".frontool").animate({left: '0px'}, 1);
                        $(this).html("<");
                        $(this).removeClass("close_02").animate({right: '0'}, 1000);
                    }
                });
            }

            tools();
        });


        //处理
        function do_deal(_index, row) {
            var refund_id = row.refund_id;
            var url = '?app_act=api/sys/order_refund/do_list&refund_id=' + refund_id;
            openPage(window.btoa(url), url, '平台退单列表');
        }

        //设为已处理
        function set_process(index, row) {
            var url = "?app_act=oms/taobao_ag/set_process&refund_id=" + row.refund_id;
            _do_execute(url, 'table', '设为已处理', 500, 325);
        }

        //强制完成
        function enforce_complete(index, row) {
            var params = {
                'refund_id': row.refund_id,
            };
            $.post("?app_act=oms/taobao_ag/enforce_complete", params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                tableStore.load();
            }, "json");
        }

        //同步
        function do_sync(index, row) {
            var params = {
                'refund_id': row.refund_id,
            };
            $.post("?app_act=oms/taobao_ag/do_sync", params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                tableStore.load();
            }, "json");
        }


        //审核
        function do_check(index, row) {
            var params = {
                'refund_id': row.refund_id,
            };
            $.post("?app_act=oms/taobao_ag/do_check", params, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                tableStore.load();
            }, "json");
        }


        //推送日志
        function push_log_view(index, row) {
            var url = "?app_act=oms/taobao_ag/push_log&refund_id=" + row.refund_id;
            _do_execute(url, 'table', '推送日志', 920, 500);
        }

        //批量同步
        function do_sync_multi() {
            var task_info = {};
            task_info['act'] = 'app_act=oms/taobao_ag/do_sync';
            task_info['obj_name'] = '批量同步的退单';
            task_info['ids_params_name'] = 'refund_id';
            var task_name = '批量同步';
            process_batch_task(task_info['act'], task_name, task_info['obj_name'], task_info['ids_params_name'], 0, undefined, undefined, undefined, 'ag_sync');
        }

        //批量审核
        function do_check_multi() {
            var task_info = {};
            task_info['act'] = 'app_act=oms/taobao_ag/do_check';
            task_info['obj_name'] = '批量审核的退单';
            task_info['ids_params_name'] = 'refund_id';
            var task_name = '批量审核';
            process_batch_task(task_info['act'], task_name, task_info['obj_name'], task_info['ids_params_name'], 0, undefined, undefined, undefined, 'ag_check');
        }

    </script>
<?php include_once(get_tpl_path('process_batch_task'));