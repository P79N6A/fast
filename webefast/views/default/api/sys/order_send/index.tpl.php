<style>
    .control-label{ font-size:14px;}
    #send_time_start,#send_time_end{ width:87px;}
    #searchForm input,#searchForm select{ font-size:12px;}
    #table{margin-top:5px;}
</style>
<?php echo load_js("baison.js", true); ?>
<script>
    function get_checkbox_id() {
        var str = "";
        var check_id_arr = tableGrid.getSelection();
        for (var i = 0; i < check_id_arr.length; i++) {
            str += check_id_arr[i].api_order_send_id + ",";
        }
        str = str.substring(0, str.length - 1);
        return str;
    }
    //正常回写
    function send(index, row) {
        if (typeof row == "undefined") {
            var id = get_checkbox_id();
        } else {
            var id = row.api_order_send_id;
        }
        if (id == '') {
            BUI.Message.Show({
                msg: '请选择回写单据',
                icon: 'error',
                buttons: [{
                        text: '确认',
                        elCls: 'button button-primary',
                        handler: function () {
                            this.close();
                        }
                    }, ],
                autoHide: true,
                autoHideDelay: 2000
            });
        } else {
            /*
             var url = "?app_act=sys/task/send_order&id="+id;
             if (row.source=='taobao') {
             url = "?app_act=api/sys/order_send/send_order&id="+id;
             }*/
            var url = "?app_act=api/sys/order_send/send_order&id=" + id;
            ajax_post({
                url: url,
                async: false,
                alert: false,
                data: {"type": "0", "force_send": "0"},
                callback: function (data) {
                    var type = data.status == "1" ? 'success' : 'error';
                    BUI.Message.Alert(data.message, type);
                    if (data.fail_num > 0) {
                        $("#order_send_fail_num").html(data.fail_num);
                    }
                    tableStore.load();
                }
            });
        }
    }
    //强制回写
    function enforce_send(index, row) {
        if (typeof row == "undefined") {
            var id = get_checkbox_id();
        } else {
            var id = row.api_order_send_id;
        }
        ajax_post({
            url: "?app_act=sys/task/send_order&id=" + id,
            async: false,
            alert: false,
            data: {"type": "1"},
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                tableStore.load();
            }
        });
    }
    //本地回写
    function send_local(index, row) {
        var action = '';
        if (typeof row == "undefined") {
            var id = get_checkbox_id();
            action = 'batch_send';
        } else {
            var id = row.api_order_send_id;
            action = 'send';
        }
        ajax_post({
            url: "?app_act=sys/task/send_order&id=" + id + "&send_local=send_local",
            async: false,
            alert: false,
            data: {"type": "2", "action": action},
            callback: function (data) {
                var type = data.status == "1" ? 'true' : 'false';

                BUI.Message.Show({
                    msg: data.message,
//             	icon : 'question',
                    buttons: [],
                    autoHide: type,
                    autoHideDelay: 2000
                });
                if (data.fail_num > 0) {
                    $("#order_send_fail_num").html(data.fail_num);
                }
                tableStore.load();
            }
        });
    }
</script>
<?php
render_control('PageHead', 'head1', array('title' => '平台网单回写列表',
    'links' => array(
    //array('type' => 'js', 'title' => '一键网单回写', 'js' => "down()"),
    ),
));
?>

<?php
$keyword_type = array();
$keyword_type['tid'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['express_no'] = '快递单号';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '支持模糊查询：交易号、订单号、快递单号',
        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('express'),
        ),
        array(
            'label' => '回写状态',
            'type' => 'select_multi',
            'id' => 'status',
            'value' => '0,-1',
            'data' => ds_get_select_by_field('order_send_status', 1),
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'source',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select(),
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '发货时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'send_time_start', 'value' => date('Y-m-d', strtotime('-6 day'))),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'send_time_end', 'remark' => '', 'value' => date('Y-m-d')),
            )
        ),
    )
));
?>
<?php if ($response['num'] > 0) { ?>
    <span>
        <a name="order_send_fail" class="order_send_fail" style="cursor:pointer;text-decoration:red underline;">
            <font color="red">回写失败订单(<span id="order_send_fail_num"><?php echo $response['num']; ?></span>)</font>
        </a>
        &nbsp;&nbsp;回写失败次数超过3次，系统将不再自动回写，需要人工回写。
    </span>
<?php } ?>
<ul class="toolbar frontool">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('api/sys/order_send/callback1')) { ?>
        <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=api/sys/order_send/batch_send_order&force_send=0',obj_name:'批量回写单据',ids_params_name:'api_order_send_id'}">批量回写</button></li>
    <?php } ?>
    <?php if ($response['is_check_refund'] == 1) { ?>
        <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=api/sys/order_send/batch_send_order&force_send=1',obj_name:'批量强制回写单据',ids_params_name:'api_order_send_id'}">批量强制回写</button></li>
    <?php } ?>
    <li class="li_btns"><button class="button button-primary _sys_batch_task_force_btn" task_info="{act:'app_act=api/sys/order_send/send_local&send_type=batch',obj_name:'批量本地回写单据',ids_params_name:'api_order_send_id'}">批量本地回写</button></li>
    <div class="front_close">&lt;</div>
