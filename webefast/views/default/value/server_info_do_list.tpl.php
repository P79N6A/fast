<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '服务列表',
    'links' => '',
    'ref_table' => 'table'
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '服务分类',
                'field' => 'value_category',
                'width' => '180',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '服务名称',
                'field' => 'value_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '付款时间',
                'field' => 'vra_startdate',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '到期时间',
                'field' => 'vra_enddate',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '我的评分',
                'field' => 'score_gread',
                'width' => '190',
                'align' => '',
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '50',
                'align' => '',
                'buttons' => array(
                    array('id' => 'pay', 'title' => '续费', 'callback' => 'ali_pay', 'show_cond' => 'obj.renew==1', 'priv' => ''),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '使用',
                'field' => 'user_help',
                'width' => '100',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'value/ValueServerModel::get_kh_server_by_page',
    // 'queryBy' => 'searchForm',
    'idField' => 'id',
    'params' => array('filter' => array('kh_id' => $response['kh_id'])),
        //'RowNumber'=>true,
        // 'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    //续费
    function ali_pay(index, row) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('value/server_info/renew_ali_pay'); ?>', data: {vra_server_id: row.vra_server_id, kh_id: row.vra_kh_id},
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
            icon: 'question', buttons: [
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
                        open_new_page();
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
                    BUI.Message.Alert('续费成功！', function () {
                        tableStore.load();
                        var url = '?app_act=value/server_order/do_list&tabs_type=tabs_remark';
                        openPage(window.btoa(url), url, '我的订单');
                    }, type);
                } else {
                    BUI.Message.Alert('已生成订单，支付宝支付失败！', function () {
                        tableStore.load();
                        var url = '?app_act=value/server_order/do_list';
                        openPage(window.btoa(url), url, '我的订单');
                    }, type);
                }
            }
        });
    }


    function open_new_page() {
        var url = '?app_act=value/server_order/do_list';
        openPage(window.btoa(url), url, '我的订单');
    }
</script>
