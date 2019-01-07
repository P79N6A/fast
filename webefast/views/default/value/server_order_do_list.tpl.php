<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '订单列表',
    'links' => '',
    'ref_table' => 'table'
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => false, 'id' => 'tabs_all'),
        array('title' => '待支付', 'active' => $response['tabs_pay'], 'id' => 'tabs_pay'),
        array('title' => '待评价', 'active' => $response['tabs_remark'], 'id' => 'tabs_remark'),
        array('title' => '已完成', 'active' => false, 'id' => 'tabs_complete'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单编号',
                'field' => 'order_code',
                'width' => '180',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({id})">{order_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'val_orderdate',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订购服务数量',
                'field' => 'server_num',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '费用（元）',
                'field' => 'server_money',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '类型',
                'field' => 'type',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '付款日期',
                'field' => 'pay_date',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '190',
                'align' => '',
                'buttons' => array(
                    array('id' => 'pay', 'title' => '支付', 'callback' => 'ali_pay', 'show_cond' => 'obj.pay_status==0&&obj.complete_status==0', 'priv' => ''),
                    array('id' => 'comment', 'title' => '评价', 'callback' => 'order_remark', 'show_cond' => 'obj.pay_status==1&&obj.complete_status!=1',),
                    array('id' => 'do_detele', 'title' => '删除', 'confirm' => '确定删除该订单吗？', 'callback' => 'do_delete', 'show_cond' => 'obj.pay_status!=1',),
                ),
            )
        )
    ),
    'dataset' => 'value/ValueServerModel::get_server_order_by_page',
    // 'queryBy' => 'searchForm',
    'idField' => 'id',
    'params' => array('filter' => array('kh_id' => $response['kh_id'],'tabs_type' => $response['tabs_type'])),
    //'RowNumber'=>true,
    // 'CheckSelection'=>true,
    'CascadeTable' => array(
        'list' => array(
            array('type' => 'text', 'title' => '序号', 'field' => 'sort', 'width' => '50',),
            array('type' => 'text', 'title' => '服务名称', 'field' => 'value_name', 'width' => '180',),
            array('type' => 'text', 'title' => '订购周期（月）', 'field' => 'val_hire_limit', 'width' => '100',),
            array('type' => 'text', 'title' => '销售价格', 'field' => 'val_standard_price', 'width' => '80',),
            array('type' => 'text', 'title' => '到期时间', 'field' => 'val_enddate', 'width' => '150',),
        ),
        'page_size' => 50,
        'url' => get_app_url('value/server_order/get_detail_list_by_order_code&app_fmt=json'),
        'params' => 'order_code',
    ),
));
?>
<script type="text/javascript">
    $(document).ready(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.tabs_type = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);
        });
        tableStore.load();
    })

    //支付
    function ali_pay(index, row) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('value/server_order/order_ali_pay'); ?>', data: {id: row.id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    after_submit(ret);
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    //获取返回的URL
    function after_submit(data) {
        window.open(data.data);
        BUI.Message.Show({
            title: '提示',
            msg: '是否支付成功?',
            icon: 'question',
            buttons: [
                {
                    text: '支付成功',
                    elCls: 'button button-primary',
                    handler: function () {
                        check_pay_status(data.message, this);
                    }
                },
                {
                    text: '支付失败',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                        tableStore.load();
                    }
                }
            ]
        });
    }

    //验证充值是否成功
    function check_pay_status(pay_out_trade_no, _this) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('value/server_order/check_pay_status'); ?>', data: {pay_out_trade_no: pay_out_trade_no},
            success: function (ret) {
                _this.close();
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

//评价
    function order_remark(index, row) {
        new ESUI.PopWindow("?app_act=value/server_order/edit_remark&id=" + row.id, {
            title: "评价",
            width: 450,
            height: 350,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                tableStore.load();
            }
        }).show();
    }

//删除
    function do_delete(index, row) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('value/server_order/do_order_delete'); ?>', data: {id: row.id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, function () {
                        tableStore.load();
                    }, type);
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function view(id) {
        var url = '?app_act=value/server_order/view&id=' + id
        openPage(window.btoa(url), url, '订单详情');
    }


</script>