</ul>
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
        $("#send_time_start").val("<?php echo date('Y-m-d', strtotime('-6 day')); ?>");
        $("#send_time_end").val("<?php echo date('Y-m-d') ?>");
        $("#status").val('0,-1');
        tableCellEditing.on('accept', function (record) {
            var params = {
                "sell_record_code": record.record.sell_record_code,
                "express_code": record.record.express_code,
                "express_no": record.record.express_no,
                "is_force": 1
            }
            $.post("?app_act=oms/deliver_record/edit_express", params, function (data) {
                if (data.status < 0) {
                    BUI.Message.Alert(data.message, 'error');
                } else if(data.status == 1){
                    BUI.Message.Tip(data.message, 'success');
                }
            }, "json")
        });
    })
</script>

<?php
$expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array('status' => 1), 2);
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array(
                    array('id' => 'send', 'title' => '回写', 'callback' => 'send', 'show_cond' => 'obj.status == 0 || obj.status == -2 ||obj.status == -1'),
                    array('id' => 'send_local', 'title' => '本地回写', 'callback' => 'send_local', 'show_cond' => 'obj.status == 0 || obj.status == -2 ||obj.status == -1'),
                    array('id' => 'send_again', 'title' => '再次回写', 'callback' => 'send', 'show_cond' => 'obj.status == -2 || obj.status == 0'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'source',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code',
                'width' => '120',
                'align' => '',
                'phpfun' => 'get_shop_name_by_code'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '日志',
                'field' => 'error_remark',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台交易单号',
                'field' => 'tid',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统订单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_code',
                'width' => '100',
                'align' => '',
                'format_js' => array('type' => 'map', 'value' => $expressList),
                'editor' => "{xtype : 'select', items: " . json_encode($expressList) . "}"
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express_no',
                'width' => '100',
                'align' => '',
                'editor' => "{xtype : 'text'}",
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货时间',
                'field' => 'send_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '网单回写时间',
                'field' => 'upload_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '回写状态',
                'field' => 'status',
                'width' => '100',
                'align' => '',
                'phpfun' => array("get_name_by_code", "order_send_status")
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '回写失败次数',
                'field' => 'fail_num',
                'width' => '100',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'api/sys/OrderSendModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'api_order_send_id',
    'CheckSelection' => true,
    'CellEditing' => true,
    'init' => 'nodata',
));
?>
<script type="text/javascript">
    function view(sell_record_code) {
        var url = '?app_act=oms/sell_record/view&sell_record_code=' + sell_record_code;
        openPage(window.btoa(url), url, '订单详情');
    }
    $(function () {
        $("#tid").css('border', '1px solid red');
        $(".order_send_fail").click(function () {
            $("#status").val(-1);
            $("#status_select_multi").find(".bui-select-input").click();
            status_select.get('picker').hide();
            $("#source").append("<option value='' selected='selected'> </option>")
            $("#shop_code").append("<option value='' selected='selected'> </option>");
            $("#sell_record_code").val('');
            $("#express_no").val('');
            $("#send_time_start").val('');
            $("#send_time_end").val('');
            $("#tid").val('');
            $("#btn-search").click();
        });

        $("._sys_batch_task_force_btn").click(function () {
            var task_info = eval('(' + $(this).attr('task_info') + ')');
            var task_name = $(this).text();
            process_batch_task(task_info['act'], task_name, task_info['obj_name'], task_info['ids_params_name'], task_info['submit_all_ids_flag'], undefined, undefined, undefined, 'order_send');
        });
    });

</script>
<?php include_once (get_tpl_path('process_batch_task')); ?>