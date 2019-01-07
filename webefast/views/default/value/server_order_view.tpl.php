<?php echo load_js("baison.js,record_table.js", true); ?>
<style>
    p {
        margin: 0;
    }

    .panel-body {
        padding: 0;
    }

    .table {
        margin-bottom: 0;
    }

    .bui-grid, .bui-grid-header, .bui-grid-body, .bui-grid-table, .bui-grid-row {
        width: 100% !important;
    }

    .table tr {
        padding: 5px 0;
    }

    .table th, .table td {
        border: 1px solid #dddddd;
        padding: 3px 0;
        vertical-align: middle;
    }

    .table th {
        width: 11.3%;
        text-align: center;
    }

    .table td {
        width: 23%;
        padding: 0 1%;
    }

    .row {
        margin-left: 0;
        padding: 2px 8px;
        border: 1px solid #ddd;
    }
</style>
<?php
render_control(
        'PageHead', 'head1', array(
    'title' => '编辑增值服务订购',
    'links' => array(
    // array('url' => 'market/valueorder/do_list_new', 'title' => '增值服务订购')
    )
        )
);
?>
<div class="panel record_table" id="panel_html">
</div>
<div class="record_table" style="display:none;">
</div>
<script>
    var to_edit = <?php echo $response['data']['pay_status'] == '0' ? 'true' : 'false'; ?>;
    var data = [
        {
            name: "order_code",
            title: "增值订购编号",
            value: '<?php echo $response['data']['order_code']; ?>',
            type: "input"
        },
        {
            name: "val_orderdate",
            title: "下单时间",
            value: '<?php echo $response['data']['val_orderdate']; ?>',
            type: "input"
        },
        {
            name: "val_orderdate",
            title: "支付时间",
            value: '<?php echo $response['data']['pay_date']; ?>',
            type: "input"
        },
        {
            name: "val_orderdate",
            title: "订单状态",
            value: '<?php echo $response['data']['pay_status_name']; ?>',
            type: "input"
        },
        {
            name: "server_num",
            title: "订购服务数量",
            value: '<?php echo $response['data']['server_num']; ?>',
//            type: "select",
            //   edit: to_edit
        },
        {
            name: "order_money",
            title: "订单金额",
            value: '<?php echo $response['data']['order_money']; ?>',
            type: "input"
        },
        {
            name: "discount",
            title: "优惠金额",
            value: '<?php echo $response['data']['discount']; ?>',
            type: "input",
        },
        {
            name: "server_money",
            title: "应付金额",
            value: '<?php echo $response['data']['server_money']; ?>',
        },
        {
            name: "val_desc",
            title: "描述",
            value: '<?php echo $response['data']['val_desc']; ?>',
            type: "input",
            edit: to_edit
        },
    ];
    $(function () {
        var r = new record_table();
        r.init({
            id: "panel_html",
            data: data,
            is_edit: to_edit,
            edit_url: "?app_act=value/server_order/edit_order_action",
            'load_url': "?app_act=value/server_order/get_order_info&app_fmt=json&id=" + id,
            'load_callback': function () {
                logStore.load();
            }
        });
    });
</script>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">
            订购明细
            <i class="icon-folder-open toggle"></i>
        </h3>
        <div class="pull-right">
            <?php if ($response['data']['pay_status'] == 0) { ?>
                <button class="button button-small" onclick="show_val_server();"><i class="icon-plus"></i>新增服务</button>
            <?php } ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        render_control('DataTable', 'table_list', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'button',
                        'show' => 1,
                        'title' => '操作',
                        'field' => '_operate',
                        'width' => '100',
                        'align' => '',
                        'buttons' => array(
                            array(
                                'id' => 'del',
                                'title' => '删除',
                                'callback' => 'do_delete_detail',
                                'show_cond' => 'obj.val_pay_status!=1',
                                'confirm' => '确定删除该服务吗？',
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '序号',
                        'field' => 'order_sort',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '服务名称',
                        'field' => 'val_serverid_name',
                        'width' => '180',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '订购周期（月）',
                        'field' => 'val_hire_limit',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '销售价格',
                        'field' => 'val_actual_price',
                        'width' => '100',
                        'align' => ''
                    ),
                   array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '到期时间',
                        'field' => 'val_enddate',
                        'width' => '180',
                        'align' => ''
                    ),
                )
            ),
            'dataset' => 'value/ValueServerModel::get_order_detail_info',
            //'queryBy' => 'searchForm',
            'idField' => 'val_num',
            'params' => array('filter' => array('pid' => $response['data']['id'])),
                //'RowNumber'=>true,
                //'CheckSelection' => true,
                // 'CellEditing'=>(1==$response['data']['is_check_and_accept'])?false:true,
        ));
        ?>
    </div>
</div>
<div class="panel">
    <div class="panel-header">
        <h3>日志操作 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row" style="width: 100%; padding: 0; margin: 0; border: none; padding-bottom: 5px;">
            <?php
            render_control('DataTable', 'log', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作人',
                            'field' => 'val_operator',
                            "width" => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作名称',
                            'field' => 'val_action',
                            "width" => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作时间',
                            'field' => 'val_time',
                            "width" => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '单据状态',
                            'field' => 'val_status',
                            "width" => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'val_remark',
                            "width" => '250',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'value/ValueServerModel::getLogByPage',
                'params' => array('filter' => array('order_id' => $response['data']['id'], 'page_size' => '10', 'operator_type' => 0)),
            ));
            ?>
        </div>
    </div>
</div>
<script>
    var select_url = '';
    var id = '<?php echo $response['data']['id'] ?>';
    var kh_id = '<?php echo $response['data']['kh_id'] ?>';
    function show_val_server() {
        var param = {};
        var url = '?app_act=value/value_add/server_select_view&is_select=1&kh_id=' + kh_id;
        if (typeof (top.dialog) != 'undefined') {
            if (url != select_url) {
                top.dialog.remove(true);
            } else {
                top.dialog.show();
                return;
            }
        }
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 1);
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 0);
                }
            }, {
                text: '取消',
                elCls: 'button',
                handler: function () {
                    this.close();
                }
            }
        ];
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '新增服务',
                width: '80%',
                height: 450,
                loader: {
                    url: url,
                    autoLoad: true, //不自动加载
                    params: param, //附加的参数
                    lazyLoad: false, //不延迟加载
                    dataType: 'text'   //加载的数据类型
                },
                align: {
                    //node : '#t1',//对齐的节点
                    points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                    offset: [0, 20] //偏移
                },
                mask: true,
                buttons: buttons
            });
            top.dialog.on('closed', function () {
                location.reload();
            });
            top.dialog.show();
        });
    }

    function addgoods(obj, type) {
        var select_data = {};
        select_data = top.SelectoGrid.getSelection();
        var _thisDialog = obj;
        var arr = Object.keys(select_data);
        if (arr.length == 0) {
            _thisDialog.close();
            return;
        }
        var url = '?app_act=value/server_order/add_deatil_action';
        $.post(url, {data: select_data, id: id}, function (result) {
            if (result.status != 1) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    //       _thisDialog.close();
                }, 'error');
            } else {
                if (type == 1) {
                    top.skuSelectorStore.load();
                } else {
                    _thisDialog.close();
                }
            }
        }, 'json');
    }



    //删除
    function do_delete_detail(_index, row) {
        $.post('?app_act=value/server_order/do_delete_detail', {val_num: row.val_num, },function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type === 'success') {
                        BUI.Message.Alert(ret.message, function () {
                            location.reload();
                        }, type);
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }, 'json');
    }


</script>
