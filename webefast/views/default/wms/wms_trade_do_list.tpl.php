<?php echo load_js('comm_util.js') ?>
<style>
    /*#process_batch_task_tips div{height:300px;overflow-y:scroll;}*/

    .control-label #keyword_type {
        margin-top: 2px;
        width: 90px;
    }
    #time_start {
        width: 100px;
    }
    #time_end {
        width: 100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => $response['title'],
    'links' => array(),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = 'eFAST订单号';
if ($response['wmsId'] == 'oms') {
    $keyword_type['deal_code'] = 'eFAST交易号';
}
$keyword_type['new_record_code'] = '新订单号';
$keyword_type['wms_record_code'] = 'WMS单据号';
//$keyword_type['goods_barcode'] = '商品条形码';
//$keyword_type['goods_code'] = '商品编码';
//if ($response['wmsId'] == 'oms') {
//    $keyword_type['buyer_name'] = '买家昵称';
//}
$keyword_type = array_from_dict($keyword_type);

if ($response['wmsId'] == 'oms') {
    $order_type = load_model('base/WmsTradeModel')->get_order_type_oms();
} else {
    $order_type = load_model('base/WmsTradeModel')->get_order_type_b2b();
}

$time_type = array();
$time_type['upload_request_time'] = '上传时间';
$time_type['wms_order_time'] = '发货时间';
$time_type['process_time'] = '处理时间';
$time_type['cancel_request_time'] = '取消时间';
$time_type = array_from_dict($time_type);

$fields = array(
    array(
        'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
        'type' => 'input',
        'title' => '',
        'data' => $keyword_type,
        'id' => 'keyword',
    ),
    array(
        'label' => '单据类型',
        'type' => 'select',
        'id' => 'order_type',
        'data' => $order_type
    ),
    array(
        'label' => '仓库',
        'type' => 'select_multi',
        'id' => 'efast_store_code',
        'data' => load_model('wms/WmsTradeModel')->get_wms_store(),
    ),
    array(
        'label' => array('id' => 'time_type', 'type' => 'select', 'data' => $time_type),
        'type' => 'group',
        'field' => 'daterange1',
        'data' => $time_type,
        'child' => array(
            array('title' => 'start', 'type' => 'time', 'field' => 'time_start',),
            array('pre_title' => '~', 'type' => 'time', 'field' => 'time_end', 'remark' => ''),
        )
    ),
);
if ($response['wmsId'] == 'oms') {
    $fields[] = array(
        'label' => '店铺',
        'type' => 'select_multi',
        'id' => 'shop_code',
        'data' => load_model('base/ShopModel')->get_purview_shop(),
    );
    $fields[] = array(
        'label' => '销售平台',
        'type' => 'select',
        'id' => 'sale_channel_code',
        //'data' => load_model('base/SaleChannelModel')->get_all_select()
        'data' => load_model('base/SaleChannelModel')->get_my_select(),
    );
}

