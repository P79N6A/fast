<?php require_lib('util/oms_util', true); ?>
<?php echo load_js("baison.js,record_table.js", true); ?>

<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}

    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '查看收款记录',
    'links' => array(),
    'ref_table' => 'table'
));
?>

<div class="panel record_table" id="panel_html">
</div>
<div class="panel">
    <div class="panel-header" style="border:1px solid #dddddd;">
        <h3 class="">明细信息<i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <?php
        render_control('DataTable', 'table_list', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '支付流水号',
                        'field' => 'serial_number',
                        'width' => '200',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '状态',
                        'field' => 'status',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '日期',
                        'field' => 'record_time',
                        'width' => '150',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '付款金额',
                        'field' => 'money',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '支付方式',
                        'field' => 'pay_type',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '资金账号',
                        'field' => 'capital_account',
                        'width' => '150',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '收款账号',
                        'field' => 'account_name',
                        'width' => '150',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作人',
                        'field' => 'operator',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'button',
                        'show' => 1,
                        'title' => '操作',
                        'field' => '_operate',
                        'width' => '80',
                        'align' => '',
                        'buttons' => array(
                            array('id' => 'add', 'title' => '作废', 'callback' => 'do_cancel', 'show_cond' => 'obj.state==1', 'priv' => 'fx/balance_of_payments/cancellation'),
                            array('id' => 'view', 'title' => '删除', 'callback' => 'do_delete', 'show_cond' => 'obj.state==2 && obj.income_type==1', 'priv' => 'fx/balance_of_payments/do_delete'),
                        ),
                    ),
                )
            ),
            'dataset' => 'fx/BalanceOfPaymentsModel::get_by_account',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'], 'detail_type' => 1, 'record_type' => 0)),
            'idField' => 'detail_id',
        ));
        ?>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    $(function () {
        var dataRecord = [
            {'title': '单据编号', 'type': 'input', 'name': 'record_code', 'value': '<?php echo $response['data']['record_code'] ?>'},
            {'title': '分销商名称', 'type': 'input', 'name': 'record_date', 'value': '<?php echo $response['data']['custom_name'] ?>'},
            {'title': '', 'type': '', 'name': '', 'value': ''},
            {'title': '待付金额', 'type': 'input', 'name': 'create_time', 'value': '<?php echo $response['data']['pending_money'] ?>'},
            {'title': '已付金额', 'type': 'input', 'name': 'record_type_name', 'value': "<?php echo $response['data']['pay_money'] ?>"},
            {'title': '单据金额', 'type': 'input', 'name': 'shop_code_name', 'value': '<?php echo $response['data']['money'] ?>'}
        ];
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": dataRecord,
            "is_edit": false,
            "title": "单据信息",
            "edit_url": ""
        });
    });

    function do_cancel(_index, row) {
        BUI.Message.Confirm('确认作废该笔收款记录吗？', function () {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('fx/balance_of_payments/cancellation'); ?>', data: {id: row.id},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    BUI.Message.Alert(ret.message, type);
                    if (type == 'success') {
                        table_listStore.load();
                    }
                }
            });
        }, 'question');
    }

    function do_delete(_index, row) {
        BUI.Message.Confirm('确认删除该笔收款记录吗？', function () {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('fx/balance_of_payments/do_delete'); ?>', data: {id: row.id},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    BUI.Message.Alert(ret.message, type);
                    if (type == 'success') {
                        table_listStore.load();
                    }
                }
            });
        }, 'question');
    }
</script>