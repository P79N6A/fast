<?php
$is_power = load_model('sys/PrivilegeModel')->check_priv('fx/account/install_alipay_key');
$links = '';
if ($is_power == true) {
    $links = array(array('url' => 'fx/account/alipay_key&app_scene=edit', 'title' => '支付宝收款参数设置', 'is_pop' => true, 'pop_size' => '580,500'));
}
render_control('PageHead', 'head1', array('title' => '资金账户  ',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<?php
if ($response['fx_finance_account_manage'] == 0) {
    ?>
    <div>
        <span>您尚未开启资金账户功能</span>
        <span>如需开启，请前往<a href = '#' onclick = 'do_params()'>系统管理-系统参数设置</a></span>
    </div>
    <?php
} else {
    $buttons = array(
	array(
            'label' => '查询',
            'id'    => 'btn-search',
            'type'  => 'submit',
	),
        array(
            'label' => '导出',
            'id'    => 'exprot_list'
        ) 
    );

    render_control('SearchForm', 'searchForm', array(
        'buttons' => $buttons,
//        array(array(
//                'label' => '查询',
//                'id' => 'btn-search',
//                'type' => 'submit',
//            ),
//        array(
//            'label' => '导出',
//            'id' => 'exprot_list',
//        ),),
        'fields' => array(
            array('label' => '分销商', 'type' => 'select_pop', 'id' => 'custom_code', 'select' => 'base/custom_multi'),
            array(
                'label' => '创建时间',
                'type' => 'group',
                'field' => 'create_time',
                'child' => array(
                    array('title' => 'start', 'type' => 'date', 'field' => 'create_time_start',),
                    array('pre_title' => '~', 'type' => 'date', 'field' => 'create_time_end', 'remark' => ''),
                )
            ),
        )
    ));
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '150',
                    'align' => '',
                    'buttons' => array(
                        array(
                            'id' => 'do_recharge',
                            'title' => '充值',
                            'act' => 'pop:fx/account/balance_detail_recharge&capital_type=1&custom_code={custom_code}&custom_name={custom_name}&app_scene=add&arrears_money={arrears_money}',
                            'show_name' => '充值',
                            'pop_size' => '400,500',
                            'priv' => 'fx/account/balance_detail_recharge'
                        ),
                        array(
                            'id' => 'do_deduct_money',
                            'title' => '扣款',
                            'act' => 'pop:fx/account/balance_detail_deduct_money&capital_type=0&custom_code={custom_code}&custom_name={custom_name}&app_scene=add&arrears_money={arrears_money}',
                            'show_name' => '扣款',
                            'pop_size' => '400,500',
                            'priv' => 'fx/account/balance_detail_deduct_money'
                        ),
                        array('id' => 'do_account_detail', 'title' => '明细', 'callback' => 'do_account_detail','priv' => 'fx/account/do_account_detail'),
                        array(
                            'id' => 'do_arrears_money',
                            'title' => '欠款设置',
                            'act' => 'pop:fx/account/do_arrears_money&custom_code={custom_code}&custom_name={custom_name}&app_scene=edit',
                            'show_name' => '欠款设置',
                            'pop_size' => '400,400',
                            'priv' => 'fx/account/do_arrears_money'
                        ),
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '分销商编号',
                    'field' => 'custom_code',
                    'width' => '150',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '分销商名称',
                    'field' => 'custom_name',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '预存款账户余额',
                    'field' => 'yck_account_capital',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '分销商欠款额度',
                    'field' => 'arrears_money',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '创建时间',
                    'field' => 'create_time',
                    'width' => '150',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'fx/AccountModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'custom_id',
        'export'  => array('id' => 'exprot_list', 'conf' => 'account_do_list_detail', 'name' => '分销商资金帐户','export_type'=>'file'),//
//    'export' => array('id' => 'exprot_list', 'conf' => 'account_do_list', 'name' => '分销商预存款', 'export_type' => 'file'),
    ));
}
?>
<script type="text/javascript">
    var login_type = "<?php echo $response['login_type'] ?>";
    $(function () {
        if (login_type == 2) {
            $("#searchForm #custom_code_select_pop").attr("disabled", "true");
            $("#searchForm #custom_code_select_img").unbind();
        }

    });

    var selectPopWindowcustom_code = {
        dialog: null,
        callback: function (value) {
            var custom_code = [];
            var custom_name = [];
            $.each(value, function (i, v) {
                custom_code.push(v['custom_code']);
                custom_name.push(v['custom_name']);
            });
            $('#custom_code_select_pop').val(custom_name.join());
            $('#custom_code').val(custom_code.join());
            if (selectPopWindowcustom_code.dialog != null) {
                selectPopWindowcustom_code.dialog.close();
            }
        }
    };

    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/account/do_delete'); ?>', data: {account_id: row.account_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function do_confirm(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/account/do_confirm'); ?>', data: {account_id: row.account_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('确认成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    function do_ali_pay(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/account/do_list_ali_pay'); ?>', data: {account_code: row.account_code, account_money: row.account_money},
            success: function (data) {
                if (data.status < 0) {
                    BUI.Message.Alert(data.message, 'error');
                } else if (data.status == 2) {
                    window.open(data.data);
                    BUI.Message.Show({
                        title: '提示',
                        msg: '是否充值成功?',
                        icon: 'question',
                        buttons: [
                            {
                                text: '充值成功',
                                elCls: 'button button-primary',
                                handler: function () {
                                    check_pay_status(row.account_code);
                                    this.close();
                                }
                            },
                            {
                                text: '充值失败',
                                elCls: 'button',
                                handler: function () {
                                    tableStore.load();
                                    this.close();
                                }
                            }
                        ]
                    });
                }
            }
        });
    }
    function check_pay_status(_account_code) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/account/check_pay_status'); ?>', data: {account_code: _account_code},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    tableStore.load();
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    function do_account_detail(_index, row) {
        openPage('<?php echo base64_encode('?app_act=fx/account/do_account_detail&custom_code=') ?>' + row.custom_code + '&custom_name=' + row.custom_name, '?app_act=fx/account/do_account_detail&custom_code=' + row.custom_code + '&custom_name=' + row.custom_name, '详情');
        return;
    }
    function do_params() {
        openPage('<?php echo base64_encode('?app_act=sys/params/do_list') ?>', '?app_act=sys/params/do_list', '系统参数设置');
        return;
    }
</script>