$fields[] = array(
    'label' => '取消状态',
    'type' => 'select',
    'id' => 'cancel_request_flag',
    'data' => load_model('wms/WmsTradeModel')->getWmsFlag('cancel_request_flag'),
);

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
        )
    ),
    'show_row' => 3,
    'fields' => $fields,
));
?>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => false, 'id' => 'tabs_all'),
        array('title' => '待上传', 'active' => true, 'id' => 'tabs_wait_upload'),
        array('title' => '待发货/待收货', 'active' => false, 'id' => 'tabs_wait_order'),
        array('title' => '待处理', 'active' => false, 'id' => 'tabs_wait_process'),
        array('title' => '已发货/已收货', 'active' => false, 'id' => 'tabs_ordered'),
        array('title' => '已取消', 'active' => false, 'id' => 'tabs_cancel'),
        array('title' => '操作失败', 'active' => false, 'id' => 'tabs_fail'),
    ),
    'for' => 'TabPage1Contents'
));
?>
<div id="TabPage1Contents">
    <div></div>
    <?php if ($response['wmsId'] == 'oms') {?>        
        <div>
            <?php if (load_model('sys/PrivilegeModel')->check_priv('wms/wms_mgr/opt_upload_oms')) { ?> 
            <ul class="toolbar frontool" id="ToolBar2">
                <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=wms/wms_mgr/opt_upload',obj_name:'批量上传',ids_params_name:'id'}">批量上传</button></li>
                <li class="front_close">&lt;</li>
            </ul>
            <?php } ?>   
        </div>        
        <div></div>
        <div>
            <?php  if (load_model('sys/PrivilegeModel')->check_priv('wms/wms_mgr/opt_order_shipping_oms')) { ?>
            <ul class="toolbar frontool" id="ToolBar2">
                <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=wms/wms_mgr/opt_order_shipping',obj_name:'批量处理',ids_params_name:'id'}">批量处理</button></li>
                <li class="front_close">&lt;</li>
            </ul>
            <?php } ?>
        </div>
        <div></div>
        <div></div>    
        <div>
            <?php if (load_model('sys/PrivilegeModel')->check_priv('wms/wms_mgr/opt_upload_oms')) { ?>  
            <ul class="toolbar frontool" id="ToolBar2">
                <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=wms/wms_mgr/opt_upload',obj_name:'批量上传',ids_params_name:'id'}" >批量上传</button></li>
                <li class="front_close">&lt;</li>
            </ul>
            <?php } ?>
        </div>   
    <?php }else{ ?>        
        <div>
            <?php if (load_model('sys/PrivilegeModel')->check_priv('wms/wms_mgr/opt_upload_b2b')) { ?> 
            <ul class="toolbar frontool" id="ToolBar2">
                <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=wms/wms_mgr/opt_upload',obj_name:'批量上传',ids_params_name:'id'}">批量上传</button></li>
                <li class="front_close">&lt;</li>
            </ul>
            <?php } ?>
        </div>           
        <div></div>       
        <div>
            <?php if (load_model('sys/PrivilegeModel')->check_priv('wms/wms_mgr/opt_order_shipping_b2b')) { ?>
            <ul class="toolbar frontool" id="ToolBar2">
                <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=wms/wms_mgr/opt_order_shipping',obj_name:'批量处理',ids_params_name:'id'}">批量处理</button></li>
                <li class="front_close">&lt;</li>
            </ul>
            <?php } ?>
        </div>        
        <div></div>
        <div></div>        
        <div>
            <?php if (load_model('sys/PrivilegeModel')->check_priv('wms/wms_mgr/opt_upload_b2b')) { ?>
            <ul class="toolbar frontool" id="ToolBar2">
                <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=wms/wms_mgr/opt_upload',obj_name:'批量上传',ids_params_name:'id'}">批量上传</button></li>
                <li class="front_close">&lt;</li>
            </ul>
            <?php } ?>
        </div>        
    <?php } ?>
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
    });
</script>

<?php
$force_cancel = load_model('sys/PrivilegeModel')->check_priv('wms/wms_mgr/force_cancel');
$buttons = array(
    array(
        'id' => 'view',
        'title' => '详情',
        'act' => "pop:wms/wms_trade/view&task_id={id}&type={$response['wmsId']}",
        'show_name' => '详情',
        'show_cond' => '',
        'priv' => 'wms/wms_trade/view',
        'pop_size' => '920,600'
    ),
    array(
        'id' => 'upload',
        'title' => '上传',
        'callback' => 'upload',
        'confirm' => '确认要上传吗？',
//        'priv' => 'wms/wms_mgr/upload',
        'show_cond' => '(obj.upload_request_flag == 0  && obj.upload_response_flag == 0  && obj.wms_order_flow_end_flag == 0)||(obj.upload_request_flag == 0  && (obj.upload_response_flag == 0 || obj.upload_response_flag == 20)   && obj.wms_order_flow_end_flag == 0)'
    ),
//    array('id' => 'cancel',
//        'title' => '取消',
//        'callback' => 'cancel',
//        'confirm' => '确认要取消吗？',
//        'priv' => 'wms/wms_mgr/cancel',
//        'show_cond' => '(obj.cancel_request_flag == 0 || obj.cancel_response_flag == 20) && obj.upload_response_flag == 10 && obj.wms_order_flow_end_flag == 0'
//    ),
    array(
        'id' => 'order_shipping',
        'title' => '处理',
        'callback' => 'order_shipping',
        'confirm' => '确认要处理吗？',
        'priv' => 'wms/wms_mgr/order_shipping',
        'show_cond' => 'obj.wms_order_flow_end_flag == 1 && obj.process_flag < 30'
    ),
);
if (TRUE === $force_cancel) {
    $buttons[] = array(
        'id' => 'force_cancel',
        'title' => '强制取消',
        'callback' => 'force_cancel',
        'priv' => 'wms/wms_mgr/force_cancel',
        'show_cond' => '(obj.cancel_request_flag == 0 || obj.cancel_response_flag == 20) && obj.upload_response_flag == 10 && obj.wms_order_flow_end_flag == 0'
    );
}

