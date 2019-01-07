<?php
render_control('PageHead', 'head1', array('title' => '待收款列表',
    'links' => array(),
    'ref_table' => 'table'
));
?>

<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit',
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => '单据编号',
            'type' => 'input',
            'id' => 'record_code',
        ),
        array(
            'label' => '分销商',
            'type' => 'select_pop',
            'id' => 'custom_code',
            'select' => 'base/custom_multi'
        ),
        array(
            'label' => '收款状态',
            'title' => '',
            'type' => 'select',
            'id' => 'pay_status',
            'data' => array(array('','全部'),array('0', '未收款'), array('1', '部分收款')),
        ),
        array(
            'label' => '出库时间',
            'type' => 'group',
            'field' => 'lastchanged',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'lastchanged_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'lastchanged_end', 'remark' => ''),
            )
        ),
    )
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
                'width' => '110',
                'align' => '',
                'buttons' => array(
                    array('id' => 'add', 'title' => '<li class="bui-bar-item button button-small bui-inline-block" title="添加收款记录" aria-disabled="false" aria-pressed="false" style="width:12px;"><i class="icon icon-edit"></i></li>', 'callback' => 'do_add', 'show_cond' => '', 'priv' => 'fx/pending_payment/add_receive'),
                    array('id' => 'view', 'title' => '<li class="bui-bar-item button button-small bui-inline-block" title="查看收款记录" aria-disabled="false" aria-pressed="false" style="width:12px;"><i class="icon icon-list"></i></li>', 'callback' => 'do_view', 'priv' => 'fx/pending_payment/view_receive'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({store_out_record_id})" >{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商名称',
                'field' => 'custom_code_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库数量',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单金额',
                'field' => 'money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付金额',
                'field' => 'pay_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '待付金额',
                'field' => 'pending_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收款状态',
                'field' => 'pay_status_txt',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库时间',
                'field' => 'lastchanged',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '180',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'fx/PendingPaymentModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'export' => array('id' => 'exprot_list', 'conf' => 'pending_payment', 'name' => '分销账务待收款列表', 'export_type' => 'file'),
));
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

    function do_add(_index, row) {
        openPage('<?php echo base64_encode('?app_act=fx/pending_payment/add&record_code=') ?>' + row.record_code, '?app_act=fx/pending_payment/add&record_code=' + row.record_code, '添加收款记录');
    }

    function do_view(_index, row) {
        openPage('<?php echo base64_encode('?app_act=fx/pending_payment/view&record_code=') ?>' + row.record_code, '?app_act=fx/pending_payment/view&record_code=' + row.record_code, '查看收款记录');
    }

    function view(_id) {
        openPage('<?php echo base64_encode('?app_act=wbm/store_out_record/view&store_out_record_id') ?>' + _id, '?app_act=wbm/store_out_record/view&store_out_record_id=' + _id, '批发销货单详情');
    }
</script>