$list = array(
    array(
        'type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '80',
        'align' => '',
        'buttons' => $buttons,
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '单据类型',
        'field' => 'record_order_type',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '状态',
        'field' => 'status',
        'width' => '80',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '订单号',
        'field' => 'record_code',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '新订单号',
        'field' => 'new_record_code',
        'width' => '120',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '仓库',
        'field' => 'store_name',
        'width' => '80',
        'align' => ''
    ),
);

if ($response['wmsId'] == 'oms') {
    $list[] = array(
        'type' => 'text',
        'show' => 1,
        'title' => '销售平台',
        'field' => 'sale_channel_name',
        'width' => '80',
        'align' => ''
    );
    $list[] = array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺',
        'field' => 'shop_name',
        'width' => '100',
        'align' => ''
    );
    $list[] = array(
        'type' => 'text',
        'show' => 1,
        'title' => '交易号',
        'field' => 'deal_code',
        'width' => '120',
        'align' => ''
    );
    $list[] = array(
        'type' => 'text',
        'show' => 1,
        'title' => '买家昵称',
        'field' => 'buyer_name',
        'width' => '100',
        'align' => ''
    );
}

$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => 'WMS单据号',
    'field' => 'wms_record_code',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '上传时间',
    'field' => 'upload_request_time',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '取消时间',
    'field' => 'cancel_request_time',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '收发货时间',
    'field' => 'wms_order_time',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '处理时间',
    'field' => 'process_time',
    'width' => '100',
    'align' => ''
);
$list[] = array(
    'type' => 'text',
    'show' => 1,
    'title' => '日志',
    'field' => 'log_err_msg',
    'width' => '100',
    'align' => ''
);
$name = $response['wmsId'] == 'oms' ? '外包仓零售单' : '外包仓进销存单';
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'wms/WmsTradeModel::do_list_by_page',
    'export' => array('id' => 'exprot_list', 'conf' => 'wms_trade_'.$response['wmsId'], 'name' => $name, 'export_type' => 'file'),
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'params' => array('filter' => array('wmsId' => $response['wmsId'])),
    'customFieldTable' => $response['wmsId'] == 'oms' ? 'wms/wms_oms_trade' : 'wms/wms_b2b_trade',
    'CheckSelection' => true,
    'init' => 'nodata',
));
?>
<div id="msg_id"></div>
<script type="text/javascript">
    //读取已选中项
    var wmsId = '<?php echo $response['wmsId']; ?>';
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
            tableStore.load({}, function(){
                $(".nodata").text('');//清除加载提示
            });
        });
    });

    tableStore.on('beforeload', function (e) {
        e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
        e.params.wmsId = wmsId;
        tableStore.set("params", e.params);
    });
    
    function show_process_batch_task_plan(title, content) {
        BUI.use('bui/overlay', function (Overlay) {
            var dialog = new Overlay.Dialog({
                title: title,
                width: 500,
                height: 400,
                mask: true,
                buttons: [],
                bodyContent: content
            });
            dialog.show();
        });
    }

    function upload(index, row) {
        var d = {"task_id": row.id, "type": wmsId, "app_fmt": 'json'};
        $.post('<?php echo get_app_url('wms/wms_mgr/upload'); ?>', d, function (data) {
            var type = data.status === 1 ? 'success' : 'error';
            var msg = data.status === 1 ? '上传成功' : data.message;
            BUI.Message.Alert(msg, type);
            tableStore.load();
        }, "json");
    }

    function cancel(index, row) {
        if (row.record_type == 'sell_record') {
            var d = {"sell_record_code": row.record_code, "type": 'opt_intercept'};
            $.post('<?php echo get_app_url('oms/sell_record/opt'); ?>', d, function (data) {
                var type = data.status === 1 ? 'success' : 'error';
                var msg = data.status === 1 ? '取消成功' : data.message;
                BUI.Message.Alert(msg, type);
                tableStore.load();
            }, "json");
        } else {
            var d = {"task_id": row.id, "type": wmsId, "app_fmt": 'json'};
            $.post('<?php echo get_app_url('wms/wms_mgr/cancel'); ?>', d, function (data) {
                var type = data.status === 1 ? 'success' : 'error';
                var msg = data.status === 1 ? '取消成功' : data.message;
                BUI.Message.Alert(msg, type);
                tableStore.load();
            }, "json");
        }
    }
    function force_cancel(index, row) {
        BUI.Message.Confirm('强制取消仅拦截系统单据状态，不能保证取消WMS单据状态，<br/><b>请线下联系WMS取消单据</b>，否则会导致WMS重复操作！', function () {
            if (row.record_type == 'sell_record') {
                var d = {"sell_record_code": row.record_code, "app_fmt": 'json'};
                $.post('<?php echo get_app_url('oms/sell_record/wms_force_cancel'); ?>', d, function (data) {
                    var type = data.status === 1 ? 'success' : 'error';
                    var msg = data.status === 1 ? '取消成功' : data.message;
                    BUI.Message.Alert(msg, type);
                    tableStore.load();
                }, "json");
            } else if (row.record_type == 'sell_return') {
                var d = {"sell_return_code": row.record_code, "app_fmt": 'json'};
                $.post('<?php echo get_app_url('oms/sell_return/wms_force_cancel'); ?>', d, function (data) {
                    var type = data.status === 1 ? 'success' : 'error';
                    var msg = data.status === 1 ? '取消成功' : data.message;
                    BUI.Message.Alert(msg, type);
                    tableStore.load();
                }, "json");
            } else {
                var d = {"task_id": row.id, "type": wmsId, "app_fmt": 'json'};
                $.post('<?php echo get_app_url('wms/wms_mgr/force_cancel'); ?>', d, function (data) {
                    var type = data.status === 1 ? 'success' : 'error';
                    var msg = data.status === 1 ? '取消成功' : data.message;
                    BUI.Message.Alert(msg, type);
                    tableStore.load();
                }, "json");
            }
        }, 'warning');
    }
    function order_shipping(index, row) {
        var d = {"task_id": row.id, "type": wmsId, "app_fmt": 'json'};
        $.post('<?php echo get_app_url('wms/wms_mgr/order_shipping'); ?>', d, function (data) {
            var type = data.status === 1 ? 'success' : 'error';
            var msg = data.status === 1 ? '处理成功' : data.message;
            BUI.Message.Alert(msg, type);
            tableStore.load();
        }, "json");
    }

    function view(sell_record_code) {
        var url = '?app_act=oms/sell_record/view&sell_record_code=' + sell_record_code
        openPage(window.btoa(url), url, '订单详情');
    }
    $("._sys_batch_task_force_btn").click(function () {
        var task_info = eval('(' + $(this).attr('task_info') + ')');
        var task_name = $(this).text();
        task_info['act'] +='&type='+ wmsId;
        process_batch_task(task_info['act'], task_name, task_info['obj_name'], task_info['ids_params_name'], task_info['submit_all_ids_flag'], undefined, undefined, undefined, 'wms_trade');
    });
</script>
<?php include_once (get_tpl_path('process_batch_task'));